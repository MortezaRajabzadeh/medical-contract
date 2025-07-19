<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * سیدر اصلی برنامه
     * اجرای سیدرهای مختلف برای ایجاد داده‌های اولیه
     */
    public function run(): void
    {
        // اجرای سیدر کامل نقش‌ها، مجوزها، کاربران و مراکز درمانی
        // این سیدر همه جداول مرتبط را پاک و از نو می‌سازد
        $this->call(UserRolePermissionSeeder::class);
        
        // ایجاد قرارداد نمونه برای تست
        $this->call(ContractSeeder::class);
        
        // سایر سیدرها در صورت نیاز می‌توانند اینجا اضافه شوند
        // توجه: UserRolePermissionSeeder به تنهایی تمام جداول مورد نیاز را پر می‌کند
        // $this->call(MedicalCenterSeeder::class);
    }
}
