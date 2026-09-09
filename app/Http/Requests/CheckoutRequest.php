<?php

namespace App\Http\Requests;

use App\Models\Order;
use App\Services\LocationTokenService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $token = $this->input('table_token') ?: session('location.token');

        if (! $token) {
            return;
        }

        $location = app(LocationTokenService::class)->verify($token);

        if ($location === null) {
            return;
        }

        if ($location['type'] === LocationTokenService::TYPE_TABLE && blank($this->input('table_id'))) {
            $this->merge(['table_id' => $location['id']]);
        }

        if ($location['type'] === LocationTokenService::TYPE_ROOM && blank($this->input('room_id'))) {
            $this->merge(['room_id' => $location['id']]);
        }
    }

    public function rules(): array
    {
        return [
            'order_type' => ['required', 'in:'.implode(',', [Order::TYPE_DINE_IN, Order::TYPE_TAKE_AWAY, Order::TYPE_ROOM_SERVICE])],
            'table_id' => ['required_if:order_type,'.Order::TYPE_DINE_IN, 'nullable', Rule::exists('dining_tables', 'id')->where('is_active', true)->whereNull('deleted_at')],
            'room_id' => ['required_if:order_type,'.Order::TYPE_ROOM_SERVICE, 'nullable', Rule::exists('rooms', 'id')->where('is_active', true)->whereNull('deleted_at')],
            'table_token' => ['nullable', 'string', 'max:512'],
            'customer_name' => ['nullable', 'string', 'max:100', 'regex:/^[\p{L}\p{M}\s.,\'-]+$/u'],
            'customer_phone' => ['nullable', 'regex:/^[0-9]{8,15}$/'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'discount_code' => ['nullable', 'string', 'max:50'],
            'idempotency_key' => ['nullable', 'string', 'max:191'],
            'payment_method_id' => ['nullable', 'integer', Rule::exists('payment_methods', 'id')->where('is_active', true)->whereNull('deleted_at')],
        ];
    }

    public function messages(): array
    {
        return [
            'order_type.required' => 'Tipe order wajib dipilih',
            'order_type.in' => 'Tipe order tidak valid',
            'table_id.required_if' => 'Meja wajib dipilih untuk dine-in',
            'room_id.required_if' => 'Room wajib dipilih untuk room service',
            'customer_name.regex' => 'Nama hanya boleh berupa huruf',
            'customer_phone.regex' => 'No. HP harus berupa angka (8–15 digit)',
        ];
    }
}
