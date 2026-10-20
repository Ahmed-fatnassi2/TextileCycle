<?php

namespace App\Http\Requests;

<<<<<<< HEAD
use App\Models\User;
=======
>>>>>>> origin/Donation/Association-"aziz"
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWorkshopRequest extends StoreWorkshopRequest
{
    public function rules(): array
    {
        $workshop = $this->route('workshop');
<<<<<<< HEAD
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
=======
        return ['name' => ['required', 'string', 'max:255', Rule::unique('workshops', 'name')->ignore($workshop->id)], 'specialty' => 'required|string|max:255', 'address' => 'required|string|max:255', 'phone' => ['required', 'string', 'regex:/^\\d{2} ?\\d{3} ?\\d{3}$/']];
>>>>>>> origin/Donation/Association-"aziz"
    }
}
