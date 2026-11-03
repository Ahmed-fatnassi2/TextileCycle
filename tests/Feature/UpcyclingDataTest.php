<?php

namespace Tests\Feature;

use App\Models\MaterialBatch;
use App\Models\UpcycledProduct;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class UpcyclingDataTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();
        Storage::fake('local');
        $this->admin = User::factory()->admin()->create();
    }

    // ---------- Export Excel ----------

    public function test_export_contains_two_sheets_and_respects_filters(): void
    {
        $jean = MaterialBatch::factory()->create(['material_type' => 'Jean', 'weight' => 24.5, 'quality_grade' => 'A']);
        UpcycledProduct::factory()->for($jean)->create(['name' => 'Sac cabas', 'price' => 40, 'stock' => 3]);
        $laine = MaterialBatch::factory()->create(['material_type' => 'Laine']);
        UpcycledProduct::factory()->for($laine)->create(['name' => 'Écharpe']);

        $response = $this->actingAs($this->admin)->get(route('admin.upcycling.export', ['material_type' => 'Jean']));
        $response->assertOk();

        $spreadsheet = IOFactory::load($response->getFile()->getPathname());
        $this->assertSame(['Lots', 'Produits'], $spreadsheet->getSheetNames());

        $lots = $spreadsheet->getSheetByName('Lots')->toArray();
        $this->assertSame(['ID', 'Type matière', 'Poids (kg)', 'Qualité', 'Nb produits', 'Créé le'], $lots[0]);
        $this->assertCount(2, $lots); // en-tête + 1 lot (Laine filtré)
        $this->assertSame('Jean', $lots[1][1]);

        $products = $spreadsheet->getSheetByName('Produits')->toArray();
        $this->assertCount(2, $products);
        $this->assertSame('Sac cabas', $products[1][1]);
        $this->assertEquals(120, $products[1][7]); // valeur du stock = 40 × 3
    }

    public function test_templates_can_be_downloaded(): void
    {
        $this->actingAs($this->admin);

        $this->get(route('admin.upcycling.template', 'lots'))->assertOk()->assertDownload('modele-import-lots.xlsx');
        $this->get(route('admin.upcycling.template', 'produits'))->assertOk()->assertDownload('modele-import-produits.xlsx');
        $this->get(route('admin.upcycling.template', 'autre'))->assertNotFound();
    }

    // ---------- Import ----------

    public function test_import_preview_validates_each_row_without_saving(): void
    {
        $csv = "ID,Type matière,Poids (kg),Qualité\n"
            .",Jean,\"24,5\",a\n"   // virgule décimale + qualité en minuscule : normalisées
            .",,10,A\n"             // type manquant
            .",Lin,-3,B\n"          // poids négatif
            .",,,\n"                // ligne vide ignorée
            ."999,Coton,5,A\n";     // ID inconnu

        $response = $this->actingAs($this->admin)->post(route('admin.upcycling.import.preview'), [
            'type' => 'lots',
            'file' => UploadedFile::fake()->createWithContent('lots.csv', $csv),
        ])->assertOk();

        $preview = $response->viewData('preview');
        $this->assertSame(1, $preview['valid']);
        $this->assertSame(3, $preview['invalid']);
        $this->assertSame(['material_type' => 'Jean', 'weight' => 24.5, 'quality_grade' => 'A'], $preview['rows'][0]['data']);
        $this->assertSame(3, $preview['rows'][1]['line']);
        $this->assertNotEmpty($preview['rows'][3]['errors']);

        $this->assertDatabaseCount('material_batches', 0);
    }

    public function test_confirm_imports_only_valid_rows_and_updates_rows_with_an_id(): void
    {
        $existing = MaterialBatch::factory()->create(['material_type' => 'Jean', 'weight' => 10, 'quality_grade' => 'B']);

        // CSV "à la française" : séparateur point-virgule
        $csv = "ID;Type matière;Poids (kg);Qualité\n"
            ."{$existing->id};Jean;12,75;A\n"
            .";Laine;8;B\n"
            .";Coton;abc;A\n";

        $this->actingAs($this->admin)->post(route('admin.upcycling.import.preview'), [
            'type' => 'lots',
            'file' => UploadedFile::fake()->createWithContent('lots.csv', $csv),
        ])->assertOk();

        $this->post(route('admin.upcycling.import.confirm'))
            ->assertRedirect(route('admin.material-batches.index'))
            ->assertSessionHas('success', 'Import terminé : 1 créé(s), 1 mis à jour, 1 ligne(s) ignorée(s) car invalides.');

        $this->assertEquals(12.75, $existing->refresh()->weight);
        $this->assertSame('A', $existing->quality_grade);
        $this->assertDatabaseHas('material_batches', ['material_type' => 'Laine']);
        $this->assertDatabaseMissing('material_batches', ['material_type' => 'Coton']);
        $this->assertEmpty(Storage::disk('local')->allFiles('imports'));
    }

    public function test_an_exported_workbook_can_be_edited_and_reimported(): void
    {
        $batch = MaterialBatch::factory()->create(['material_type' => 'Jean']);
        $product = UpcycledProduct::factory()->for($batch)->create(['name' => 'Pochette', 'price' => 15, 'stock' => 4]);

        $export = $this->actingAs($this->admin)->get(route('admin.upcycling.export'));
        $spreadsheet = IOFactory::load($export->getFile()->getPathname());
        $spreadsheet->getSheetByName('Produits')->setCellValue('E2', 25); // stock 4 -> 25
        $path = tempnam(sys_get_temp_dir(), 'tc').'.xlsx';
        IOFactory::createWriter($spreadsheet, 'Xlsx')->save($path);

        // Le classeur contient 2 feuilles : l'import "produits" doit trouver la bonne
        $this->post(route('admin.upcycling.import.preview'), [
            'type' => 'produits',
            'file' => new UploadedFile($path, 'export.xlsx', null, null, true),
        ])->assertOk()->assertViewHas('preview', fn ($p) => $p['valid'] === 1 && $p['rows'][0]['action'] === 'update');

        $this->post(route('admin.upcycling.import.confirm'))->assertRedirect(route('admin.upcycled-products.index'));

        $this->assertSame(25, $product->refresh()->stock);
        $this->assertDatabaseCount('upcycled_products', 1);
        @unlink($path);
    }

    public function test_product_import_rejects_unknown_batches(): void
    {
        $csv = "Nom,Prix (TND),Stock,Lot ID\nSac,30,5,12345\n";

        $this->actingAs($this->admin)->post(route('admin.upcycling.import.preview'), [
            'type' => 'produits',
            'file' => UploadedFile::fake()->createWithContent('produits.csv', $csv),
        ])->assertViewHas('preview', fn ($p) => $p['valid'] === 0 && $p['invalid'] === 1);
    }

    public function test_file_with_wrong_columns_or_extension_is_rejected(): void
    {
        $this->actingAs($this->admin)->post(route('admin.upcycling.import.preview'), [
            'type' => 'lots',
            'file' => UploadedFile::fake()->createWithContent('autre.csv', "a,b,c\n1,2,3\n"),
        ])->assertViewHas('preview', fn ($p) => str_contains($p['error'], 'Colonnes introuvables'));

        $this->post(route('admin.upcycling.import.preview'), [
            'type' => 'lots',
            'file' => UploadedFile::fake()->create('photo.png', 10),
        ])->assertSessionHasErrors('file');
    }

    public function test_confirm_without_a_pending_import_redirects(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.upcycling.import.confirm'))
            ->assertRedirect(route('admin.upcycling.import'))
            ->assertSessionHas('error');
    }

    // ---------- PDF ----------

    public function test_global_and_batch_pdf_reports_are_generated(): void
    {
        $batch = MaterialBatch::factory()->create(['material_type' => 'Jean']);
        UpcycledProduct::factory()->for($batch)->create(['name' => 'Sac cabas']);

        $this->actingAs($this->admin);

        $report = $this->get(route('admin.upcycling.report', ['material_type' => 'Jean']));
        $report->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $report->getContent());

        $single = $this->get(route('admin.material-batches.pdf', $batch));
        $single->assertOk()->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_upcycling_tools_are_admin_only(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('admin.upcycling.export'))->assertForbidden();
        $this->get(route('admin.upcycling.report'))->assertForbidden();
        $this->get(route('admin.upcycling.import'))->assertForbidden();
        $this->get(route('admin.upcycling.ideas'))->assertForbidden();
        $this->post(route('admin.upcycling.ideas.suggest'), ['material_type' => 'Jean'])->assertForbidden();
        Http::assertNothingSent();
    }

    // ---------- Idées IA : admin, à partir du stock ----------

    public function test_stock_ideas_use_all_batches_of_the_material_and_are_validated(): void
    {
        $big = MaterialBatch::factory()->create(['material_type' => 'Jean', 'weight' => 12, 'quality_grade' => 'A']);
        $small = MaterialBatch::factory()->create(['material_type' => 'Jean', 'weight' => 5, 'quality_grade' => 'B']);
        UpcycledProduct::factory()->for($small)->create(['name' => 'Sac cabas']);
        MaterialBatch::factory()->create(['material_type' => 'Laine', 'weight' => 50]);

        $this->fakeAi(['ideas' => [
            $this->idea('Pochette zippée', 0.15, 20, 25),
            $this->idea('sac CABAS', 0.6, 10, 40),   // déjà fabriqué avec cette matière (casse ignorée)
            $this->idea('Plaid géant', 30, 1, 120),  // plus de matière que le plus gros lot
            $this->idea('Tablier', 0.5, 100, 45),    // stock limité au maximum possible
            $this->idea('Coussin', 0.4, 5, 5000),    // prix hors limites
        ]]);

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.upcycling.ideas.suggest'), ['material_type' => 'Jean'])
            ->assertOk()
            ->assertJsonPath('material.total_kg', 17)
            ->assertJsonPath('material.batches', 2)
            ->assertJsonPath('discarded', 3);

        $ideas = $response->json('ideas');
        $this->assertSame(['Pochette zippée', 'Tablier'], array_column($ideas, 'name'));

        // Rattaché au plus gros lot ; quantités calculées par le serveur
        $this->assertSame($big->id, $ideas[0]['batch']['id']);
        $this->assertSame(80, $ideas[0]['units_possible']);  // floor(12 / 0.15)
        $this->assertSame(24, $ideas[1]['suggested_stock']); // 100 demandé, 24 possibles
        $this->assertEquals(12, $ideas[1]['kg_used']);

        // "Créer ce produit" ouvre le formulaire prérempli
        $this->get($ideas[0]['create_url'])
            ->assertOk()
            ->assertSee('value="Pochette zippée"', false)
            ->assertSee('Une pochette en jean récupéré.');

        Http::assertSent(function (Request $request) {
            $context = json_decode($request['messages'][1]['content'], true);

            return $request['response_format']['json_schema']['name'] === 'upcycling_stock_ideas'
                && $context['material']['largest_batch_kg'] == 12
                && $context['products_already_made_with_this_material'] === ['Sac cabas'];
        });
    }

    public function test_up_to_six_ideas_are_returned_for_stock(): void
    {
        MaterialBatch::factory()->has(UpcycledProduct::factory()->state(['name' => 'Existant']))
            ->create(['material_type' => 'Jean', 'weight' => 50]);

        $this->fakeAi(['ideas' => array_map(fn ($i) => $this->idea("Produit {$i}", 0.5, 10, 30), range(1, 8))]);
        $this->actingAs($this->admin)
            ->postJson(route('admin.upcycling.ideas.suggest'), ['material_type' => 'Jean'])
            ->assertOk()
            ->assertJsonCount(6, 'ideas');

        // 6 idées détaillées : la limite de longueur de réponse est relevée
        Http::assertSent(fn (Request $request) => $request['max_completion_tokens'] === 6000);
    }

    public function test_up_to_six_ideas_are_returned_for_visitors(): void
    {
        $this->fakeAi([
            'is_textile' => true,
            'message' => 'Bonne idée !',
            'catalog_material_type' => '',
            'ideas' => array_map(fn ($i) => $this->diyIdea("Idée {$i}", ['a', 'b', 'c']), range(1, 8)),
        ]);
        $this->postJson(route('products.ideas.suggest'), ['material' => 'un vieux jean'])
            ->assertOk()
            ->assertJsonCount(6, 'ideas');
    }

    // ---------- Pagination dynamique ----------

    public function test_next_page_excludes_ideas_already_shown_for_stock(): void
    {
        MaterialBatch::factory()->create(['material_type' => 'Jean', 'weight' => 50]);

        $this->fakeAi(['ideas' => [
            $this->idea('Pochette', 0.2, 10, 20),    // déjà affichée page 1 : écartée
            $this->idea('TROUSSE ', 0.2, 10, 20),    // déjà affichée (casse et espaces ignorés) : écartée
            $this->idea('Tablier', 0.5, 10, 40),     // nouvelle
        ]]);

        $this->actingAs($this->admin)
            ->postJson(route('admin.upcycling.ideas.suggest'), ['material_type' => 'Jean', 'exclude' => ['Pochette', 'Trousse']])
            ->assertOk()
            ->assertJsonCount(1, 'ideas')
            ->assertJsonPath('ideas.0.name', 'Tablier');

        // L'IA reçoit la liste pour ne pas les reproposer
        Http::assertSent(fn (Request $request) => json_decode($request['messages'][1]['content'], true)['ideas_already_proposed'] === ['Pochette', 'Trousse']);
    }

    public function test_next_page_excludes_ideas_already_shown_for_visitors(): void
    {
        $this->fakeAi([
            'is_textile' => true,
            'message' => 'Encore des idées !',
            'catalog_material_type' => '',
            'ideas' => [$this->diyIdea('Trousse', ['a', 'b', 'c']), $this->diyIdea('Coussin', ['a', 'b', 'c'])],
        ]);

        $this->postJson(route('products.ideas.suggest'), ['material' => 'un vieux jean', 'exclude' => ['Trousse']])
            ->assertOk()
            ->assertJsonCount(1, 'ideas')
            ->assertJsonPath('ideas.0.name', 'Coussin');

        Http::assertSent(fn (Request $request) => json_decode($request['messages'][1]['content'], true)['ideas_already_proposed'] === ['Trousse']);
    }

    public function test_dynamic_pagination_is_capped(): void
    {
        MaterialBatch::factory()->create(['material_type' => 'Jean']);
        $tooMany = array_map(fn ($i) => "Idée {$i}", range(1, 31));

        $this->actingAs($this->admin)
            ->postJson(route('admin.upcycling.ideas.suggest'), ['material_type' => 'Jean', 'exclude' => $tooMany])
            ->assertJsonValidationErrors('exclude');

        $this->postJson(route('products.ideas.suggest'), ['material' => 'jean', 'exclude' => $tooMany])
            ->assertJsonValidationErrors('exclude');

        Http::assertNothingSent();
    }

    public function test_stock_ideas_require_a_material_in_stock(): void
    {
        $this->actingAs($this->admin)
            ->postJson(route('admin.upcycling.ideas.suggest'), ['material_type' => 'Soie'])
            ->assertJsonValidationErrors('material_type');

        Http::assertNothingSent();
    }

    public function test_ai_errors_return_a_friendly_503(): void
    {
        MaterialBatch::factory()->create(['material_type' => 'Jean']);
        Http::fake(['api.openai.com/*' => Http::response([], 500)]);

        $this->actingAs($this->admin)
            ->postJson(route('admin.upcycling.ideas.suggest'), ['material_type' => 'Jean'])
            ->assertStatus(503);

        $this->postJson(route('products.ideas.suggest'), ['material' => 'un vieux jean'])->assertStatus(503);
    }

    public function test_admin_ideas_page_lists_materials_and_is_linked_from_lists_and_batches(): void
    {
        $batch = MaterialBatch::factory()->create(['material_type' => 'Lin', 'weight' => 40]);
        MaterialBatch::factory()->create(['material_type' => 'Lin', 'weight' => 10]);
        $this->actingAs($this->admin);

        $this->get(route('admin.upcycling.ideas', ['material_type' => 'Lin']))
            ->assertOk()
            ->assertSee('data-upcycling-ideas', false)
            ->assertSee('Lin · 2 lot(s) · 50,0 kg');

        $this->get(route('admin.material-batches.index'))->assertSee(route('admin.upcycling.ideas'));
        $this->get(route('admin.upcycled-products.index'))->assertSee(route('admin.upcycling.ideas'));
        $this->get(route('admin.material-batches.show', $batch))
            ->assertSee(route('admin.upcycling.ideas', ['material_type' => 'Lin']), false)
            ->assertSee(route('admin.material-batches.pdf', $batch));
    }

    // ---------- Idées IA : public, matière du visiteur ----------

    public function test_public_ideas_page_is_open_to_visitors_and_linked_from_the_catalog(): void
    {
        $this->get(route('products.index'))->assertOk()->assertSee(route('products.ideas'));
        $this->get(route('products.ideas'))->assertOk()->assertSee('Que créer avec ma matière ?');
    }

    public function test_visitor_gets_diy_ideas_and_catalog_products_in_the_same_material(): void
    {
        $jean = MaterialBatch::factory()->create(['material_type' => 'Jean']);
        UpcycledProduct::factory()->for($jean)->create(['name' => 'Sac en jean', 'stock' => 3]);
        UpcycledProduct::factory()->for($jean)->create(['name' => 'Épuisé', 'stock' => 0]);
        UpcycledProduct::factory()->for(MaterialBatch::factory()->state(['material_type' => 'Laine']))->create(['name' => 'Bonnet']);

        $this->fakeAi([
            'is_textile' => true,
            'message' => 'Votre jean a encore de belles années devant lui !',
            'catalog_material_type' => 'Jean',
            'ideas' => [
                $this->diyIdea('Trousse', ['Découper', 'Coudre', 'Retourner']),
                $this->diyIdea('Idée vide', ['Une seule étape']), // moins de 2 étapes : écartée
                $this->diyIdea('Tote bag', ['Couper les jambes', 'Coudre le fond', 'Ajouter les anses']),
            ],
        ]);

        $this->postJson(route('products.ideas.suggest'), ['material' => '2 vieux jeans'])
            ->assertOk()
            ->assertJsonPath('is_textile', true)
            ->assertJsonPath('ideas.0.name', 'Trousse')
            ->assertJsonPath('ideas.1.name', 'Tote bag')
            ->assertJsonCount(2, 'ideas')
            ->assertJsonCount(1, 'catalog')
            ->assertJsonPath('catalog.0.name', 'Sac en jean');

        // La saisie du visiteur est envoyée comme donnée JSON, et la liste des matières limite le choix de l'IA
        Http::assertSent(function (Request $request) {
            $schema = $request['response_format']['json_schema']['schema'];

            return json_decode($request['messages'][1]['content'], true)['visitor_material'] === '2 vieux jeans'
                && $schema['properties']['catalog_material_type']['enum'] === ['', 'Jean', 'Laine'];
        });
    }

    public function test_non_textile_input_returns_only_the_explanation(): void
    {
        MaterialBatch::factory()->has(UpcycledProduct::factory())->create(['material_type' => 'Jean']);

        $this->fakeAi([
            'is_textile' => false,
            'message' => 'Décrivez plutôt un vêtement ou un tissu.',
            'catalog_material_type' => 'Jean',
            'ideas' => [$this->diyIdea('Ignorée', ['a', 'b', 'c'])],
        ]);

        $this->postJson(route('products.ideas.suggest'), ['material' => 'une bouteille en plastique'])
            ->assertOk()
            ->assertJsonPath('is_textile', false)
            ->assertJsonPath('ideas', [])
            ->assertJsonPath('catalog', []);
    }

    public function test_visitor_input_is_validated(): void
    {
        $this->postJson(route('products.ideas.suggest'), ['material' => ''])->assertJsonValidationErrors('material');
        $this->postJson(route('products.ideas.suggest'), ['material' => str_repeat('a', 101)])->assertJsonValidationErrors('material');
        Http::assertNothingSent();
    }

    private function fakeAi(array $content): void
    {
        Http::fake(['api.openai.com/*' => Http::response(['choices' => [['message' => ['content' => json_encode($content)]]]])]);
    }

    private function idea(string $name, float $kg, int $stock, float $price): array
    {
        return [
            'name' => $name,
            'description' => 'Une pochette en jean récupéré.',
            'kg_per_unit' => $kg,
            'suggested_stock' => $stock,
            'suggested_price' => $price,
            'difficulty' => 'facile',
            'reasoning' => 'Adapté au jean de qualité A.',
        ];
    }

    private function diyIdea(string $name, array $steps): array
    {
        return [
            'name' => $name,
            'description' => 'Un objet utile en jean récupéré.',
            'difficulty' => 'facile',
            'time_needed' => '1 h',
            'steps' => $steps,
        ];
    }
}
