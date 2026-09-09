@extends('layouts.app')

@section('title', 'Kitchen Display Nonaktif')

@section('content')
    <div class="flex min-h-[60vh] flex-col items-center justify-center text-center">
        <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-gray-100">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
            </svg>
        </div>
        <h1 class="text-xl font-bold text-gray-900">Kitchen Display dinonaktifkan</h1>
        <p class="mt-2 max-w-sm text-sm text-gray-500">
            Modul Kitchen Display System saat ini dimatikan melalui pengaturan fitur. Hubungi administrator untuk mengaktifkannya kembali.
        </p>
        <a href="{{ route('dashboard') }}" class="btn btn-secondary mt-6">Kembali ke Dashboard</a>
    </div>
@endsection