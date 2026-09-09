@extends('layouts.guest')

@section('title', 'Login - '.config('app.name'))

@section('content')
    <div class="mb-6 text-center">
        <h1 class="font-display text-2xl font-semibold text-ink-900">Masuk</h1>
        <p class="mt-1 text-sm text-ink-500">Akses kasir, dapur, dan manajemen</p>
    </div>

    <div class="card p-6">
        <form method="POST" action="{{ route('login.store') }}" class="space-y-4">
            @csrf

            <div>
                <label for="email" class="label">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="input @error('email') border-burgundy-500 @enderror">
                @error('email')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="label">Password</label>
                <input id="password" type="password" name="password" required autocomplete="current-password" class="input @error('password') border-burgundy-500 @enderror">
                @error('password')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <label class="flex items-center gap-2 text-sm text-ink-600">
                <input type="checkbox" name="remember" value="1" class="h-4 w-4 rounded border-ink-900/20 text-forest-700 focus:ring-forest-600">
                Ingat saya
            </label>

            <div class="flex items-center justify-between gap-3">
                <span class="text-xs text-ink-400">Belum punya akun? Hubungi admin.</span>
                <a href="{{ route('password.forgot') }}" class="text-sm font-medium text-forest-700 hover:underline">Lupa password?</a>
            </div>

            <div>
                <button type="submit" class="btn btn-primary w-full">Masuk</button>
            </div>
        </form>
    </div>

    <p class="mt-4 text-center text-xs text-ink-400">
        Default: admin@pos.local / kitchen@pos.local / cashier@pos.local — lihat seeder.
    </p>
@endsection