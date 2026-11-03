<?php

namespace App\Services;

use App\Models\RepairRequest;
use App\Models\Workshop;

class WorkshopAdvisor
{
    public function search(?string $problem = null, ?string $keyword = null, ?float $latitude = null, ?float $longitude = null): array
    {
        $load = RepairRequest::query()->whereIn('status', [RepairRequest::STATUS_PENDING, RepairRequest::STATUS_QUOTE_ACCEPTED, RepairRequest::STATUS_IN_PROGRESS])->selectRaw('workshop_id, count(*) as total')->groupBy('workshop_id')->pluck('total', 'workshop_id');
        return Workshop::query()->get()->map(function (Workshop $w) use ($load, $problem, $keyword) {
            $open = (int) ($load[$w->id] ?? 0);
            $match = (int) (($keyword && str_contains(mb_strtolower($w->specialty), mb_strtolower($keyword))) || ($problem && str_contains(mb_strtolower($w->specialty), mb_strtolower($problem))));
            return ['workshop' => $w, 'open' => $open, 'score' => $match * 100 - $open];
        })->sortByDesc('score')->take(5)->map(fn ($r) => ['name' => $r['workshop']->name, 'specialty' => $r['workshop']->specialty, 'address' => $r['workshop']->address, 'phone' => $r['workshop']->phone, 'open_workload' => $r['open'], 'url' => route('workshops.show', $r['workshop'])])->values()->all();
    }

    public function estimate(string $problem): array
    {
        $values = RepairRequest::query()->where('problem_type', $problem)->whereIn('status', [RepairRequest::STATUS_REPAIRED, RepairRequest::STATUS_COLLECTED])->whereNotNull('cost')->pluck('cost');
        if ($values->count() >= 3) return ['min' => round($values->min(), 2), 'average' => round($values->avg(), 2), 'max' => round($values->max(), 2), 'indicative' => true];
        return ['min' => config('ai.default_cost_range.min'), 'average' => null, 'max' => config('ai.default_cost_range.max'), 'indicative' => true];
    }
}
