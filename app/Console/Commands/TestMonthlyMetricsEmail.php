<?php

namespace App\Console\Commands;

use App\Mail\MonthlyMetricsMail;
use App\Models\Client;
use App\Services\MonthlyMetricsService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use InvalidArgumentException;
use Throwable;

class TestMonthlyMetricsEmail extends Command
{
    protected $signature = 'metrics:test-email
        {client : The database ID of the client}
        {email : The test recipient email address}
        {--month= : Month to report in YYYY-MM format}';

    protected $description =
        'Generate and send a monthly metrics email to a test recipient';

    public function handle(MonthlyMetricsService $metrics): int
    {
        $client = Client::find($this->argument('client'));

        if (!$client) {
            $this->error('Client not found.');

            return self::FAILURE;
        }

        $email = $this->argument('email');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('The test recipient must be a valid email address.');

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

            Mail::to($email)->send(
                new MonthlyMetricsMail($report)
            );
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info(
            "Monthly metrics email for {$client->name} sent to {$email}."
        );

        $this->line(
            "{$startDate->toDateString()} to {$endDate->toDateString()}"
        );

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