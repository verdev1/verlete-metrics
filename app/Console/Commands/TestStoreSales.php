<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Services\StoreService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Throwable;

class TestStoreSales extends Command
{
    protected $signature = 'store:test
        {client : The database ID of the client}
        {--month= : Month to report in YYYY-MM format}';

    protected $description =
        'Retrieve monthly store sales for a client';

    public function handle(StoreService $stores): int
    {
        $client = Client::find($this->argument('client'));

        if (!$client) {
            $this->error('Client not found.');

            return self::FAILURE;
        }

        if (blank($client->store) || $client->store === 'none') {
            $this->error(
                'This client does not have an ecommerce store configured.'
            );

            return self::FAILURE;
        }

        try {
            if ($monthOption = $this->option('month')) {
                $month = Carbon::createFromFormat(
                    '!Y-m',
                    $monthOption
                );

                if (!$month || $month->format('Y-m') !== $monthOption) {
                    throw new \InvalidArgumentException(
                        'The month must use YYYY-MM format.'
                    );
                }
            } else {
                $month = Carbon::now()->subMonth();
            }

            $startDate = $month->copy()->startOfMonth();
            $endDate = $month->copy()->endOfMonth();

            $summary = $stores->monthlySummary(
                $client,
                $startDate,
                $endDate
            );
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $currency = strtoupper($summary['currency']);

        $this->info(
            "{$client->name}: {$startDate->toDateString()} "
            . "to {$endDate->toDateString()}"
        );

        $this->line('Platform: ' . ucfirst($client->store));

        $this->table(
            ['Store Sales', 'Value'],
            [
                [
                    'Orders',
                    number_format($summary['orders']),
                ],
                [
                    'Total Revenue',
                    $currency . ' $'
                        . number_format(
                            $summary['total_revenue'],
                            2
                        ),
                ],
                [
                    'Average Order Value',
                    $currency . ' $'
                        . number_format(
                            $summary['average_order_value'],
                            2
                        ),
                ],
            ]
        );

        return self::SUCCESS;
    }
}