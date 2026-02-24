<?php

namespace App\Support;

use App\Models\UsageEvent;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class UsageReportService
{
    /**
     * @return Collection<int, array<string,mixed>>
     */
    public function billingSummary(CarbonInterface $from, CarbonInterface $to, ?int $companyId = null): Collection
    {
        $rows = UsageEvent::query()
            ->selectRaw('company_id, event_type, COUNT(*) as events_count, SUM(quantity) as total_quantity, SUM(billable_units) as total_billable_units')
            ->whereBetween('occurred_at', [$from, $to])
            ->when($companyId !== null, fn ($q) => $q->where('company_id', $companyId))
            ->groupBy('company_id', 'event_type')
            ->orderBy('company_id')
            ->orderBy('event_type')
            ->get();

        return $rows->map(function (UsageEvent $row) {
            return [
                'company_id' => $row->company_id,
                'event_type' => $row->event_type,
                'events_count' => (int) ($row->events_count ?? 0),
                'total_quantity' => (int) ($row->total_quantity ?? 0),
                'total_billable_units' => (int) ($row->total_billable_units ?? 0),
            ];
        });
    }
}
