<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['role' => 'required|in:user,admin'];
    }

    public function messages(): array
    {
        return ['role.required' => 'Le rôle est obligatoire.', 'role.in' => 'Le rôle sélectionné est invalide.'];
    }
}
