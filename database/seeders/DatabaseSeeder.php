<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Seed products
        $this->call(ProductSeeder::class);

        // Ensure an admin account exists
        $this->call(AdminSeeder::class);

        // Promote ADMIN_EMAIL to admin if column exists
        $adminEmail = env('ADMIN_EMAIL');
        if ($adminEmail) {
            $hasIsAdmin = false;
            try {
                $hasIsAdmin = DB::getSchemaBuilder()->hasColumn('users', 'is_admin');
            } catch (\Throwable $e) {
                $hasIsAdmin = false;
            }

            if ($hasIsAdmin) {
                User::where('email', $adminEmail)->update(['is_admin' => true]);
            }
        }
    }
}
