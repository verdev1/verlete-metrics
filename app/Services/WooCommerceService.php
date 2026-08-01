<?php

namespace App\Services;

use Carbon\CarbonInterface;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class WooCommerceService
{
    protected function client(
        string $website,
        string $username,
        string $applicationPassword
    ): PendingRequest {
        return Http::baseUrl(
            rtrim($website, '/') . '/wp-json/wc/v3/'
        )
            ->withBasicAuth($username, $applicationPassword)
            ->acceptJson()
            ->timeout(30)
            ->retry(2, 500, throw: false);
    }

    public function monthlySummary(
        string $website,
        string $username,
        string $applicationPassword,
        CarbonInterface $startDate,
        CarbonInterface $endDate
    ): array {
        $client = $this->client(
            $website,
            $username,
            $applicationPassword
        );

        $orders = [];
        $page = 1;

        do {
            $response = $client->get('orders', [
                'status' => 'processing,completed',
                'after' => $startDate
                    ->copy()
                    ->startOfDay()
                    ->toIso8601String(),
                'before' => $endDate
                    ->copy()
                    ->endOfDay()
                    ->toIso8601String(),
                'dates_are_gmt' => false,
                'per_page' => 100,
                'page' => $page,
                'orderby' => 'date',
                'order' => 'asc',
            ]);

            if ($response->failed()) {
                throw new RuntimeException(
                    'Could not retrieve WooCommerce orders: '
                    . $response->status()
                    . ' '
                    . $response->body()
                );
            }

            $pageOrders = $response->json();

            if (!is_array($pageOrders)) {
                throw new RuntimeException(
                    'WooCommerce returned an invalid orders response.'
                );
            }

            $orders = array_merge($orders, $pageOrders);

            $totalPages = max(
                1,
                (int) $response->header('X-WP-TotalPages')
            );

            $page++;
        } while ($page <= $totalPages);

        $currencies = collect($orders)
            ->pluck('currency')
            ->filter()
            ->map(fn (string $currency): string => strtoupper($currency))
            ->unique()
            ->values();

        if ($currencies->count() > 1) {
            throw new RuntimeException(
                'WooCommerce returned orders in multiple currencies: '
                . $currencies->implode(', ')
            );
        }

        $totalRevenue = collect($orders)->sum(
            function (array $order): float {
                $orderTotal = (float) ($order['total'] ?? 0);

                /*
                 * WooCommerce normally returns refund totals as negative
                 * values, for example -25.00.
                 */
                $refundTotal = collect($order['refunds'] ?? [])
                    ->sum(function (array $refund): float {
                        $amount = (float) ($refund['total'] ?? 0);

                        return $amount > 0 ? -$amount : $amount;
                    });

                return $orderTotal + $refundTotal;
            }
        );

        $orderCount = count($orders);

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