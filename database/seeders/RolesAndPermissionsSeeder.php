<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\Models\Role::class]->flushCache();
        app()[Permission::class]->flushCache();

        // Create roles
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $lawyer = Role::firstOrCreate(['name' => 'lawyer']);
        $client = Role::firstOrCreate(['name' => 'client']);
        $accountant = Role::firstOrCreate(['name' => 'accountant']);
        $secretary = Role::firstOrCreate(['name' => 'secretary']);
        $assistant = Role::firstOrCreate(['name' => 'legal_assistant']);

        // Create permissions
        $permissions = [
            'view_cases', 'create_cases', 'edit_cases', 'delete_cases',
            'view_clients', 'create_clients', 'edit_clients', 'delete_clients',
            'view_invoices', 'create_invoices', 'edit_invoices', 'delete_invoices',
            'view_users', 'create_users', 'edit_users', 'delete_users',
            'manage_roles', 'manage_permissions',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Assign permissions to roles
        $admin->givePermissionTo(Permission::all());
    }
}
