<?php

namespace Tests\Unit;

use App\Models\RepairRequest;
use App\Services\RepairImpactCalculator;
use Tests\TestCase;

class RepairImpactCalculatorTest extends TestCase
{
    public function test_it_infers_categories_from_french_item_descriptions(): void
    {
        $calculator = new RepairImpactCalculator;

        $this->assertSame('pantalon', $calculator->inferCategory(null, 'Jean troué'));
        $this->assertSame('robe', $calculator->inferCategory(null, 'Robe à ajuster'));
        $this->assertSame('veste', $calculator->inferCategory(null, 'Manteau usé'));
        $this->assertSame('autre', $calculator->inferCategory(null, 'Article inconnu'));
    }

    public function test_an_explicit_category_is_preserved(): void
    {
        $this->assertSame('haut', (new RepairImpactCalculator)->inferCategory('haut', 'Jean'));
    }
}
