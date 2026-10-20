<?php

namespace App\Http\Requests;

<<<<<<< HEAD
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
=======
use Illuminate\Foundation\Http\FormRequest;
>>>>>>> origin/Donation/Association-"aziz"

class UpdateUserRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
<<<<<<< HEAD
        return ['role' => ['required', Rule::in([User::ROLE_USER, User::ROLE_ADMIN, User::ROLE_WORKSHOP_OWNER])]];
=======
        return ['role' => 'required|in:user,admin'];
>>>>>>> origin/Donation/Association-"aziz"
    }

    public function messages(): array
    {
        return ['role.required' => 'Le rôle est obligatoire.', 'role.in' => 'Le rôle sélectionné est invalide.'];
    }
}
