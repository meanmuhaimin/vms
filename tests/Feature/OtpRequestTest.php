<?php

namespace Tests\Feature;

use App\Models\OtpLedger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class OtpRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_otp_request_creates_ledger_entry_and_cache_state(): void
    {
        $response = $this->postJson('/api/v1/auth/otp-request', [
            'phone_number' => '+60123456789',
            'client_timestamp' => now()->toISOString(),
        ]);

        $response->assertOk()
            ->assertJsonPath('status', 'SUCCESS')
            ->assertJsonPath('session_ttl_seconds', 180);

        $this->assertSame(1, OtpLedger::query()->count());
        $this->assertNotNull(Cache::get('otp:+60123456789'));
    }
}
