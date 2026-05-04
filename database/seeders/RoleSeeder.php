<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'create-project',
            'edit-project',
            'delete-project',
            'manage-members',
            'create-sprint',
            'edit-sprint',
            'create-task',
            'edit-task',
            'delete-task',
            'assign-task',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions
        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo($permissions);

        $member = Role::create(['name' => 'member']);
        $member->givePermissionTo([
            'create-task',
            'edit-task',
            'assign-task',
        ]);

        Role::create(['name' => 'viewer']);
        // viewer gets no permissions — read only
    }
}
