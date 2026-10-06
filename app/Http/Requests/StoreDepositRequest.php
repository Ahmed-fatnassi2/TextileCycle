<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDepositRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
{
    $rules = [
        'deposit_point_id' => 'required|exists:deposit_points,id',
        'weight_kg' => 'required|numeric|min:0.1|max:500',
        'status' => 'required|in:Déposé,Trié,Rejeté',
        'state' => 'required|in:Neuf,Bon état,Usé,Déchiré',
        'deposit_date' => 'required|date|after_or_equal:today',
    ];

    // user_id obligatoire seulement pour le back office (admin)
    if ($this->routeIs('admin.*')) {
        $rules['user_id'] = 'required|exists:users,id';
    }

    return $rules;
}

    public function messages(): array
    {
        return [
            'deposit_point_id.required' => 'Vous devez sélectionner un point de collecte.',
            'weight_kg.required' => 'Le poids est obligatoire.',
            'weight_kg.numeric' => 'Le poids doit être un nombre.',
            'weight_kg.min' => 'Le poids minimum est 0.1 kg.',
            'deposit_date.after_or_equal' => 'La date doit être aujourd’hui ou dans le futur.',
        ];
    }
}