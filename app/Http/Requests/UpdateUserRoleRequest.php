<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['role' => ['required', Rule::in([User::ROLE_USER, User::ROLE_ADMIN, User::ROLE_WORKSHOP_OWNER])]];
    }

    public function messages(): array
    {
        return ['role.required' => 'Le rôle est obligatoire.', 'role.in' => 'Le rôle sélectionné est invalide.'];
    }
}
