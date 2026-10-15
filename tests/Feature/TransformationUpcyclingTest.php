<?php

namespace Tests\Feature;

use App\Models\MaterialBatch;
use App\Models\UpcycledProduct;
use App\Models\User;
use Database\Seeders\UpcyclingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransformationUpcyclingTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_batches_and_products(): void
    {
        $admin = User::factory()->admin()->create();
        $batch = MaterialBatch::factory()->create();
        $product = UpcycledProduct::factory()->for($batch)->create();

        $this->actingAs($admin);

        foreach ([
            route('admin.material-batches.index'),
            route('admin.material-batches.create'),
            route('admin.material-batches.show', $batch),
            route('admin.material-batches.edit', $batch),
            route('admin.upcycled-products.index'),
            route('admin.upcycled-products.create'),
            route('admin.upcycled-products.show', $product),
            route('admin.upcycled-products.edit', $product),
        ] as $url) {
            $this->get($url)->assertOk();
        }

        $this->post(route('admin.material-batches.store'), [
            'material_type' => 'Jean',
            'weight' => 24.5,
            'quality_grade' => 'A',
        ])->assertRedirect(route('admin.material-batches.index'));

        $newBatch = MaterialBatch::where('material_type', 'Jean')->firstOrFail();

        $this->post(route('admin.upcycled-products.store'), [
            'name' => 'Sac cabas du module',
            'price' => 39.9,
            'stock' => 12,
            'material_batch_id' => $newBatch->id,
        ])->assertRedirect(route('admin.upcycled-products.index'));

        $newProduct = UpcycledProduct::where('name', 'Sac cabas du module')->firstOrFail();

        $this->assertEquals($newBatch->id, $newProduct->material_batch_id);

        $this->put(route('admin.upcycled-products.update', $newProduct), [
            'name' => 'Sac cabas recyclé',
            'price' => 44,
            'stock' => 8,
            'material_batch_id' => $newBatch->id,
        ])->assertRedirect(route('admin.upcycled-products.index'));

        $this->assertDatabaseHas('upcycled_products', [
            'id' => $newProduct->id,
            'name' => 'Sac cabas recyclé',
            'stock' => 8,
        ]);
    }

    public function test_product_requires_a_valid_batch_and_batch_deletion_cascades(): void
    {
        $admin = User::factory()->admin()->create();
        $batch = MaterialBatch::factory()->create();
        $product = UpcycledProduct::factory()->for($batch)->create();

        $this->actingAs($admin)
            ->post(route('admin.upcycled-products.store'), [
                'name' => 'Produit sans lot valide',
                'price' => 20,
                'stock' => 1,
                'material_batch_id' => 999999,
            ])
            ->assertSessionHasErrors('material_batch_id');

        $this->delete(route('admin.material-batches.destroy', $batch))
            ->assertRedirect(route('admin.material-batches.index'));

        $this->assertDatabaseMissing('material_batches', ['id' => $batch->id]);
        $this->assertDatabaseMissing('upcycled_products', ['id' => $product->id]);
    }

    public function test_upcycling_seeder_creates_products_for_each_batch(): void
    {
        $this->seed(UpcyclingSeeder::class);

        $this->assertDatabaseCount('material_batches', 8);
        $this->assertGreaterThanOrEqual(8, UpcycledProduct::count());
        $this->assertFalse(MaterialBatch::doesntHave('upcycledProducts')->exists());
    }

    public function test_clients_can_browse_upcycled_products_without_authentication(): void
    {
        $batch = MaterialBatch::factory()->create([
            'material_type' => 'Coton',
            'quality_grade' => 'A',
        ]);
        $product = UpcycledProduct::factory()->for($batch)->create([
            'name' => 'Pochette cliente',
            'stock' => 4,
        ]);

        $this->get(route('products.index'))
            ->assertOk()
            ->assertSee('Pochette cliente')
            ->assertSee('Coton');

        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('Pochette cliente')
            ->assertSee('Disponible');
    }
}