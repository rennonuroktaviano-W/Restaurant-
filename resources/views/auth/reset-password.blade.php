@extends('layouts.guest')

@section('title', 'Reset Password - '.config('app.name'))

@section('content')
    <div class="flex min-h-screen items-center justify-center bg-gray-100 px-4">
        <div class="w-full max-w-md">
            <div class="mb-6 text-center">
                <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-xl bg-brand-600 text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                </div>
                <h1 class="text-xl font-semibold text-gray-900">Reset Password</h1>
                <p class="mt-1 text-sm text-gray-500">Buat password baru untuk akun Anda</p>
            </div>

            @include('partials.flash')

            <div class="card p-6">
                <form method="POST" action="{{ route('password.reset.store') }}" class="space-y-4">
                    @csrf

                    <input type="hidden" name="token" value="{{ $token }}">
                    <input type="hidden" name="email" value="{{ $email }}">

                    <div>
                        <label class="label">Email</label>
                        <p class="text-sm font-medium text-gray-700">{{ $email }}</p>
                    </div>

                    <div>
                        <label for="password" class="label">Password Baru</label>
                        <input id="password" type="password" name="password" required minlength="8" autocomplete="new-password" class="input @error('password') border-red-400 @enderror">
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
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
        </div>
    </div>
@endsection