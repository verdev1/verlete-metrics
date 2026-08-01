<?php

namespace App\Services;

use Carbon\CarbonInterface;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class WordPressService
{
    protected function client(
        string $website,
        string $username,
        string $applicationPassword
    ): PendingRequest {
        return Http::baseUrl(
            rtrim($website, '/') . '/wp-json/fluentform/v1/'
        )
            ->withBasicAuth($username, $applicationPassword)
            ->acceptJson()
            ->timeout(30)
            ->retry(2, 500, throw: false);
    }

    public function monthlyFormSubmissions(
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

        $formsResponse = $client->get('forms', [
            'per_page' => 100,
        ]);

        if ($formsResponse->failed()) {
            throw new RuntimeException(
                'Could not retrieve Fluent Forms: '
                . $formsResponse->status()
                . ' '
                . $formsResponse->body()
            );
        }

        $forms = data_get($formsResponse->json(), 'data.data')
            ?? data_get($formsResponse->json(), 'data')
            ?? data_get($formsResponse->json(), 'forms')
            ?? [];

        return collect($forms)
            ->map(function (array $form) use (
                $client,
                $startDate,
                $endDate
            ): array {
                $formId = $form['id'];

                $response = $client->get('submissions', [
                    'form_id' => $formId,
                    'per_page' => 1,
                    'page' => 1,
                    'date_range' => [
                        $startDate->toDateString(),
                        $endDate->toDateString(),
                    ],
                ]);

                if ($response->failed()) {
                    throw new RuntimeException(
                        "Could not retrieve submissions for form {$formId}: "
                        . $response->status()
                        . ' '
                        . $response->body()
                    );
                }

                $body = $response->json();

                $count = data_get($body, 'total')
                    ?? data_get($body, 'data.total')
                    ?? data_get($body, 'submissions.total')
                    ?? 0;

                return [
                    'form_id' => (int) $formId,
                    'form_name' => $form['title'] ?? "Form {$formId}",
                    'submission_count' => (int) $count,
                ];
            })
            ->values()
            ->all();
    }
}