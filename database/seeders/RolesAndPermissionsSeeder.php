<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'view projects',
            'create projects',
            'update projects',
            'delete projects',
            'create task',
            'assign task',
            'update any task',
            'move any task',
            'delete task',
            'update own task',
            'move assigned task',
            'comment on task',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        $manager = Role::firstOrCreate(['name' => 'manager']);
        $manager->syncPermissions([
            'view projects', 'create projects', 'update projects', 'delete projects',
            'create task', 'assign task', 'update any task', 'move any task', 'delete task',
            'comment on task',
        ]);

        $staff = Role::firstOrCreate(['name' => 'staff']);
        $staff->syncPermissions([
            'view projects', 'create task', 'update own task', 'move assigned task', 'comment on task',
        ]);

        $managerUser = User::firstOrCreate(
            ['email' => 'manager@pinboard.test'],
            [
                'name' => 'Manager',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $managerUser->assignRole('manager');
    }
}
