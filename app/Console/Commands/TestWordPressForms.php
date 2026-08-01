<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Services\WordPressService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Throwable;

class TestWordPressForms extends Command
{
    protected $signature = 'wordpress:test-forms {client}';

    protected $description =
        'Retrieve Fluent Forms submission counts for the previous month';

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

        $month = Carbon::now()->subMonth();

        $startDate = $month->copy()->startOfMonth();
        $endDate = $month->copy()->endOfMonth();

        try {
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
}