<?php

namespace App\Http\Requests;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_type' => ['required', 'in:'.implode(',', [Order::TYPE_DINE_IN, Order::TYPE_TAKE_AWAY, Order::TYPE_ROOM_SERVICE])],
            'table_id' => ['required_if:order_type,'.Order::TYPE_DINE_IN, 'nullable', 'exists:dining_tables,id'],
            'room_id' => ['required_if:order_type,'.Order::TYPE_ROOM_SERVICE, 'nullable', 'exists:rooms,id'],
            'table_token' => ['nullable', 'string', 'max:100'],
            'customer_name' => ['nullable', 'string', 'max:100'],
            'customer_phone' => ['nullable', 'string', 'max:30'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'discount_code' => ['nullable', 'string', 'max:50'],
            'idempotency_key' => ['nullable', 'string', 'max:191'],
            'payment_method_id' => ['nullable', 'integer', 'exists:payment_methods,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'order_type.required' => 'Tipe order wajib dipilih',
            'order_type.in' => 'Tipe order tidak valid',
            'table_id.required_if' => 'Meja wajib dipilih untuk dine-in',
            'room_id.required_if' => 'Room wajib dipilih untuk room service',
        ];
    }
}
