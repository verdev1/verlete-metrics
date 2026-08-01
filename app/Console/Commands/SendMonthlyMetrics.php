<?php

namespace App\Console\Commands;

use App\Jobs\SendMonthlyMetricsEmail;
use App\Models\Client;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendMonthlyMetrics extends Command
{
    protected $signature = 'metrics:send-monthly
        {--month= : Month to report in YYYY-MM format}';

    protected $description =
        'Queue monthly metrics emails for all active clients';

    public function handle(): int
    {
        $month = filled($this->option('month'))
            ? Carbon::createFromFormat('!Y-m', $this->option('month'))
            : now()->subMonth()->startOfMonth();

        $clients = Client::query()
            ->where('is_active', true)
            ->whereNotNull('emails')
            ->where('emails', '!=', '')
            ->get();

        if ($clients->isEmpty()) {
            $this->warn('No active clients with recipient emails found.');

            return self::SUCCESS;
        }

        $jobCount = 0;

        foreach ($clients as $client) {
            SendMonthlyMetricsEmail::dispatch(
                $client->id,
                $month->format('Y-m')
            )->onQueue('monthly-metrics');

            $jobCount++;
        }

        $this->info(
            "{$jobCount} monthly metrics email job(s) queued "
            . "for {$month->format('F Y')}."
        );

        return self::SUCCESS;
    }
}