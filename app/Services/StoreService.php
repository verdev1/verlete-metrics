<?php

namespace App\Services;

use App\Models\Client;
use Carbon\CarbonInterface;
use InvalidArgumentException;
use RuntimeException;

class StoreService
{
    public function __construct(
        protected WooCommerceService $woocommerce,
        protected SureCartService $surecart
    ) {
    }

    public function monthlySummary(
        Client $client,
        CarbonInterface $startDate,
        CarbonInterface $endDate
    ): array {
        return match ($client->store) {
            'woocommerce' => $this->woocommerceSummary(
                $client,
                $startDate,
                $endDate
            ),

            'surecart' => $this->surecartSummary(
                $client,
                $startDate,
                $endDate
            ),

            'none', null, '' => throw new InvalidArgumentException(
                'This client does not have a store configured.'
            ),

            default => throw new InvalidArgumentException(
                "Unsupported store platform: {$client->store}"
            ),
        };
    }

    protected function woocommerceSummary(
        Client $client,
        CarbonInterface $startDate,
        CarbonInterface $endDate
    ): array {
        if (
            blank($client->website)
            || blank($client->application_username)
            || blank($client->application_password)
        ) {
            throw new RuntimeException(
                'The client is missing WooCommerce connection details.'
            );
        }

        return $this->woocommerce->monthlySummary(
            $client->website,
            $client->application_username,
            $client->application_password,
            $startDate,
            $endDate
        );
    }

    protected function surecartSummary(
        Client $client,
        CarbonInterface $startDate,
        CarbonInterface $endDate
    ): array {
        if (blank($client->surecart_api_key)) {
            throw new RuntimeException(
                'The client is missing its SureCart API key.'
            );
        }

        return $this->surecart->monthlySummary(
            $client->surecart_api_key,
            $startDate,
            $endDate
        );
    }
}