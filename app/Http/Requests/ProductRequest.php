<?php

namespace App\Http\Requests;

use App\Support\Permissions;
use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(Permissions::CATALOG_MANAGE) ?? false;
    }

    public function rules(): array
    {
        $productId = $this->route('product')?->id;

        return [
            'category_id' => ['required', 'exists:categories,id'],
            'sku' => ['required', 'string', 'max:50', 'unique:products,sku,'.$productId],
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['required', 'string', 'max:160', 'alpha_dash', 'unique:products,slug,'.$productId],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,webp,gif', 'max:2048'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['required', 'numeric', 'min:0'],
            'stock_type' => ['required', 'in:limited,unlimited'],
            'stock' => ['required_if:stock_type,limited', 'nullable', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
            'is_available' => ['sometimes', 'boolean'],
            'is_featured' => ['sometimes', 'boolean'],
            'is_kitchen' => ['sometimes', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'Kategori wajib dipilih',
            'category_id.exists' => 'Kategori tidak valid',
            'sku.required' => 'SKU wajib diisi',
            'sku.unique' => 'SKU sudah digunakan',
            'name.required' => 'Nama produk wajib diisi',
            'slug.required' => 'Slug wajib diisi',
            'slug.unique' => 'Slug sudah digunakan',
            'sale_price.required' => 'Harga jual wajib diisi',
            'sale_price.min' => 'Harga jual tidak boleh negatif',
            'stock_type.required' => 'Tipe stok wajib dipilih',
            'stock.required_if' => 'Stok wajib diisi untuk tipe terbatas',
            'stock.min' => 'Stok tidak boleh negatif',
            'image.image' => 'File harus berupa gambar',
            'image.mimes' => 'Format gambar harus jpeg, png, webp, atau gif',
            'image.max' => 'Ukuran gambar maksimal 2MB',
        ];
    }
}
