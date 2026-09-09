<?php

namespace App\Http\Requests;

use App\Support\Permissions;
use Illuminate\Foundation\Http\FormRequest;

class PaymentMethodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(Permissions::SETTING_MANAGE) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'code' => ['required', 'string', 'max:30', 'unique:payment_methods,code,'.$this->route('payment_method')?->id],
            'type' => ['required', 'in:cash,online'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'config' => ['nullable', 'json'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama metode bayar wajib diisi',
            'code.required' => 'Kode wajib diisi',
            'code.unique' => 'Kode sudah digunakan',
            'type.required' => 'Tipe metode bayar wajib dipilih',
            'type.in' => 'Tipe metode bayar tidak valid',
            'config.json' => 'Konfigurasi harus berupa JSON',
        ];
    }
}
