<?php

namespace Tests\Feature;

use App\Models\Visitor;
use App\Models\VisitorLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PruneVisitorPiiTest extends TestCase
{
    use RefreshDatabase;

    public function test_pruning_masks_checked_out_visitor_pii_after_retention_window(): void
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
            'verification_channel' => 'CHIP_READ',
            'status' => 'APPROVED',
            'checkout_time' => now()->subDays(91),
        ]);

        $this->artisan('vms:prune-visitor-pii')->assertSuccessful();

        $visitor->refresh();
        $this->assertNull($visitor->phone_number);
        $this->assertNull($visitor->email_address);
        $this->assertNull($visitor->full_name);
        $this->assertNull($visitor->encrypted_id_number);
    }
}
