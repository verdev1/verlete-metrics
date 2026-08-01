<?php

namespace App\Services;

use Illuminate\Support\Facades\View;

class MonthlyMessageService
{
    public function render(array $report): string
    {
        return View::make(
            'reports.monthly-message',
            $report
        )->render();
    }
}