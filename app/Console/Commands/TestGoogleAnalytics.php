<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Services\GoogleAnalyticsService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use InvalidArgumentException;
use Throwable;

class TestGoogleAnalytics extends Command
{
    protected $signature = 'analytics:test
        {client : The database ID of the client}
        {--month= : Month to report in YYYY-MM format}';

    protected $description =
        'Retrieve calendar month analytics for a client';

    public function handle(GoogleAnalyticsService $analytics): int
    {
        $client = Client::find($this->argument('client'));

        if (!$client) {
            $this->error('Client not found.');

            return self::FAILURE;
        }

        if (blank($client->analytics_property)) {
            $this->error('This client does not have a GA4 Property ID.');

            return self::FAILURE;
        }

        try {
            $month = $this->parseMonth();

            $startDate = $month->copy()->startOfMonth()->toDateString();
            $endDate = $month->copy()->endOfMonth()->toDateString();

            $data = $analytics->monthlySummary(
                $client->analytics_property,
                $startDate,
                $endDate
            );
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

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

    protected function parseMonth(): Carbon
    {
        $monthOption = $this->option('month');

        if (blank($monthOption)) {
            return Carbon::now()->subMonth();
        }

        $month = Carbon::createFromFormat('!Y-m', $monthOption);

        if ($month->format('Y-m') !== $monthOption) {
            throw new InvalidArgumentException(
                'The month must use YYYY-MM format.'
            );
        }

        return $month;
    }
}
