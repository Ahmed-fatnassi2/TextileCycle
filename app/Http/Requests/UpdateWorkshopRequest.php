<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWorkshopRequest extends StoreWorkshopRequest
{
    public function rules(): array
    {
        $workshop = $this->route('workshop');
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('workshops', 'name')->ignore($workshop->id)],
            'specialty' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'phone' => ['required', 'string', 'regex:/^\\d{2} ?\\d{3} ?\\d{3}$/'],
            'owner_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', User::ROLE_WORKSHOP_OWNER)),
            ],
        ];
    }
}
