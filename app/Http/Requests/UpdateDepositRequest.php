<?php

namespace App\Http\Requests;

use App\Models\Deposit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDepositRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'deposit_point_id' => 'required|exists:deposit_points,id',
            'weight_kg' => 'required|numeric|min:0.1|max:500',
            'status' => ['required', Rule::in(Deposit::statuses())],
            'state' => ['required', Rule::in(Deposit::states())],
            'deposit_date' => 'required|date|after_or_equal:today',
        ];
    }
}