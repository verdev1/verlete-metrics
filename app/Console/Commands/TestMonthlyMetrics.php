<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Services\MonthlyMessageService;
use App\Services\MonthlyMetricsService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use InvalidArgumentException;
use Throwable;

class TestMonthlyMetrics extends Command
{
    protected $signature = 'metrics:test
        {client : The database ID of the client}
        {--month= : Month to report in YYYY-MM format}';

    protected $description =
        'Retrieve and display the monthly metrics report for a client';

    public function handle(
        MonthlyMetricsService $metrics,
        MonthlyMessageService $messages
    ): int {
        $client = Client::find($this->argument('client'));

        if (!$client) {
            $this->error('Client not found.');

            return self::FAILURE;
        }

        try {
            $month = $this->parseMonth();

            $startDate = $month->copy()->startOfMonth();
            $endDate = $month->copy()->endOfMonth();

            $report = $metrics->generate(
                $client,
                $startDate,
                $endDate
            );

            $message = $messages->render($report);
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info(
            "{$client->name}: {$startDate->toDateString()} "
            . "to {$endDate->toDateString()}"
        );

        $this->newLine();
        $this->line($message);

        return self::SUCCESS;
    }

    protected function parseMonth(): Carbon
    {
        $monthOption = $this->option('month');

        if (blank($monthOption)) {
            return Carbon::now()->subMonth()->startOfMonth();
        }

        try {
            $month = Carbon::createFromFormat(
                '!Y-m',
                $monthOption
            );
        } catch (Throwable) {
            throw new InvalidArgumentException(
                'The month must use YYYY-MM format.'
            );
        }

        if ($month->format('Y-m') !== $monthOption) {
            throw new InvalidArgumentException(
                'The month must use YYYY-MM format.'
            );
        }

        return $month;
    }
}