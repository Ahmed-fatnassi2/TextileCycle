<?php

namespace App\Services;

use App\Models\RepairRequest;

class RepairImpactCalculator
{
    public function inferCategory(?string $category, string $description): string
    {
        if (array_key_exists($category ?? '', config('repair_impact.factors', []))) return $category;
        $text = mb_strtolower($description);
        return match (true) {
            str_contains($text, 'pantalon'), str_contains($text, 'jean') => 'pantalon',
            str_contains($text, 'robe') => 'robe',
            str_contains($text, 'veste'), str_contains($text, 'manteau') => 'veste',
            str_contains($text, 'drap'), str_contains($text, 'linge'), str_contains($text, 'serviette') => 'linge_maison',
            str_contains($text, 'pull'), str_contains($text, 'chemise'), str_contains($text, 'haut') => 'haut',
            default => 'autre',
        };
    }

    public function calculate(RepairRequest $repair): void
    {
        $category = $this->inferCategory($repair->item_category, $repair->item_description);
        $factor = config("repair_impact.factors.{$category}", config('repair_impact.factors.autre'));
        $share = (float) config('repair_impact.replacement_share', 0.5);
        $repair->update(['item_category' => $category, 'co2_saved_kg' => round($factor['co2'] * $share, 2), 'waste_saved_kg' => round($factor['waste'] * $share, 2)]);
    }
}
