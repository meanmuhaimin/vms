<?php

namespace App\Http\Controllers;

use App\Models\VisitorLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ApprovalActionController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'log_id' => ['required', 'uuid', 'exists:visitor_logs,log_id'],
            'host_action' => ['required', Rule::in(['APPROVE', 'DENY'])],
            'assigned_location_id' => ['required', 'string', 'max:50'],
            'auth_token' => ['required', 'string', 'max:255'],
        ]);

        $status = $validated['host_action'] === 'APPROVE' ? 'APPROVED' : 'DENIED';

        $visitorLog = VisitorLog::query()->findOrFail($validated['log_id']);
        $visitorLog->forceFill([
            'assigned_location_id' => $validated['assigned_location_id'],
            'status' => $status,
            'host_approval_time' => now(),
        ])->save();

        $printerId = config('vms.default_printer_id', 'LOBBY_LANE_01_PRINTER');

        return response()->json([
            'status' => 'PROCESSED',
            'action_applied' => $status,
            'print_trigger_dispatched' => $status === 'APPROVED',
            'target_printer_id' => $status === 'APPROVED' ? $printerId : null,
        ]);
    }
}
