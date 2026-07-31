<?php

namespace App\Services;

use Google\Analytics\Data\V1beta\Client\BetaAnalyticsDataClient;
use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\Metric;
use Google\Analytics\Data\V1beta\RunReportRequest;

class GoogleAnalyticsService
{
    protected BetaAnalyticsDataClient $client;

    public function __construct()
    {
        $credentialsPath = base_path(
            config('services.google_analytics.credentials')
        );

        $this->client = new BetaAnalyticsDataClient([
            'credentials' => $credentialsPath,
        ]);
    }

    public function monthlySummary(
        string $propertyId,
        string $startDate,
        string $endDate
    ): array {
        $response = $this->client->runReport(
            new RunReportRequest([
                'property' => 'properties/' . $propertyId,

                'date_ranges' => [
                    new DateRange([
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                    ]),
                ],

                'metrics' => [
                    new Metric(['name' => 'newUsers']),
                    new Metric(['name' => 'activeUsers']),
                    new Metric(['name' => 'screenPageViews']),
                ],
            ])
        );

        $rows = $response->getRows();

        if (count($rows) === 0) {
            return [
                'new_users' => 0,
                'active_users' => 0,
                'page_views' => 0,
            ];
        }

        $values = $rows[0]->getMetricValues();

        return [
            'new_users' => (int) $values[0]->getValue(),
            'active_users' => (int) $values[1]->getValue(),
            'page_views' => (int) $values[2]->getValue(),
        ];
    }
}