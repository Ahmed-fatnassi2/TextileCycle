<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWorkshopRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return ['name' => 'required|string|max:255|unique:workshops,name', 'specialty' => 'required|string|max:255', 'address' => 'required|string|max:255', 'phone' => ['required', 'string', 'regex:/^\\d{2} ?\\d{3} ?\\d{3}$/']];
    }

    public function messages(): array
    {
        return ['name.required' => 'Le nom est obligatoire.', 'name.string' => 'Le nom doit être du texte.', 'name.max' => 'Le nom ne peut pas dépasser 255 caractères.', 'name.unique' => 'Cet atelier existe déjà.', 'specialty.required' => 'La spécialité est obligatoire.', 'specialty.string' => 'La spécialité doit être du texte.', 'specialty.max' => 'La spécialité ne peut pas dépasser 255 caractères.', 'address.required' => "L'adresse est obligatoire.", 'address.string' => "L'adresse doit être du texte.", 'address.max' => "L'adresse ne peut pas dépasser 255 caractères.", 'phone.required' => 'Le téléphone est obligatoire.', 'phone.string' => 'Le téléphone doit être du texte.', 'phone.regex' => 'Le téléphone doit contenir 8 chiffres, espaces autorisés.'];
    }

    public function attributes(): array { return ['name' => 'nom', 'specialty' => 'spécialité', 'address' => 'adresse', 'phone' => 'téléphone']; }
}
