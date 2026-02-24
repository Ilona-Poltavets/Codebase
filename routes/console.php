<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Support\UsageReportService;
use Carbon\Carbon;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('usage:billing-report {--from=} {--to=} {--company=}', function (UsageReportService $service) {
    $from = $this->option('from') ? Carbon::parse((string) $this->option('from'))->startOfDay() : now()->startOfMonth();
    $to = $this->option('to') ? Carbon::parse((string) $this->option('to'))->endOfDay() : now()->endOfDay();
    $companyId = $this->option('company') ? (int) $this->option('company') : null;

    $rows = $service->billingSummary($from, $to, $companyId);

    if ($rows->isEmpty()) {
        $this->info('No usage events found for selected range.');

        return;
    }

    $this->table(
        ['company_id', 'event_type', 'events_count', 'total_quantity', 'total_billable_units'],
        $rows->all()
    );
})->purpose('Build usage summary prepared for future billing');
