<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\MedicalCenter;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserRolePermissionSeeder extends Seeder
{
    /**
     * سیدر کامل برای نقش‌ها، مجوزها و کاربران
     * این سیدر باعث ایجاد نقش‌ها، مجوزها و کاربران پایه می‌شود
     * همچنین ارتباط بین user_type و نقش‌ها را برقرار می‌کند
     */
    public function run(): void
    {
        // پاک کردن داده‌های قبلی
        $this->truncateTables();
        
        // ریست کردن کش نقش‌ها و مجوزها
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        
        // ایجاد نقش‌ها
        $this->createRoles();
        
        // ایجاد مجوزها و تخصیص به نقش‌ها
        $this->createPermissions();
        
        // ایجاد مرکز درمانی نمونه
        $medicalCenter = $this->createMedicalCenter();
        
        // ایجاد کاربران با نقش‌های مناسب
        $this->createUsers($medicalCenter);
    }
    
    /**
     * پاک کردن داده‌های قبلی در جدول‌ها
     */
    private function truncateTables(): void
    {
        // غیرفعال کردن موقت foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        // پاک کردن جدول‌های مرتبط
        DB::table('model_has_roles')->truncate();
        DB::table('model_has_permissions')->truncate();
        DB::table('role_has_permissions')->truncate();
        DB::table('permissions')->truncate();
        DB::table('roles')->truncate();
        DB::table('users')->truncate();
        DB::table('medical_centers')->truncate();
        DB::table('contracts')->truncate();
        
        // فعال کردن مجدد foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
    
    /**
     * ایجاد نقش‌ها
     */
    private function createRoles(): void
    {
        // ایجاد نقش‌های اصلی
        Role::create(['name' => 'system_admin']);     // مدیر سیستم
        Role::create(['name' => 'medical_admin']);    // مدیر مرکز درمانی
        Role::create(['name' => 'medical_staff']);    // کارکنان درمانی
        Role::create(['name' => 'viewer']);           // مشاهده‌کننده (کارکنان اداری)
        
        $this->command->info('نقش‌های سیستم ایجاد شدند.');
    }
    
    /**
     * ایجاد مجوزها و تخصیص به نقش‌ها
     */
    private function createPermissions(): void
    {
        // تعریف مجوزهای سیستم
        $permissions = [
            'manage_medical_centers',   // مدیریت مراکز درمانی
            'manage_users',             // مدیریت کاربران
            'create_contracts',         // ایجاد قرارداد
            'edit_contracts',           // ویرایش قرارداد
            'approve_contracts',        // تایید قرارداد
            'view_contracts',           // مشاهده قرارداد
            'delete_contracts',         // حذف قرارداد
            'upload_files',             // آپلود فایل
            'download_files',           // دانلود فایل
            'view_reports'              // مشاهده گزارشات
        ];
        
        // ایجاد مجوزها
        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }
        
        // دریافت نقش‌ها
        $adminRole = Role::findByName('system_admin');
        $medicalAdminRole = Role::findByName('medical_admin');
        $medicalStaffRole = Role::findByName('medical_staff');
        $viewerRole = Role::findByName('viewer');
        
        // تخصیص تمام مجوزها به مدیر سیستم
        $adminRole->givePermissionTo(Permission::all());
        
        // تخصیص مجوزهای مناسب به مدیر مرکز درمانی
        $medicalAdminRole->givePermissionTo([
            'manage_users', 'create_contracts', 'edit_contracts', 
            'approve_contracts', 'view_contracts', 'upload_files', 
            'download_files', 'view_reports'
        ]);
        
        // تخصیص مجوزهای محدود به کارکنان درمانی
        $medicalStaffRole->givePermissionTo([
            'view_contracts', 'upload_files', 'download_files'
        ]);
        
        // تخصیص مجوزهای محدود به مشاهده‌کنندگان (کارکنان اداری)
        $viewerRole->givePermissionTo([
            'view_contracts', 'view_reports', 'download_files'
        ]);
        
        $this->command->info('مجوزهای سیستم ایجاد و به نقش‌ها تخصیص داده شدند.');
    }
    
    /**
     * ایجاد مرکز درمانی نمونه
     */
    private function createMedicalCenter(): MedicalCenter
    {
        $medicalCenter = MedicalCenter::create([
            'name' => 'مرکز درمانی نمونه',
            'license_number' => 'MC-' . rand(10000, 99999),
            'address' => 'تهران، خیابان آزادی',
            'phone' => '021-12345678',
            'email' => 'info@medical-center.test',
            'director_name' => 'دکتر محمدی',
            'status' => 'active',
            'operating_hours' => json_encode([
                'شنبه' => '8:00 - 20:00',
                'یکشنبه' => '8:00 - 20:00',
                'دوشنبه' => '8:00 - 20:00',
                'سه‌شنبه' => '8:00 - 20:00',
                'چهارشنبه' => '8:00 - 20:00',
                'پنجشنبه' => '8:00 - 14:00',
                'جمعه' => 'تعطیل'
            ]),
            'latitude' => 35.6892,
            'longitude' => 51.3890,
        ]);
        
        $this->command->info('مرکز درمانی نمونه ایجاد شد.');
        
        return $medicalCenter;
    }
    
    /**
     * ایجاد کاربران با نقش‌های مناسب
     */
    private function createUsers(MedicalCenter $medicalCenter): void
    {
        // ایجاد مدیر سیستم
        $admin = User::create([
            'name' => 'مدیر سیستم',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'user_type' => 'admin',
        ]);
        $admin->assignRole('system_admin');
        
        // تخصیص مستقیم مجوزهای کلیدی به مدیر سیستم
        $admin->givePermissionTo([
            'manage_medical_centers', 'manage_users', 'create_contracts', 'edit_contracts',
            'approve_contracts', 'view_contracts', 'delete_contracts', 'upload_files',
            'download_files', 'view_reports'
        ]);
        
        // ایجاد مدیر مرکز درمانی
        $medicalAdmin = User::create([
            'name' => 'مدیر مرکز درمانی',
            'email' => 'medical@example.com',
            'password' => Hash::make('password'),
            'user_type' => 'medical_staff',
            'medical_center_id' => $medicalCenter->id,
        ]);
        $medicalAdmin->assignRole('medical_admin');
        
        // تخصیص مستقیم مجوزهای کلیدی به مدیر مرکز درمانی
        $medicalAdmin->givePermissionTo([
            'manage_users', 'create_contracts', 'edit_contracts', 'approve_contracts',
            'view_contracts', 'upload_files', 'download_files', 'view_reports'
        ]);
        
        // ایجاد کارمند مرکز درمانی
        $staff = User::create([
            'name' => 'کارمند مرکز درمانی',
            'email' => 'staff@example.com',
            'password' => Hash::make('password'),
            'user_type' => 'medical_staff',
            'medical_center_id' => $medicalCenter->id,
        ]);
        $staff->assignRole('medical_staff');
        
        // تخصیص مستقیم مجوزهای کلیدی به کارمند مرکز درمانی
        $staff->givePermissionTo([
            'view_contracts', 'upload_files', 'download_files'
        ]);
        
        // ایجاد کارمند اداری (مشاهده‌کننده)
        $viewer = User::create([
            'name' => 'کارمند اداری',
            'email' => 'viewer@example.com',
            'password' => Hash::make('password'),
            'user_type' => 'administrative_staff',
            'medical_center_id' => $medicalCenter->id,
        ]);
        $viewer->assignRole('viewer');
        
        // تخصیص مستقیم مجوزهای کلیدی به کارمند اداری
        $viewer->givePermissionTo([
            'view_contracts', 'view_reports', 'download_files'
        ]);
        
        $this->command->info('کاربران نمونه با نقش‌ها و مجوزهای مستقیم ایجاد شدند.');
    }
}
