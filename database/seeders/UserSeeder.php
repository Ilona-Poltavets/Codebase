<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $email = env('ADMIN_EMAIL', 'admin@example.com');
        $password = env('ADMIN_PASSWORD', 'admin123');
        $name = env('ADMIN_NAME', 'Admin');
        $fullName = env('ADMIN_FULL_NAME', 'Admin User');

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'full_name' => $fullName,
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ]
        );

        $adminRoleId = Role::where('name', 'admin')->value('id');
        if ($adminRoleId) {
            $user->roles()->syncWithoutDetaching([$adminRoleId]);
        }
    }
}
