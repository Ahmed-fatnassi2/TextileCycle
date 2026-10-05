<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDepositPointRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('deposit_point')->id;

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('deposit_points', 'name')->ignore($id)],
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'capacity' => 'required|integer|min:1|max:10000',
            'state' => 'required|in:Ouvert,Plein,En maintenance,Fermé',
        ];
    }
}