@extends('layouts.guest')

@section('title', 'Reset Password - '.config('app.name'))

@section('content')
    <div class="mb-6 text-center">
        <h1 class="font-display text-2xl font-semibold text-ink-900">Reset Password</h1>
        <p class="mt-1 text-sm text-ink-500">Buat password baru untuk akun Anda</p>
    </div>

    <div class="card p-6">
        <form method="POST" action="{{ route('password.reset.store') }}" class="space-y-4">
            @csrf

            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="email" value="{{ $email }}">

            <div>
                <label class="label">Email</label>
                <p class="text-sm font-medium text-ink-700">{{ $email }}</p>
            </div>

            <div>
                <label for="password" class="label">Password Baru</label>
                <input id="password" type="password" name="password" required minlength="8" autocomplete="new-password" class="input @error('password') border-burgundy-500 @enderror">
                @error('password')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="label">Konfirmasi Password</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required minlength="8" autocomplete="new-password" class="input">
            </div>

            <div>
                <button type="submit" class="btn btn-primary w-full">Simpan Password Baru</button>
            </div>
        </form>
    </div>
@endsection