@extends('layouts.app')

@section('title', __('admin.products.title').' - '.config('app.name'))
@section('header', __('admin.products.title'))

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-ink-900">{{ __('admin.products.title') }}</h1>
        <a href="{{ route('admin.products.create') }}" class="btn btn-primary">{{ __('admin.add_new', ['item' => __('admin.products.title')]) }}</a>
    </div>

    <form method="GET" action="{{ route('admin.products.index') }}" class="mb-5">
        <div class="flex flex-wrap items-center gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('admin.products.search_placeholder') }}" class="input max-w-xs">
            <select name="category" class="select max-w-xs">
                <option value="">{{ __('admin.all_categories') }}</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(request('category') == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-secondary">{{ __('admin.search') }}</button>
            @if (request('search') || request('category'))
                <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">{{ __('admin.reset') }}</a>
            @endif
        </div>
    </form>

    <div class="card overflow-hidden">
        <table class="table-admin">
            <thead>
                <tr>
                    <th class="px-4 py-3 font-medium">{{ __('admin.image') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('admin.name') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('admin.sku') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('admin.category') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('admin.sale_price') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('admin.stock') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('admin.status') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('admin.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-ink-900/10">
                @forelse ($products as $product)
                    <tr>
                        <td class="px-4 py-3">
                            @if ($product->image)
                                <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="h-12 w-12 rounded-lg object-cover">
                            @else
                                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-ink-100 text-xs text-ink-400">{{ __('admin.na') }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3">{{ $product->name }}</td>
                        <td class="px-4 py-3">{{ $product->sku }}</td>
                        <td class="px-4 py-3">{{ $product->category->name }}</td>
                        <td class="px-4 py-3">{{ number_format($product->sale_price, 0, ',', '.') }}</td>
                        <td class="px-4 py-3">{{ $product->stock_type === 'limited' ? $product->stock : '∞' }}</td>
                        <td class="px-4 py-3">
                            @if ($product->is_active)
                                <span class="badge badge-forest">{{ __('admin.active') }}</span>
                            @else
                                <span class="badge badge-ink">{{ __('admin.inactive') }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-secondary btn-sm">{{ __('admin.edit') }}</a>
                                <form method="POST" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('{{ __('admin.confirm_delete') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">{{ __('admin.delete') }}</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-sm text-ink-500">{{ __('admin.no_data') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $products->links('partials.pagination') }}
    </div>
@endsection