<?php

namespace App\Http\Requests;

use App\Support\Permissions;
use Illuminate\Foundation\Http\FormRequest;

class CashConfirmRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(Permissions::PAYMENT_CONFIRM_CASH) ?? false;
    }

    public function rules(): array
    {
        return [
            'amount_received' => ['required', 'numeric', 'min:0', 'max:1000000000'],
            'payment_method_id' => ['required', 'exists:payment_methods,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'amount_received.required' => 'Uang diterima wajib diisi',
            'amount_received.numeric' => 'Uang diterima harus berupa angka',
            'amount_received.min' => 'Uang diterima tidak boleh negatif',
            'payment_method_id.required' => 'Metode pembayaran wajib dipilih',
            'payment_method_id.exists' => 'Metode pembayaran tidak valid',
        ];
    }
}
