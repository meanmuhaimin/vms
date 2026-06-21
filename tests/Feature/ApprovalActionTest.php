<?php

namespace Tests\Feature;

use App\Models\Visitor;
use App\Models\VisitorLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApprovalActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_approval_action_updates_log_and_dispatches_print_trigger(): void
    {
        $visitor = Visitor::query()->create([
            'phone_number' => '+60123456789',
            'email_address' => 'jane@example.com',
            'full_name' => 'Jane Doe',
            'company_name' => 'Acme Corp',
            'id_doc_type' => 'MYKAD',
            'encrypted_id_number' => '900101011234',
        ]);

        $log = VisitorLog::query()->create([
            'visitor_id' => $visitor->visitor_id,
            'host_employee_id' => 'EMP-001',
            'assigned_location_id' => 'CONF_ROOM_12B',
            'verification_channel' => 'CHIP_READ',
            'status' => 'PENDING_HOST',
            'desk_release_time' => now(),
        ]);

        $response = $this->postJson('/api/v1/approval/action', [
            'log_id' => $log->log_id,
            'host_action' => 'APPROVE',
            'assigned_location_id' => 'CONF_ROOM_12B',
            'auth_token' => 'crypto_signature_hash_string',
        ]);

        $response->assertOk()
            ->assertJsonPath('status', 'PROCESSED')
            ->assertJsonPath('action_applied', 'APPROVED')
            ->assertJsonPath('print_trigger_dispatched', true)
            ->assertJsonPath('target_printer_id', 'LOBBY_LANE_01_PRINTER');

        $this->assertDatabaseHas('visitor_logs', [
            'log_id' => $log->log_id,
            'status' => 'APPROVED',
            'assigned_location_id' => 'CONF_ROOM_12B',
        ]);
    }
}
