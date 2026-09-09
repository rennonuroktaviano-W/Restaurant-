@extends('layouts.app')

@section('title', 'Edit Pengguna - ' . config('app.name'))
@section('header', 'Edit Pengguna')

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Edit Pengguna</h1>
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Kembali</a>
    </div>

    <form method="POST" action="{{ route('admin.users.update', $user) }}" class="card p-6">
        @csrf
        @method('PUT')

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="name" class="label">Nama</label>
                <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" class="input @error('name') border-red-400 @enderror" required>
                @error('name')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="label">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" class="input @error('email') border-red-400 @enderror" required>
                @error('email')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="phone" class="label">No. HP</label>
                <input type="text" name="phone" id="phone" value="{{ old('phone', $user->phone) }}" class="input @error('phone') border-red-400 @enderror">
                @error('phone')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="label">Password</label>
                <input type="password" name="password" id="password" class="input @error('password') border-red-400 @enderror">
                <p class="mt-1 text-xs text-gray-500">Kosongkan jika tidak ingin mengubah</p>
                @error('password')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="label">Konfirmasi Password</label>
                <input type="password" name="password_confirmation" id="password_confirmation" class="input">
            </div>

            <div class="sm:col-span-2">
                <label class="label">Role</label>
                <div class="flex flex-wrap gap-4">
                    @foreach ($roles as $role)
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="roles[]" value="{{ $role->name }}" @checked($user->hasRole($role->name)) class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                            <span class="text-sm text-gray-700">{{ $role->name }}</span>
                        </label>
                    @endforeach
                </div>
                @error('roles')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="sm:col-span-2">
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $user->is_active)) class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                    <span class="text-sm text-gray-700">Aktif</span>
                </label>
            </div>
        </div>

        <div class="mt-6 flex items-center gap-2">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Batal</a>
        </div>
    </form>

    <form method="POST" action="{{ route('admin.users.reset-password', $user) }}" class="card mt-6 p-6" onsubmit="return confirm('Reset password pengguna ini? Tindakan dicatat pada audit log.');">
        @csrf

        <h2 class="text-lg font-bold text-gray-900">Reset Password (Paksa)</h2>
        <p class="mt-1 text-sm text-gray-500">Mengganti password tanpa memerlukan password lama. Aksi ini dicatat pada audit log.</p>

        <div class="mt-4 grid gap-4 sm:grid-cols-2">
            <div>
                <label for="reset_password" class="label">Password Baru</label>
                <input type="password" name="password" id="reset_password" class="input @error('password') border-red-400 @enderror" required minlength="8">
                @error('password')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="reset_password_confirmation" class="label">Konfirmasi Password Baru</label>
                <input type="password" name="password_confirmation" id="reset_password_confirmation" class="input" required>
            </div>
        </div>

        <div class="mt-5">
            <button type="submit" class="btn btn-danger">Reset Password</button>
        </div>
    </form>
@endsection
