@extends('layouts.app')

@section('title', 'QR Room - '.config('app.name'))
@section('header', 'QR Room')

@section('content')
    <div class="mb-5">
        <a href="{{ route('admin.areas.index') }}" class="text-sm font-medium text-brand-600 hover:text-brand-700">&larr; Kembali</a>
    </div>

    <div class="card mx-auto max-w-md p-6 text-center">
        <h1 class="text-xl font-bold text-gray-900">{{ $room->name }}</h1>
        <p class="mt-1 text-sm text-gray-500">{{ $room->area?->name }}</p>

        <div class="mx-auto mt-5 max-w-xs">
            <img src="{{ $qrDataUri }}" alt="QR code untuk room {{ $room->name }}" class="w-full">
        </div>

        <p class="mt-4 break-all text-xs text-gray-400">{{ $url }}</p>

        <div class="mt-6 flex justify-center gap-2">
            <button type="button" onclick="window.print()" class="btn btn-primary">Cetak QR</button>
            <a href="{{ route('admin.areas.index') }}" class="btn btn-secondary">Tutup</a>
        </div>
    </div>
@endsection