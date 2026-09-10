<?php

namespace App\Http\Requests;

use App\Support\Permissions;
use Illuminate\Foundation\Http\FormRequest;

class DiningTableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(Permissions::LOCATION_MANAGE) ?? false;
    }

    public function rules(): array
    {
        return [
            'area_id' => ['required', 'exists:areas,id'],
            'table_number' => ['required', 'string', 'max:30', 'unique:dining_tables,table_number,'.$this->route('table')?->id],
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
            'table_number.required' => 'Nomor meja wajib diisi',
            'table_number.unique' => 'Nomor meja sudah digunakan',
            'status.in' => 'Status meja tidak valid',
        ];
    }
}
