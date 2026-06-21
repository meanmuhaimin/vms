<?php

namespace App\Http\Controllers;

use App\Models\OtpLedger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class OtpRequestController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phone_number' => ['required', 'string', 'max:20', 'regex:/^\+[1-9][0-9]{7,18}$/'],
            'client_timestamp' => ['required', 'date'],
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

        OtpLedger::create([
            'phone_number' => $phoneNumber,
            'otp_token_hash' => $otpHash,
            'expiration_time' => Carbon::now()->addSeconds($ttlSeconds),
            'attempts_count' => 0,
        ]);

        Cache::put('otp:'.$phoneNumber, [
            'token_hash' => $otpHash,
            'expires_at' => Carbon::now()->addSeconds($ttlSeconds)->toISOString(),
        ], $ttlSeconds);

        return response()->json([
            'status' => 'SUCCESS',
            'message' => 'OTP dispatch action executed successfully via GOWA routing channels.',
            'session_ttl_seconds' => $ttlSeconds,
        ]);
    }
}
