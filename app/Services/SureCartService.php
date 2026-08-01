<?php

namespace App\Services;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class SureCartService
{
    protected function client(string $apiKey): PendingRequest
    {
        return Http::baseUrl('https://api.surecart.com/v1/')
            ->withToken($apiKey)
            ->acceptJson()
            ->timeout(30)
            ->retry(2, 500, throw: false);
    }

    public function monthlySummary(
        string $apiKey,
        CarbonInterface $startDate,
        CarbonInterface $endDate
    ): array {
        $client = $this->client($apiKey);

        $checkouts = [];
        $page = 1;

        do {
            $response = $client->get('checkouts', [
                'status' => ['paid'],
                'live_mode' => true,
                'limit' => 100,
                'page' => $page,
            ]);

            if ($response->failed()) {
                throw new RuntimeException(
                    'Could not retrieve SureCart checkouts: '
                    . $response->status()
                    . ' '
                    . $response->body()
                );
            }

            $body = $response->json();
            $pageCheckouts = data_get($body, 'data', []);

            if (!is_array($pageCheckouts)) {
                throw new RuntimeException(
                    'SureCart returned an invalid checkouts response.'
                );
            }

            $checkouts = array_merge($checkouts, $pageCheckouts);

            $returnedCount = count($pageCheckouts);
            $page++;

            /*
             * SureCart returns at most 100 records per page. A page with
             * fewer than 100 records is the final page.
             */
        } while ($returnedCount === 100);

        $monthlyCheckouts = collect($checkouts)
            ->filter(function (array $checkout) use (
                $startDate,
                $endDate
            ): bool {
                if (($checkout['status'] ?? null) !== 'paid') {
                    return false;
                }

                if (empty($checkout['paid_at'])) {
                    return false;
                }

                $paidAt = Carbon::createFromTimestamp(
                    (int) $checkout['paid_at']
                );

                return $paidAt->betweenIncluded(
                    $startDate->copy()->startOfDay(),
                    $endDate->copy()->endOfDay()
                );
            })
            ->values();

        $currencies = $monthlyCheckouts
            ->pluck('currency')
            ->filter()
            ->map(fn (string $currency): string => strtoupper($currency))
            ->unique()
            ->values();

        if ($currencies->count() > 1) {
            throw new RuntimeException(
                'SureCart returned checkouts in multiple currencies: '
                . $currencies->implode(', ')
            );
        }

        /*
         * SureCart monetary amounts are returned in the smallest currency
         * unit. For AUD and USD, this means cents.
         *
         * net_paid_amount already accounts for refunded amounts.
         */
        $totalRevenueInCents = $monthlyCheckouts->sum(
            fn (array $checkout): int =>
                (int) ($checkout['net_paid_amount'] ?? 0)
        );

        $orderCount = $monthlyCheckouts->count();
        $totalRevenue = $totalRevenueInCents / 100;

        return [
            'orders' => $orderCount,
            'total_revenue' => round($totalRevenue, 2),
            'average_order_value' => $orderCount > 0
                ? round($totalRevenue / $orderCount, 2)
                : 0.00,
            'currency' => $currencies->first() ?? 'AUD',
        ];
    }
}