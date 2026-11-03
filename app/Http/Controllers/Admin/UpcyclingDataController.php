<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ImportTemplateExport;
use App\Exports\UpcyclingExport;
use App\Http\Controllers\Controller;
use App\Models\MaterialBatch;
use App\Services\UpcyclingImporter;
use App\Support\UpcyclingFilters;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

// Import / export Excel et rapports PDF du module "lots de matière et produits upcyclés".
class UpcyclingDataController extends Controller
{
    private const SESSION_KEY = 'upcycling_import';

    // ---------- Export Excel ----------

    public function export(Request $request): BinaryFileResponse
    {
        $filters = UpcyclingFilters::fromRequest($request);

        return Excel::download(new UpcyclingExport($filters), 'textilecycle-lots-produits-'.now()->format('Y-m-d').'.xlsx');
    }

    public function template(string $type): BinaryFileResponse
    {
        abort_unless(array_key_exists($type, UpcyclingImporter::types()), 404);

        return Excel::download(new ImportTemplateExport($type), "modele-import-{$type}.xlsx");
    }

    // ---------- Import Excel (envoi -> aperçu -> confirmation) ----------

    public function importForm(Request $request): View
    {
        return view('admin.upcycling.import', [
            'types' => UpcyclingImporter::types(),
            'selectedType' => $request->query('type', 'lots'),
        ]);
    }

    public function preview(Request $request, UpcyclingImporter $importer): View|RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(array_keys(UpcyclingImporter::types()))],
            'file' => ['required', 'file', 'max:5120', 'extensions:xlsx,xls,csv'],
        ], [
            'file.extensions' => 'Le fichier doit être au format .xlsx, .xls ou .csv.',
        ]);

        $this->discardPendingImport($request);

        // Nom aléatoire + extension d'origine (le format de lecture en dépend)
        $file = $request->file('file');
        $path = $file->storeAs('imports', Str::uuid().'.'.strtolower($file->getClientOriginalExtension()), 'local');

        try {
            $preview = $importer->preview($data['type'], Storage::disk('local')->path($path));
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($path);

            return back()->withErrors(['file' => 'Fichier illisible : vérifiez qu’il s’agit bien d’un fichier Excel ou CSV.']);
        }

        $request->session()->put(self::SESSION_KEY, [
            'path' => $path,
            'type' => $data['type'],
            'name' => $file->getClientOriginalName(),
        ]);

        return view('admin.upcycling.import-preview', [
            'preview' => $preview,
            'fileName' => $file->getClientOriginalName(),
            'typeLabel' => UpcyclingImporter::types()[$data['type']],
        ]);
    }

    public function confirm(Request $request, UpcyclingImporter $importer): RedirectResponse
    {
        $pending = $request->session()->pull(self::SESSION_KEY);

        if (! $pending || ! Storage::disk('local')->exists($pending['path'])) {
            return redirect()->route('admin.upcycling.import')->with('error', 'Aucun import en attente. Envoyez à nouveau le fichier.');
        }

        $counts = $importer->import($pending['type'], Storage::disk('local')->path($pending['path']));
        Storage::disk('local')->delete($pending['path']);

        $route = $pending['type'] === 'lots' ? 'admin.material-batches.index' : 'admin.upcycled-products.index';
        $message = "Import terminé : {$counts['created']} créé(s), {$counts['updated']} mis à jour";
        $message .= $counts['skipped'] ? ", {$counts['skipped']} ligne(s) ignorée(s) car invalides." : '.';

        return redirect()->route($route)->with('success', $message);
    }

    public function cancel(Request $request): RedirectResponse
    {
        $this->discardPendingImport($request);

        return redirect()->route('admin.upcycling.import')->with('success', 'Import annulé.');
    }

    // ---------- Rapports PDF ----------

    public function report(Request $request): Response
    {
        $filters = UpcyclingFilters::fromRequest($request);
        $batches = UpcyclingFilters::batches($filters)->with('upcycledProducts')->orderBy('material_type')->orderBy('id')->get();

        return $this->pdf($batches, 'Rapport des lots de matière et produits upcyclés', $filters, $request)
            ->stream('rapport-upcycling-'.now()->format('Y-m-d').'.pdf');
    }

    public function batchReport(Request $request, MaterialBatch $materialBatch): Response
    {
        $materialBatch->load('upcycledProducts');

        return $this->pdf(collect([$materialBatch]), "Fiche du lot #{$materialBatch->id} · {$materialBatch->material_type}", [], $request)
            ->stream("lot-{$materialBatch->id}.pdf");
    }

    private function pdf(Collection $batches, string $title, array $filters, Request $request): \Barryvdh\DomPDF\PDF
    {
        $products = $batches->flatMap->upcycledProducts;
        $value = fn ($items) => $items->sum(fn ($p) => (float) $p->price * $p->stock);

        $stats = [
            'batches' => $batches->count(),
            'weight' => (float) $batches->sum('weight'),
            'products' => $products->count(),
            'units' => (int) $products->sum('stock'),
            'value' => $value($products),
        ];

        $byMaterial = $batches->groupBy('material_type')->map(fn ($group) => [
            'batches' => $group->count(),
            'weight' => (float) $group->sum('weight'),
            'products' => $group->flatMap->upcycledProducts->count(),
            'units' => (int) $group->flatMap->upcycledProducts->sum('stock'),
            'value' => $value($group->flatMap->upcycledProducts),
        ])->sortKeys();

        return Pdf::loadView('pdf.upcycling-report', [
            'title' => $title,
            'batches' => $batches,
            'stats' => $stats,
            'byMaterial' => $byMaterial,
            'filters' => $filters,
            'generatedAt' => now(),
            'author' => $request->user()->name,
        ])
            ->setPaper('a4')
            // N'embarque que les caractères utilisés de la police (sinon ~900 Ko par PDF)
            ->setOption('isFontSubsettingEnabled', true);
    }

    private function discardPendingImport(Request $request): void
    {
        if ($pending = $request->session()->pull(self::SESSION_KEY)) {
            Storage::disk('local')->delete($pending['path']);
        }
    }
}
