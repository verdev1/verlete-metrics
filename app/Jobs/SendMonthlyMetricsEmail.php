<?php

namespace App\Jobs;

use App\Mail\MonthlyMetricsMail;
use App\Models\Client;
use App\Models\EmailLog;
use App\Services\MonthlyMessageService;
use App\Services\MonthlyMetricsService;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Throwable;

class SendMonthlyMetricsEmail implements ShouldQueue, ShouldBeUnique
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 180;

    public array $backoff = [60, 300, 900];

    public function __construct(
        public int $clientId,
        public string $month
    ) {
    }

    public function uniqueId(): string
    {
        return "monthly_metrics:{$this->clientId}:{$this->month}";
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

        $emails = collect(
            preg_split('/[,;\s]+/', $client->emails ?? '')
        )
            ->map(fn (string $email) => trim($email))
            ->filter(fn (string $email) => filter_var(
                $email,
                FILTER_VALIDATE_EMAIL
            ))
            ->unique()
            ->values()
            ->all();

        if (empty($emails)) {
            throw new RuntimeException(
                "Client {$client->id} has no valid recipient emails."
            );
        }

        $startDate = Carbon::createFromFormat(
            '!Y-m',
            $this->month
        )->startOfMonth();

        $endDate = $startDate->copy()->endOfMonth();

        $subject = "{$client->name} Monthly Metrics | "
            . $startDate->format('F Y');

        $alreadySent = EmailLog::query()
            ->where('client_id', $client->id)
            ->where('type', 'monthly_metrics')
            ->whereDate('reporting_month', $startDate->toDateString())
            ->where('status', 'sent')
            ->exists();

        if ($alreadySent) {
            return;
        }

        $emailLog = EmailLog::firstOrCreate(
            [
                'client_id' => $client->id,
                'type' => 'monthly_metrics',
                'reporting_month' => $startDate->toDateString(),
            ],
            [
                'recipient_email' => implode(', ', $emails),
                'subject' => $subject,
                'status' => 'processing',
                'attempted_at' => now(),
            ]
        );

        if ($emailLog->status === 'sent') {
            return;
        }

        $emailLog->update([
            'recipient_email' => implode(', ', $emails),
            'subject' => $subject,
            'status' => 'processing',
            'attempted_at' => now(),
            'sent_at' => null,
            'error_message' => null,
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

            Mail::to($emails)->send(
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