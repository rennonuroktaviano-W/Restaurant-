@extends('layouts.app')

@section('title', 'Pengaturan - '.config('app.name'))
@section('header', 'Pengaturan')

@section('content')
    @php
        $groupLabels = [
            'general' => 'Umum',
            'receipt' => 'Struk',
            'pricing' => 'Harga & Pajak',
            'features' => 'Fitur',
        ];
        $boolKeys = ['pricing.pay_later', 'feature.kds_enabled', 'feature.soldout_label_product'];
        $numberKeys = ['pricing.tax_rate', 'pricing.service_charge_rate', 'order.timeout_minutes'];
    @endphp

    <h1 class="mb-5 text-2xl font-bold text-gray-900">Pengaturan</h1>

    <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6">
        @csrf
        @method('PUT')

        @foreach ($groups as $groupName => $settings)
            <div class="card overflow-hidden">
                <div class="border-b border-gray-200 px-5 py-4">
                    <h2 class="text-base font-semibold text-gray-900">{{ $groupLabels[$groupName] ?? ucfirst($groupName) }}</h2>
                </div>
                <div class="grid gap-4 p-5 sm:grid-cols-2">
                    @foreach ($settings as $setting)
                        @if (in_array($setting->key, $boolKeys, true))
                            <label class="flex items-center gap-2 text-sm text-gray-700">
                                <input type="hidden" name="settings[{{ $setting->key }}]" value="false">
                                <input type="checkbox" name="settings[{{ $setting->key }}]" value="true" @checked($setting->value === 'true') class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                                {{ $setting->label }}
                            </label>
                        @else
                            <div>
                                <label for="s-{{ $setting->key }}" class="label">{{ $setting->label }}</label>
                                <input id="s-{{ $setting->key }}"
                                       type="{{ in_array($setting->key, $numberKeys, true) ? 'number' : 'text' }}"
                                       step="{{ in_array($setting->key, $numberKeys, true) ? '0.01' : null }}"
                                       name="settings[{{ $setting->key }}]"
                                       value="{{ $setting->value }}"
                                       class="input">
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endforeach

        <div class="flex justify-end">
            <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
        </div>
    </form>
@endsection