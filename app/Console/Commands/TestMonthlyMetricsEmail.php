<?php

namespace App\Console\Commands;

use App\Mail\MonthlyMetricsMail;
use App\Models\Client;
use App\Models\EmailLog;
use App\Services\MonthlyMessageService;
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

    public function handle(
        MonthlyMetricsService $metrics,
        MonthlyMessageService $messages
    ): int {
        $email = $this->argument('email');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('The test recipient must be a valid email address.');

            return self::FAILURE;
        }

        $client = Client::find($this->argument('client'));

        if (!$client) {
            $this->error('Client not found.');

            return self::FAILURE;
        }

        $emailLog = null;

        try {
            $month = $this->parseMonth();

            $startDate = $month->copy()->startOfMonth();
            $endDate = $month->copy()->endOfMonth();

            $subject = "{$client->name} Monthly Metrics | "
                . $startDate->format('F Y');

            /*
            * Create the log before collecting metrics or sending.
            * This ensures failures are also recorded.
            */
            $emailLog = EmailLog::create([
                'client_id' => $client->id,
                'type' => 'monthly_metrics_test',
                'reporting_month' => $startDate->toDateString(),
                'recipient_email' => $email,
                'subject' => $subject,
                'status' => 'processing',
                'attempted_at' => now(),
            ]);

            $report = $metrics->generate(
                $client,
                $startDate,
                $endDate
            );

            $body = $messages->render($report);

            $emailLog->update([
                'body' => $body,
            ]);

            Mail::to($email)->send(
                new MonthlyMetricsMail($report)
            );

            $emailLog->update([
                'status' => 'sent',
                'sent_at' => now(),
                'error_message' => null,
            ]);
        } catch (Throwable $exception) {
            if ($emailLog) {
                $emailLog->update([
                    'status' => 'failed',
                    'error_message' => $exception->getMessage(),
                ]);
            }

            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info(
            "Monthly metrics email for {$client->name} sent to {$email}."
        );

        $this->line(
            "{$startDate->toDateString()} to {$endDate->toDateString()}"
        );

        $this->line("Email log ID: {$emailLog->id}");

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