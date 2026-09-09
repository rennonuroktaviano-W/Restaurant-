@extends('layouts.guest')

@section('title', 'Lupa Password - '.config('app.name'))

@section('content')
    <div class="mb-6 text-center">
        <h1 class="font-display text-2xl font-semibold text-ink-900">Lupa Password</h1>
        <p class="mt-1 text-sm text-ink-500">Masukkan email Anda untuk menerima tautan reset password</p>
    </div>

    <div class="card p-6">
        <form method="POST" action="{{ route('password.forgot.store') }}" class="space-y-4">
            @csrf

            <div>
                <label for="email" class="label">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email" class="input @error('email') border-burgundy-500 @enderror">
                @error('email')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <button type="submit" class="btn btn-primary w-full">Kirim Tautan Reset</button>
            </div>
        </form>
    </div>

    <p class="mt-4 text-center">
        <a href="{{ route('login') }}" class="text-sm font-medium text-forest-700 hover:underline">&larr; Kembali ke Login</a>
    </p>
@endsection