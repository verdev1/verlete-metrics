<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Services\WordPressService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use InvalidArgumentException;
use Throwable;

class TestWordPressForms extends Command
{
    protected $signature = 'wordpress:test-forms
        {client : The database ID of the client}
        {--month= : Month to report in YYYY-MM format}';

    protected $description =
        'Retrieve Fluent Forms submission counts for a calendar month';

    public function handle(WordPressService $wordpress): int
    {
        $client = Client::find($this->argument('client'));

        if (!$client) {
            $this->error('Client not found.');

            return self::FAILURE;
        }

        if (
            blank($client->website)
            || blank($client->application_username)
            || blank($client->application_password)
        ) {
            $this->error(
                'The client is missing WordPress connection details.'
            );

            return self::FAILURE;
        }

        try {
            $month = $this->parseMonth();

            $startDate = $month->copy()->startOfMonth();
            $endDate = $month->copy()->endOfMonth();

            $forms = $wordpress->monthlyFormSubmissions(
                $client->website,
                $client->application_username,
                $client->application_password,
                $startDate,
                $endDate
            );
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info(
            "{$client->name}: {$startDate->toDateString()} "
            . "to {$endDate->toDateString()}"
        );

        $this->table(
            ['Form ID', 'Form', 'Submissions'],
            collect($forms)
                ->map(fn (array $form): array => [
                    $form['form_id'],
                    $form['form_name'],
                    number_format($form['submission_count']),
                ])
                ->all()
        );

        return self::SUCCESS;
    }

    protected function parseMonth(): Carbon
    {
        $monthOption = $this->option('month');

        if (blank($monthOption)) {
            return Carbon::now()->subMonth();
        }

        $month = Carbon::createFromFormat('!Y-m', $monthOption);

        if ($month->format('Y-m') !== $monthOption) {
            throw new InvalidArgumentException(
                'The month must use YYYY-MM format.'
            );
        }

        return $month;
    }
}