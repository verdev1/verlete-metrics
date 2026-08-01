<?php

namespace App\Services;

use App\Models\Client;
use Carbon\CarbonInterface;

class MonthlyMetricsService
{
    public function __construct(
        protected GoogleAnalyticsService $analytics,
        protected WordPressService $wordpress,
        protected StoreService $stores
    ) {
    }

    public function generate(
        Client $client,
        CarbonInterface $startDate,
        CarbonInterface $endDate
    ): array {
        $analytics = $this->analytics->monthlySummary(
            $client->analytics_property,
            $startDate->toDateString(),
            $endDate->toDateString()
        );

        $forms = $this->wordpress->monthlyFormSubmissions(
            $client->website,
            $client->application_username,
            $client->application_password,
            $startDate,
            $endDate
        );

        $store = null;

        if (
            filled($client->store)
            && $client->store !== 'none'
        ) {
            $store = $this->stores->monthlySummary(
                $client,
                $startDate,
                $endDate
            );
        }

        return [
            'client' => $client,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'analytics' => $analytics,
            'forms' => $forms,
            'store' => $store,
        ];
    }
}