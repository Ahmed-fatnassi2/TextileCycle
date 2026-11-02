<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

// Règles d'un produit upcyclé : formulaire admin (création / modification) et import Excel.
class UpcycledProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return self::baseRules();
    }

    public static function baseRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'price' => ['required', 'numeric', 'min:0', 'decimal:0,2'],
            'stock' => ['required', 'integer', 'min:0'],
            'material_batch_id' => ['required', 'integer', 'exists:material_batches,id'],
        ];
    }
}
