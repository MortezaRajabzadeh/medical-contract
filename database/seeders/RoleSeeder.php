<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create roles
        $adminRole = Role::create(['name' => 'system_admin']);
        $medicalAdminRole = Role::create(['name' => 'medical_admin']);
        $medicalStaffRole = Role::create(['name' => 'medical_staff']);
        $viewerRole = Role::create(['name' => 'viewer']);

        // Create permissions
        $permissions = [
            'manage_medical_centers',
            'manage_users',
            'create_contracts',
            'edit_contracts',
            'approve_contracts',
            'view_contracts',
            'delete_contracts',
            'upload_files',
            'download_files',
            'view_reports'
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Assign all permissions to admin role
        $adminRole->givePermissionTo(Permission::all());
        
        // Assign permissions to medical admin role
        $medicalAdminRole->givePermissionTo([
            'manage_users', 'create_contracts', 'edit_contracts', 
            'approve_contracts', 'view_contracts', 'upload_files', 
            'download_files', 'view_reports'
        ]);
        
        // Assign permissions to medical staff role
        $medicalStaffRole->givePermissionTo([
            'create_contracts', 'edit_contracts', 'view_contracts', 
            'upload_files', 'download_files'
        ]);
        
        // Assign view-only permissions to viewer role
        $viewerRole->givePermissionTo(['view_contracts']);
        
        // Create default admin user if not exists
        $admin = \App\Models\User::firstOrCreate(
            ['email' => 'admin@medicalsystem.com'],
            [
                'name' => 'System Administrator',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
                'user_type' => 'admin',
                'is_active' => true
            ]
        );
        
        $admin->assignRole('system_admin');
    }
}
