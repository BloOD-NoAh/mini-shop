<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    public function index()
    {
        $admins = User::query()
            ->where('is_admin', true)
            ->latest('id')
            ->paginate(20);

        if (request()->wantsJson()) {
            return response()->json($admins);
        }

        return view('admin.admins.index', [
            'admins' => $admins,
        ]);
    }

    public function create()
    {
        return view('admin.admins.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'string', 'email', 'max:255',
                Rule::unique('users', 'email')->whereNull('deleted_at'),
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $user->is_admin = true;
        $user->save();

        return redirect()->route('admins.index')->with('status', 'Admin created');
    }

    public function edit(User $admin)
    {
        abort_unless($admin->is_admin, 404);
        return view('admin.admins.edit', [
            'admin' => $admin,
        ]);
    }

    public function update(Request $request, User $admin): RedirectResponse
    {
        abort_unless($admin->is_admin, 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'string', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($admin->id)->whereNull('deleted_at'),
            ],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $payload = [
            'name' => $data['name'],
            'email' => $data['email'],
        ];
        if (!empty($data['password'])) {
            $payload['password'] = Hash::make($data['password']);
        }

        $admin->update($payload);
        if (!$admin->is_admin) {
            $admin->is_admin = true; // ensure remains admin
            $admin->save();
        }

        return redirect()->route('admins.index')->with('status', 'Admin updated');
    }

    public function destroy(User $admin): RedirectResponse
    {
        abort_unless($admin->is_admin, 404);

        // Prevent deleting yourself to avoid lockout (optional safeguard)
        if (auth()->id() === $admin->id) {
            return back()->withErrors(['error' => 'You cannot delete your own admin account.']);
        }

        $admin->delete();
        return redirect()->route('admins.index')->with('status', 'Admin deleted');
    }
}
