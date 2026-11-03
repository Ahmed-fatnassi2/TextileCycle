<?php

namespace App\Services;

use App\Models\RepairRequest;
use Illuminate\Support\Collection;

class DelayRiskPredictor
{
    public function assess(Collection $repairs): array
    {
        $ids = $repairs->pluck('workshop_id')->unique();
        $workload = RepairRequest::query()->whereIn('workshop_id', $ids)->whereIn('status', [RepairRequest::STATUS_PENDING, RepairRequest::STATUS_QUOTE_ACCEPTED, RepairRequest::STATUS_IN_PROGRESS])->selectRaw('workshop_id, count(*) as aggregate')->groupBy('workshop_id')->pluck('aggregate', 'workshop_id');
        $durations = RepairRequest::query()->whereIn('workshop_id', $ids)->whereNotNull('started_at')->whereNotNull('ready_at')->get(['workshop_id', 'started_at', 'ready_at'])->groupBy('workshop_id')->map(function ($rows) {
            return ['samples' => $rows->count(), 'average_days' => $rows->avg(fn ($row) => $row->started_at->diffInHours($row->ready_at) / 24)];
        });

        return $repairs->mapWithKeys(function (RepairRequest $repair) use ($workload, $durations) {
            $days = $repair->estimated_completion_date ? (int) now()->startOfDay()->diffInDays($repair->estimated_completion_date->startOfDay(), false) : 0;
            $history = $durations->get($repair->workshop_id);
            $average = $history && $history['samples'] >= 3 ? (float) $history['average_days'] : 5.0;
            $load = (int) ($workload[$repair->workshop_id] ?? 0);
            $risk = ($days < 0 || $load >= 8 || $days < $average / 2) ? 'high' : (($load >= 4 || $days <= $average) ? 'medium' : 'low');
            return [$repair->id => ['risk' => $risk, 'reason' => "{$days} jours restants, durée moyenne ".round($average)." j, {$load} demandes ouvertes"]];
        })->all();
    }
}
