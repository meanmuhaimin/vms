<?php

namespace App\Console\Commands;

use App\Models\Visitor;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class PruneVisitorPii extends Command
{
    protected $signature = 'vms:prune-visitor-pii {--days=90 : Number of days after checkout before masking PII}';

    protected $description = 'Mask visitor PII for checked-out visitor records older than the retention window.';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $cutoff = Carbon::now()->subDays($days);

        $count = Visitor::query()
            ->whereNotNull('encrypted_id_number')
            ->whereHas('logs', function ($query) use ($cutoff): void {
                $query->whereNotNull('checkout_time')
                    ->where('checkout_time', '<=', $cutoff);
            })
            ->update([
                'phone_number' => null,
                'email_address' => null,
                'full_name' => null,
                'company_name' => null,
                'encrypted_id_number' => null,
            ]);

        $this->info("Masked PII for {$count} visitor record(s) older than {$days} days after checkout.");

        return self::SUCCESS;
    }
}
