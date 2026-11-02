<?php

namespace App\Http\Requests;

use App\Models\Deposit;
use App\Models\DepositPoint;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'state' => ['required', Rule::in(Deposit::states())],
            'deposit_date' => 'required|date|after_or_equal:today',
        ];

        // Back office uniquement : l'admin choisit le citoyen et le statut.
        // Côté citoyen, le statut est imposé par le serveur ("En attente").
        if ($this->routeIs('admin.*')) {
            $rules['user_id'] = 'required|exists:users,id';
            $rules['status'] = ['required', Rule::in(Deposit::statuses())];
        } else {
            // Un citoyen ne peut réserver que sur un point ouvert.
            $rules['deposit_point_id'] = [
                'required',
                Rule::exists('deposit_points', 'id')->where('state', DepositPoint::STATE_OUVERT),
            ];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'deposit_point_id.required' => 'Vous devez sélectionner un point de collecte.',
            'deposit_point_id.exists' => 'Ce point de collecte n’accepte pas de dépôts pour le moment.',
            'weight_kg.required' => 'Le poids est obligatoire.',
            'weight_kg.numeric' => 'Le poids doit être un nombre.',
            'weight_kg.min' => 'Le poids minimum est 0.1 kg.',
            'deposit_date.after_or_equal' => 'La date doit être aujourd’hui ou dans le futur.',
        ];
    }
}