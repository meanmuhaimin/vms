<?php

namespace Tests\Feature;

use App\Models\Visitor;
use App\Models\VisitorLog;
use App\Models\WayfindingLocation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WayfindingTest extends TestCase
{
    use RefreshDatabase;

    public function test_wayfinding_returns_only_approved_assigned_location(): void
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
            'display_name' => 'Conf Room 12B',
            'building_sector' => 'Tower A',
            'floor_label' => 'Level 12',
            'map_asset_path' => '/maps/tower-a/level-12.svg',
            'route_steps' => ['Take lift A to Level 12', 'Turn right to room 12B'],
            'is_public' => false,
        ]);

        $log = VisitorLog::query()->create([
            'visitor_id' => $visitor->visitor_id,
            'host_employee_id' => 'EMP-001',
            'assigned_location_id' => 'CONF_ROOM_12B',
            'verification_channel' => 'CHIP_READ',
            'status' => 'APPROVED',
            'host_approval_time' => now(),
        ]);

        $this->getJson('/api/v1/wayfinding/'.$log->log_id)
            ->assertOk()
            ->assertJsonPath('assigned_location_id', 'CONF_ROOM_12B')
            ->assertJsonMissing(['location_id' => 'ADMIN_SECURE_ZONE']);
    }
}
