<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMaterialBatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'material_type' => ['required', 'string', 'max:255'],
            'weight' => ['required', 'numeric', 'gt:0', 'decimal:0,2'],
            'quality_grade' => ['required', 'string', 'max:10', 'regex:/^[A-Z]+$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'material_type.required' => 'Le type de matière est obligatoire.',
            'weight.required' => 'Le poids est obligatoire.',
            'weight.numeric' => 'Le poids doit être un nombre.',
            'weight.gt' => 'Le poids doit être supérieur à zéro.',
            'quality_grade.required' => 'La qualité est obligatoire.',
            'quality_grade.regex' => 'La qualité doit contenir uniquement des lettres majuscules (ex. A, AA, PREMIUM).',
            'quality_grade.max' => 'La qualité ne peut pas dépasser 10 lettres.',
        ];
    }
}
