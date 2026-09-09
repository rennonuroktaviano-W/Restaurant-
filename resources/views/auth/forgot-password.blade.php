@extends('layouts.guest')

@section('title', 'Lupa Password - '.config('app.name'))

@section('content')
    <div class="flex min-h-screen items-center justify-center bg-gray-100 px-4">
        <div class="w-full max-w-sm">
            <div class="mb-6 text-center">
                <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-xl bg-brand-600 text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <h1 class="text-xl font-semibold text-gray-900">Lupa Password</h1>
                <p class="mt-1 text-sm text-gray-500">Masukkan email Anda untuk menerima tautan reset password</p>
            </div>

            @include('partials.flash')

            @if (session('status'))
                <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('status') }}
                </div>
            @endif

            <div class="card p-6">
                <form method="POST" action="{{ route('password.forgot.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label for="email" class="label">Email</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email" class="input @error('email') border-red-400 @enderror">
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <button type="submit" class="btn btn-primary w-full">Kirim Tautan Reset</button>
                    </div>
                </form>
            </div>

            <p class="mt-4 text-center">
                <a href="{{ route('login') }}" class="text-sm font-medium text-brand-600 hover:text-brand-700">&larr; Kembali ke Login</a>
            </p>
        </div>
    </div>
@endsection