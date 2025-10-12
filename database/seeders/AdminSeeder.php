<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('ADMIN_EMAIL', 'admin@example.com');
        $password = env('ADMIN_PASSWORD', 'password');
        $name = env('ADMIN_NAME', 'Admin');

        $user = User::withTrashed()->firstWhere('email', $email);
        if (! $user) {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
            ]);
        } else {
            if ($user->trashed()) {
                $user->restore();
            }
        }

        if (! $user->is_admin) {
            $user->is_admin = true;
            $user->save();
        }
    }
}
