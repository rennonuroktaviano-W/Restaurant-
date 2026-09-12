<?php

namespace App\Http\Requests;

use App\Support\Permissions;
use Illuminate\Foundation\Http\FormRequest;

class CashOnlineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(Permissions::PAYMENT_INITIATE) ?? false;
    }

    public function rules(): array
    {
        return [
            'payment_method_id' => ['required', 'exists:payment_methods,id'],
            'amount' => ['nullable', 'numeric', 'min:0', 'max:1000000000'],
        ];
    }

    public function messages(): array
    {
        return [
            'payment_method_id.required' => 'Metode pembayaran wajib dipilih',
            'payment_method_id.exists' => 'Metode pembayaran tidak valid',
            'amount.numeric' => 'Nominal harus berupa angka',
            'amount.min' => 'Nominal tidak boleh negatif',
        ];
    }
}
