<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct(protected AuditLogger $audit)
    {
        Gate::authorize('user.manage');
    }

    public function index(Request $request): View
    {
        $users = User::query()
            ->with('roles')
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%"))
            ->when($request->role, fn ($q, $r) => $q->role($r))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $roles = Role::all();

        return view('admin.users.index', compact('users', 'roles'));
    }

    public function create(): View
    {
        $roles = Role::all();

        return view('admin.users.create', compact('roles'));
    }

    public function store(UserRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $roles = $data['roles'];
        unset($data['roles']);

        $user = User::create($data);

        $user->syncRoles($roles);

        $this->audit->log('create', 'users', 'user', $user->id, [], $user->toArray());

        return redirect()->route('admin.users.index')->with('success', 'Pengguna berhasil dibuat.');
    }

    public function edit(User $user): View
    {
        $roles = Role::all();

        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $old = ['roles' => $user->getRoleNames()->toArray()];
        $data = $request->validated();
        $roles = $data['roles'];
        unset($data['roles'], $data['password_confirmation']);

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $user->update($data);
        $user->syncRoles($roles);

        if ($user->getRoleNames()->toArray() !== $old['roles']) {
            $this->audit->log('update_role', 'users', 'user', $user->id, $old, ['roles' => $user->getRoleNames()->toArray()]);
        }

        $this->audit->log('update', 'users', 'user', $user->id, $old, $user->fresh()->toArray());

        return redirect()->route('admin.users.index')->with('success', 'Pengguna berhasil diperbarui.');
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        Gate::authorize('user.manage');

        $data = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user->update(['password' => $data['password']]);

        $this->audit->log('reset_password', 'users', 'user', $user->id, [], ['password' => '[REDACTED]']);

        return redirect()->route('admin.users.index')->with('success', "Password {$user->name} berhasil direset.");
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak dapat menghapus akun sendiri.');
        }

        $user->update(['is_active' => false]);

        $this->audit->log('deactivate', 'users', 'user', $user->id, [], []);

        return redirect()->route('admin.users.index')->with('success', 'Pengguna dinonaktifkan.');
    }
}
