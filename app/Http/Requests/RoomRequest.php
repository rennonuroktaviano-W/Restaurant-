<?php

namespace App\Http\Requests;

use App\Support\Permissions;
use Illuminate\Foundation\Http\FormRequest;

class RoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(Permissions::LOCATION_MANAGE) ?? false;
    }

    public function rules(): array
    {
        return [
            'area_id' => ['required', 'exists:areas,id'],
            'room_number' => ['required', 'string', 'max:30', 'unique:rooms,room_number,'.$this->route('room')?->id],
            'name' => ['nullable', 'string', 'max:120'],
            'status' => ['sometimes', 'in:available,occupied,reserved'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'area_id.required' => 'Area wajib dipilih',
            'area_id.exists' => 'Area tidak valid',
            'room_number.required' => 'Nomor room wajib diisi',
            'room_number.unique' => 'Nomor room sudah digunakan',
            'status.in' => 'Status room tidak valid',
        ];
    }
}
