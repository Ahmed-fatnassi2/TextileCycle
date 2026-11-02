<?php

namespace Tests\Feature;

use App\Models\Association;
use App\Models\Donation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Informations détaillées d'une demande de don : référence, catégorie, description, date de soumission, adresse.
class DonationDetailsTest extends TestCase
{
    use RefreshDatabase;

    private User $member;

    private Association $association;

    protected function setUp(): void
    {
        parent::setUp();

        $this->member = User::factory()->create();
        $this->association = Association::factory()->for($this->member)->create(['status' => Association::STATUS_APPROVED]);
    }

    public function test_association_submits_a_detailed_request_and_gets_a_reference(): void
    {
        $this->travelTo(now()->setDate(2026, 11, 3));

        $this->actingAs($this->member)->post(route('association.donations.store'), [
            'quantity_items' => 40,
            'category' => 'Enfants',
            'description' => 'Manteaux et pulls 4-10 ans pour 30 familles avant l’hiver.',
            'delivery_address' => '15 Rue de Marseille',
            'delivery_city' => 'Tunis',
            'donation_date' => now()->addDays(3)->toDateString(),
        ])->assertRedirect(route('association.donations.index'));

        $donation = Donation::sole();
        $this->assertSame(sprintf('DON-2026-%04d', $donation->id), $donation->reference);
        $this->assertSame('Enfants', $donation->category);
        $this->assertSame('Tunis', $donation->delivery_city);
        $this->assertTrue($donation->submittedAt()->isSameDay(now()));

        $this->get(route('association.donations.show', $donation))
            ->assertOk()
            ->assertSee($donation->reference)
            ->assertSee('Soumise le 03/11/2026')
            ->assertSee('Manteaux et pulls 4-10 ans')
            ->assertSee('15 Rue de Marseille, Tunis');

        $this->get(route('association.donations.index'))->assertSee($donation->reference)->assertSee('Enfants');
    }

    public function test_new_fields_are_required_with_clear_messages(): void
    {
        $this->actingAs($this->member)->post(route('association.donations.store'), [
            'quantity_items' => 40,
            'category' => 'Animaux',
            'description' => 'court',
            'donation_date' => now()->addDays(3)->toDateString(),
        ])->assertSessionHasErrors([
            'category' => 'Catégorie invalide.',
            'description' => 'La description doit contenir au moins 10 caractères.',
            'delivery_address' => 'L’adresse de livraison est obligatoire.',
            'delivery_city' => 'La ville de livraison est obligatoire.',
        ]);

        $this->assertDatabaseCount('donations', 0);
    }

    public function test_new_request_form_reuses_the_last_delivery_address(): void
    {
        Donation::factory()->for($this->association)->create(['delivery_address' => '8 Rue Haffouz', 'delivery_city' => 'Sfax']);

        $this->actingAs($this->member)->get(route('association.donations.create'))
            ->assertOk()
            ->assertSee('value="8 Rue Haffouz"', false)
            ->assertSee('value="Sfax"', false);
    }

    public function test_reference_is_unique_and_kept_on_update(): void
    {
        $first = Donation::factory()->for($this->association)->create(['status' => Donation::STATUS_PENDING, 'donation_date' => now()->addDays(5)]);
        $second = Donation::factory()->for($this->association)->create();
        $this->assertNotSame($first->reference, $second->reference);

        $reference = $first->reference;
        $this->actingAs($this->member)->put(route('association.donations.update', $first), [
            'quantity_items' => 12,
            'category' => 'Bébés',
            'description' => 'Bodies et pyjamas 0-12 mois.',
            'delivery_address' => '1 Rue de la Liberté',
            'delivery_city' => 'Ariana',
            'donation_date' => now()->addDays(5)->toDateString(),
        ])->assertRedirect();

        $this->assertSame($reference, $first->fresh()->reference);
        $this->assertSame('Bébés', $first->fresh()->category);
    }

    public function test_admin_sees_details_and_searches_by_reference_category_or_city(): void
    {
        $admin = User::factory()->admin()->create();
        $kids = Donation::factory()->for($this->association)->create(['category' => 'Enfants', 'delivery_city' => 'Sousse']);
        $women = Donation::factory()->for($this->association)->create(['category' => 'Femmes', 'delivery_city' => 'Bizerte']);

        $this->actingAs($admin);

        $this->get(route('admin.donations.index', ['search' => strtolower($kids->reference)]))
            ->assertOk()->assertSee($kids->reference)->assertDontSee($women->reference);

        $this->get(route('admin.donations.index', ['category' => 'Femmes']))
            ->assertSee($women->reference)->assertDontSee($kids->reference);

        $this->get(route('admin.donations.index', ['search' => 'Bizerte']))
            ->assertSee($women->reference)->assertDontSee($kids->reference);

        $this->get(route('admin.donations.index', ['category' => 'Animaux']))->assertSessionHasErrors('category');

        $this->get(route('admin.donations.show', $kids))
            ->assertOk()
            ->assertSee($kids->reference)
            ->assertSee($kids->description)
            ->assertSee('Sousse');
    }
}
