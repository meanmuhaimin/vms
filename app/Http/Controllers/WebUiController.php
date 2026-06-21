<?php

namespace App\Http\Controllers;

use App\Models\OtpLedger;
use App\Models\Visitor;
use App\Models\VisitorLog;
use App\Models\WayfindingLocation;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class WebUiController extends Controller
{
    public function home(): View
    {
        return view('home', [
            'locations' => $this->publicLocations(),
        ]);
    }

    public function requestOtp(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'phone_number' => ['required', 'string', 'max:20', 'regex:/^\+[1-9][0-9]{7,18}$/'],
        ]);

        $phoneNumber = $validated['phone_number'];
        $limiterKey = 'otp-request:'.$phoneNumber;
        $resendLimit = (int) config('vms.otp_resend_limit', 3);

        if (RateLimiter::tooManyAttempts($limiterKey, $resendLimit)) {
            throw ValidationException::withMessages([
                'phone_number' => ['OTP resend limit exceeded for this check-in session.'],
            ]);
        }

        RateLimiter::hit($limiterKey, 180);

        $ttlSeconds = (int) config('vms.otp_ttl_seconds', 180);
        $otpToken = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $otpHash = hash('sha256', $otpToken);

        OtpLedger::query()->create([
            'phone_number' => $phoneNumber,
            'otp_token_hash' => $otpHash,
            'expiration_time' => Carbon::now()->addSeconds($ttlSeconds),
            'attempts_count' => 0,
        ]);

        Cache::put('otp:'.$phoneNumber, [
            'token_hash' => $otpHash,
            'expires_at' => Carbon::now()->addSeconds($ttlSeconds)->toISOString(),
        ], $ttlSeconds);

        return back()->withInput()->with('status', 'OTP dispatch queued for '.$phoneNumber.'.');
    }

    public function storeCheckIn(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'phone_number' => ['required', 'string', 'max:20', 'regex:/^\+[1-9][0-9]{7,18}$/'],
            'email_address' => ['required', 'email', 'max:255'],
            'full_name' => ['required', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'id_doc_type' => ['required', Rule::in(['MYKAD', 'PASSPORT', 'OTHER'])],
            'id_number' => ['required', 'string', 'max:120'],
            'host_employee_id' => ['required', 'string', 'max:100'],
            'assigned_location_id' => ['required', 'string', 'max:50'],
        ]);

        $visitor = Visitor::query()->create([
            'phone_number' => $validated['phone_number'],
            'email_address' => $validated['email_address'],
            'full_name' => $validated['full_name'],
            'company_name' => $validated['company_name'],
            'id_doc_type' => $validated['id_doc_type'],
            'encrypted_id_number' => $validated['id_number'],
        ]);

        $log = VisitorLog::query()->create([
            'visitor_id' => $visitor->visitor_id,
            'host_employee_id' => $validated['host_employee_id'],
            'assigned_location_id' => $validated['assigned_location_id'],
            'verification_channel' => 'MOBILE_WEB',
            'status' => 'PENDING_HOST',
            'checkin_submit_time' => now(),
        ]);

        return redirect()->route('check-in.status', $log)->with('status', 'Check-in submitted. Please wait at reception.');
    }

    public function checkInStatus(VisitorLog $log): View
    {
        return view('check-in-status', [
            'log' => $log->load('visitor'),
            'location' => $this->locationFor($log),
        ]);
    }

    public function reception(): View
    {
        return view('reception', [
            'logs' => VisitorLog::query()
                ->with('visitor')
                ->latest('checkin_submit_time')
                ->limit(50)
                ->get(),
        ]);
    }

    public function releaseToHost(VisitorLog $log): RedirectResponse
    {
        $log->forceFill([
            'status' => 'PENDING_HOST',
            'desk_release_time' => now(),
        ])->save();

        return back()->with('status', 'Visitor released to host approval queue.');
    }

    public function checkout(VisitorLog $log): RedirectResponse
    {
        $log->forceFill([
            'status' => 'CHECKED_OUT',
            'checkout_time' => now(),
        ])->save();

        return back()->with('status', 'Visitor checked out.');
    }

    public function hostApproval(VisitorLog $log): View
    {
        return view('host-approval', [
            'log' => $log->load('visitor'),
            'locations' => $this->publicLocations(),
        ]);
    }

    public function applyHostApproval(Request $request, VisitorLog $log): RedirectResponse
    {
        $validated = $request->validate([
            'host_action' => ['required', Rule::in(['APPROVE', 'DENY'])],
            'assigned_location_id' => ['required', 'string', 'max:50'],
        ]);

        $status = $validated['host_action'] === 'APPROVE' ? 'APPROVED' : 'DENIED';

        $log->forceFill([
            'assigned_location_id' => $validated['assigned_location_id'],
            'status' => $status,
            'host_approval_time' => now(),
        ])->save();

        return redirect()->route('host.approval', $log)->with('status', 'Host action applied: '.$status.'.');
    }

    public function wayfinding(VisitorLog $log): View
    {
        abort_unless($log->status === 'APPROVED', 404);

        return view('wayfinding', [
            'log' => $log->load('visitor'),
            'location' => $this->locationFor($log),
        ]);
    }

    private function publicLocations()
    {
        return WayfindingLocation::query()
            ->where('is_public', true)
            ->orderBy('display_name')
            ->get();
    }

    private function locationFor(VisitorLog $log): ?WayfindingLocation
    {
        if ($log->assigned_location_id === null || $log->assigned_location_id === '') {
            return null;
        }

        return WayfindingLocation::query()->find($log->assigned_location_id);
    }
}
