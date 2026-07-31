<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Services\GoogleAnalyticsService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class TestGoogleAnalytics extends Command
{
    protected $signature = 'analytics:test {client}';

    protected $description = 'Retrieve the previous calendar month analytics for a client';

    public function handle(GoogleAnalyticsService $analytics): int
    {
        $client = Client::findOrFail($this->argument('client'));

        if (!$client->analytics_property) {
            $this->error('This client does not have a GA4 Property ID.');

            return self::FAILURE;
        }

        $previousMonth = Carbon::now()->subMonth();

        $startDate = $previousMonth->copy()->startOfMonth()->toDateString();
        $endDate = $previousMonth->copy()->endOfMonth()->toDateString();

        $data = $analytics->monthlySummary(
            $client->analytics_property,
            $startDate,
            $endDate
        );

        $this->info(
            "{$client->name}: {$startDate} to {$endDate}"
        );

        $this->table(
            ['Metric', 'Value'],
            [
                ['New users', number_format($data['new_users'])],
                ['Active users', number_format($data['active_users'])],
                ['Page views', number_format($data['page_views'])],
            ]
        );

        return self::SUCCESS;
    }
}