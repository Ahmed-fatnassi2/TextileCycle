<?php

namespace App\Services;

use App\Http\Requests\StoreMaterialBatchRequest;
use App\Http\Requests\UpcycledProductRequest;
use App\Imports\HeadingRowReader;
use App\Models\MaterialBatch;
use App\Models\UpcycledProduct;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Import Excel / CSV des lots de matière et des produits upcyclés, en deux temps :
 * preview() valide chaque ligne sans rien enregistrer, import() enregistre les lignes valides.
 * Une ligne avec un ID existant met à jour l'enregistrement, sinon elle en crée un.
 */
class UpcyclingImporter
{
    public const MAX_ROWS = 1000;

    // Colonnes attendues (en-têtes après normalisation) -> champ en base
    private const COLUMNS = [
        'lots' => [
            'type_matiere' => 'material_type',
            'poids_kg' => 'weight',
            'qualite' => 'quality_grade',
        ],
        'produits' => [
            'nom' => 'name',
            'description' => 'description',
            'prix_tnd' => 'price',
            'stock' => 'stock',
            'lot_id' => 'material_batch_id',
        ],
    ];

    private const OPTIONAL = ['description'];

    // Libellés des champs dans l'aperçu
    public const LABELS = [
        'material_type' => 'Type matière', 'weight' => 'Poids (kg)', 'quality_grade' => 'Qualité',
        'name' => 'Nom', 'description' => 'Description', 'price' => 'Prix (TND)', 'stock' => 'Stock',
        'material_batch_id' => 'Lot ID',
    ];

    public static function types(): array
    {
        return ['lots' => 'Lots de matière', 'produits' => 'Produits upcyclés'];
    }

    /**
     * @return array{type: string, rows: array, valid: int, invalid: int, error: ?string}
     */
    public function preview(string $type, string $path): array
    {
        $sheet = $this->readSheet($type, $path);

        if ($sheet === null) {
            $required = array_diff(array_keys(self::COLUMNS[$type]), self::OPTIONAL);

            return $this->result($type, [], 'Colonnes introuvables. Le fichier doit contenir : '.implode(', ', $required).' (téléchargez le modèle).');
        }

        $rows = [];
        foreach ($sheet as $index => $raw) {
            if ($this->isBlank($raw)) {
                continue;
            }

            if (count($rows) >= self::MAX_ROWS) {
                return $this->result($type, $rows, 'Le fichier dépasse '.self::MAX_ROWS.' lignes : seules les premières ont été lues.');
            }

            $rows[] = $this->validateRow($type, $raw, $index + 2); // ligne 1 = en-têtes
        }

        return $this->result($type, $rows, $rows ? null : 'Le fichier ne contient aucune ligne de données.');
    }

    /**
     * Enregistre les lignes valides (revalidées au moment de l'import).
     *
     * @return array{created: int, updated: int, skipped: int}
     */
    public function import(string $type, string $path): array
    {
        $preview = $this->preview($type, $path);
        $model = $type === 'lots' ? MaterialBatch::class : UpcycledProduct::class;
        $counts = ['created' => 0, 'updated' => 0, 'skipped' => $preview['invalid']];

        DB::transaction(function () use ($preview, $model, &$counts) {
            foreach ($preview['rows'] as $row) {
                if ($row['errors']) {
                    continue;
                }

                if ($row['id']) {
                    $model::findOrFail($row['id'])->update($row['data']);
                    $counts['updated']++;
                } else {
                    $model::create($row['data']);
                    $counts['created']++;
                }
            }
        });

        return $counts;
    }

    private function validateRow(string $type, array $raw, int $line): array
    {
        $data = [];
        foreach (self::COLUMNS[$type] as $column => $field) {
            $data[$field] = $this->normalize($field, $raw[$column] ?? null);
        }

        $id = $this->normalize('id', $raw['id'] ?? null);
        $table = $type === 'lots' ? 'material_batches' : 'upcycled_products';

        $rules = $type === 'lots' ? (new StoreMaterialBatchRequest)->rules() : UpcycledProductRequest::baseRules();
        $messages = $type === 'lots' ? (new StoreMaterialBatchRequest)->messages() : [];

        $validator = Validator::make(
            [...$data, 'id' => $id],
            [...$rules, 'id' => ['nullable', 'integer', "exists:{$table},id"]],
            [...$messages, 'id.exists' => 'Aucun enregistrement avec cet ID (laissez la colonne vide pour créer).'],
            ['material_batch_id' => 'lot_id', 'material_type' => 'type_matiere', 'weight' => 'poids_kg', 'quality_grade' => 'qualite', 'price' => 'prix_tnd', 'name' => 'nom'],
        );

        return [
            'line' => $line,
            'id' => $id,
            'action' => $id ? 'update' : 'create',
            'data' => $data,
            'errors' => $validator->errors()->all(),
        ];
    }

    private function normalize(string $field, mixed $value): mixed
    {
        if (is_string($value)) {
            $value = trim($value);
        }
        if ($value === '' || $value === null) {
            return null;
        }

        return match ($field) {
            // "24,5" (Excel français) -> 24.5
            'weight', 'price' => is_numeric($n = str_replace([',', ' '], ['.', ''], (string) $value)) ? round((float) $n, 2) : $value,
            'stock', 'material_batch_id', 'id' => is_numeric($value) && floor((float) $value) == $value ? (int) $value : $value,
            'quality_grade' => strtoupper((string) $value),
            default => is_string($value) ? $value : (string) $value,
        };
    }

    // Choisit la feuille qui contient les colonnes du type demandé (un export contient "Lots" et "Produits")
    private function readSheet(string $type, string $path): ?array
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $readerType = match ($extension) {
            'csv' => ExcelFormat::CSV,
            'xls' => ExcelFormat::XLS,
            default => ExcelFormat::XLSX,
        };

        if ($readerType === ExcelFormat::CSV) {
            // Excel en français enregistre les CSV avec des points-virgules
            $firstLine = (string) (new \SplFileObject($path))->fgets();
            Config::set('excel.imports.csv.delimiter', substr_count($firstLine, ';') > substr_count($firstLine, ',') ? ';' : ',');
        }

        $required = array_diff(array_keys(self::COLUMNS[$type]), self::OPTIONAL);

        foreach (Excel::toArray(new HeadingRowReader, $path, null, $readerType) as $sheet) {
            $headings = array_keys($sheet[0] ?? []);
            if ($sheet && ! array_diff($required, $headings)) {
                return $sheet;
            }
        }

        return null;
    }

    private function isBlank(array $row): bool
    {
        foreach ($row as $value) {
            if ($value !== null && trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    private function result(string $type, array $rows, ?string $error): array
    {
        $invalid = count(array_filter($rows, fn ($r) => $r['errors']));

        return [
            'type' => $type,
            'rows' => $rows,
            'valid' => count($rows) - $invalid,
            'invalid' => $invalid,
            'error' => $error,
        ];
    }
}
