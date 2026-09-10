<?php

namespace App\Http\Requests;

use App\Support\Permissions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AreaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(Permissions::LOCATION_MANAGE) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'string', 'max:140', 'alpha_dash', Rule::unique('areas')->ignore($this->route('area')?->id)->whereNull('deleted_at')],
            'type' => ['required', 'in:restaurant,pool,room,villa,other'],
            'description' => ['nullable', 'string', 'max:1000'],
            'address' => ['nullable', 'url', 'max:1000'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'open_time' => ['nullable', 'date_format:H:i'],
            'close_time' => ['nullable', 'date_format:H:i'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama area wajib diisi',
            'slug.required' => 'Slug wajib diisi',
            'slug.unique' => 'Slug sudah digunakan',
            'type.required' => 'Tipe area wajib dipilih',
            'type.in' => 'Tipe area tidak valid',
            'address.url' => 'Link Google Maps tidak valid. Tempel link berbagi dari Google Maps.',
            'latitude.between' => 'Latitude harus antara -90 dan 90',
            'longitude.between' => 'Longitude harus antara -180 dan 180',
            'open_time.date_format' => 'Jam buka harus format HH:MM (contoh: 11:00)',
            'close_time.date_format' => 'Jam tutup harus format HH:MM (contoh: 22:00)',
        ];
    }
}
