<?php

namespace Tests\Feature;

use App\Models\Visitor;
use App\Models\VisitorLog;
use App\Models\WayfindingLocation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_visitor_can_submit_check_in_from_home_page(): void
    {
        WayfindingLocation::query()->create([
            'location_id' => 'CONF_ROOM_12B',
            'display_name' => 'Conference Room 12B',
            'building_sector' => 'Tower A',
            'floor_label' => 'Level 12',
            'map_asset_path' => '/maps/tower-a/level-12.svg',
            'route_steps' => ['Take lift A to Level 12'],
            'is_public' => true,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Submit Check-In');

        $response = $this->post('/check-in', [
            'phone_number' => '+60123456789',
            'email_address' => 'jane@example.com',
            'full_name' => 'Jane Doe',
            'company_name' => 'Acme Corp',
            'id_doc_type' => 'MYKAD',
            'id_number' => '900101011234',
            'host_employee_id' => 'EMP-001',
            'assigned_location_id' => 'CONF_ROOM_12B',
        ]);

        $log = VisitorLog::query()->firstOrFail();

        $response->assertRedirect(route('check-in.status', $log));
        $this->assertDatabaseHas('visitor_logs', [
            'log_id' => $log->log_id,
            'status' => 'PENDING_HOST',
            'verification_channel' => 'MOBILE_WEB',
        ]);
    }

    public function test_host_can_approve_and_visitor_can_view_wayfinding(): void
    {
        $visitor = Visitor::query()->create([
            'phone_number' => '+60123456789',
            'email_address' => 'jane@example.com',
            'full_name' => 'Jane Doe',
            'company_name' => 'Acme Corp',
            'id_doc_type' => 'MYKAD',
            'encrypted_id_number' => '900101011234',
        ]);

        WayfindingLocation::query()->create([
            'location_id' => 'CONF_ROOM_12B',
            'display_name' => 'Conference Room 12B',
            'building_sector' => 'Tower A',
            'floor_label' => 'Level 12',
            'map_asset_path' => '/maps/tower-a/level-12.svg',
            'route_steps' => ['Take lift A to Level 12', 'Turn right'],
            'is_public' => true,
        ]);

        $log = VisitorLog::query()->create([
            'visitor_id' => $visitor->visitor_id,
            'host_employee_id' => 'EMP-001',
            'assigned_location_id' => 'CONF_ROOM_12B',
            'verification_channel' => 'MOBILE_WEB',
            'status' => 'PENDING_HOST',
            'checkin_submit_time' => now(),
        ]);

        $this->post(route('host.approval.apply', $log), [
            'host_action' => 'APPROVE',
            'assigned_location_id' => 'CONF_ROOM_12B',
        ])->assertRedirect(route('host.approval', $log));

        $this->assertDatabaseHas('visitor_logs', [
            'log_id' => $log->log_id,
            'status' => 'APPROVED',
        ]);

        $this->get(route('wayfinding.show', $log))
            ->assertOk()
            ->assertSee('Conference Room 12B')
            ->assertSee('Take lift A to Level 12');
    }

    public function test_reception_dashboard_lists_visitors(): void
    {
        $visitor = Visitor::query()->create([
            'phone_number' => '+60123456789',
            'email_address' => 'jane@example.com',
            'full_name' => 'Jane Doe',
            'company_name' => 'Acme Corp',
            'id_doc_type' => 'MYKAD',
            'encrypted_id_number' => '900101011234',
        ]);

        VisitorLog::query()->create([
            'visitor_id' => $visitor->visitor_id,
            'host_employee_id' => 'EMP-001',
            'assigned_location_id' => 'CONF_ROOM_12B',
            'verification_channel' => 'MOBILE_WEB',
            'status' => 'PENDING_HOST',
            'checkin_submit_time' => now(),
        ]);

        $this->get('/reception')
            ->assertOk()
            ->assertSee('Reception Dashboard')
            ->assertSee('Jane Doe');
    }
}
