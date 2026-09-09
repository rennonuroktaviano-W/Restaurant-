@extends('layouts.guest')

@section('title', 'Login - '.config('app.name'))

@section('content')
    <div class="flex min-h-screen items-center justify-center bg-gray-100 px-4">
        <div class="w-full max-w-sm">
            <div class="mb-6 text-center">
                <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-xl bg-brand-600 text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <h1 class="text-xl font-semibold text-gray-900">{{ config('app.name') }}</h1>
                <p class="mt-1 text-sm text-gray-500">Masuk untuk akses kasir, dapur, dan manajemen</p>
            </div>

            @include('partials.flash')

            <div class="card p-6">
                <form method="POST" action="{{ route('login.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label for="email" class="label">Email</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="input @error('email') border-red-400 @enderror">
                    </div>

                    <div>
                        <label for="password" class="label">Password</label>
                        <input id="password" type="password" name="password" required autocomplete="current-password" class="input @error('password') border-red-400 @enderror">
                    </div>

                    <label class="flex items-center gap-2 text-sm text-gray-600">
                        <input type="checkbox" name="remember" value="1" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                        Ingat saya
                    </label>

                    <div>
                        <button type="submit" class="btn btn-primary w-full">Masuk</button>
                    </div>
                </form>
            </div>

            <p class="mt-4 text-center text-xs text-gray-400">
                Default: admin@pos.local / kitchen@pos.local / cashier@pos.local — lihat seeder.
            </p>
        </div>
    </div>
@endsection