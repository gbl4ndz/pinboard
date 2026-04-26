<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Single role — all business rules live in policies
        $userRole = Role::firstOrCreate(['name' => 'user']);

        // Assign 'user' role to every existing user who doesn't have it yet
        User::all()->each(function (User $user) use ($userRole) {
            if (! $user->hasRole('user')) {
                $user->assignRole($userRole);
            }
        });

        // Keep a default seed account (useful for local dev)
        $seed = User::firstOrCreate(
            ['email' => 'user@pinboard.test'],
            [
                'name'               => 'Demo User',
                'password'           => Hash::make('password'),
                'email_verified_at'  => now(),
            ]
        );

        if (! $seed->hasRole('user')) {
            $seed->assignRole($userRole);
        }
    }
}
