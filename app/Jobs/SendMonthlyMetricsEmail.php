<?php

namespace App\Jobs;

use App\Mail\MonthlyMetricsMail;
use App\Models\Client;
use App\Models\EmailLog;
use App\Services\MonthlyMessageService;
use App\Services\MonthlyMetricsService;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Throwable;

class SendMonthlyMetricsEmail implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 180;

    public array $backoff = [60, 300, 900];

    public function __construct(
        public int $clientId,
        public string $email,
        public string $month
    ) {
    }

    public function handle(
        MonthlyMetricsService $metrics,
        MonthlyMessageService $messages
    ): void {
        $client = Client::find($this->clientId);

        if (!$client) {
            throw new RuntimeException(
                "Client {$this->clientId} no longer exists."
            );
        }

        if (!$client->is_active) {
            return;
        }

        $startDate = Carbon::createFromFormat(
            '!Y-m',
            $this->month
        )->startOfMonth();

        $endDate = $startDate->copy()->endOfMonth();

        $subject = "{$client->name} Monthly Metrics | "
            . $startDate->format('F Y');

        /*
         * A new record is created every time the queue attempts this job.
         * Retries will therefore appear as separate attempt records.
         */
        $emailLog = EmailLog::create([
            'client_id' => $client->id,
            'type' => 'monthly_metrics',
            'reporting_month' => $startDate->toDateString(),
            'recipient_email' => $this->email,
            'subject' => $subject,
            'status' => 'processing',
            'attempted_at' => now(),
        ]);

        try {
            $report = $metrics->generate(
                $client,
                $startDate,
                $endDate
            );

            $body = $messages->render($report);

            $emailLog->update([
                'body' => $body,
            ]);

            Mail::to($this->email)->send(
                new MonthlyMetricsMail($report)
            );

            $emailLog->update([
                'status' => 'sent',
                'sent_at' => now(),
                'error_message' => null,
            ]);
        } catch (Throwable $exception) {
            $emailLog->update([
                'status' => 'failed',
                'error_message' => mb_substr(
                    $exception->getMessage(),
                    0,
                    65535
                ),
            ]);

            throw $exception;
        }
    }
}