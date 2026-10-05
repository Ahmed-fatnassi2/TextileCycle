<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDepositPointRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:deposit_points,name',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'capacity' => 'required|integer|min:1|max:10000',
            'state' => 'required|in:Ouvert,Plein,En maintenance,Fermé',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Le nom du point est obligatoire.',
            'name.unique' => 'Ce nom de point existe déjà.',
            'address.required' => 'L\'adresse est obligatoire.',
            'city.required' => 'La ville est obligatoire.',
            'capacity.required' => 'La capacité est obligatoire.',
            'capacity.integer' => 'La capacité doit être un nombre.',
            'state.required' => 'L\'état est obligatoire.',
        ];
    }
}