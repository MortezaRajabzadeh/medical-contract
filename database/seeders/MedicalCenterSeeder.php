<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\MedicalCenter;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MedicalCenterSeeder extends Seeder
{
    /**
     * ایجاد مرکز درمانی تستی و اتصال کاربر به آن
     */
    public function run(): void
    {
        try {
            // ایجاد مرکز درمانی تستی
            $medicalCenter = MedicalCenter::create([
                'name' => 'مرکز درمانی تست',
                'license_number' => 'LIC-12345',
                'address' => 'تهران، خیابان آزادی',
                'phone' => '021-12345678',
                'email' => 'test@medical.com',
                'director_name' => 'دکتر محمدی',
                'status' => 'active',
                'operating_hours' => json_encode([
                    'شنبه' => '8:00-16:00',
                    'یکشنبه' => '8:00-16:00',
                    'دوشنبه' => '8:00-16:00',
                    'سه‌شنبه' => '8:00-16:00',
                    'چهارشنبه' => '8:00-16:00',
                    'پنج‌شنبه' => '8:00-14:00',
                    'جمعه' => 'تعطیل'
                ])
            ]);
            
            Log::info('مرکز درمانی تستی با موفقیت ایجاد شد', ['medical_center_id' => $medicalCenter->id]);
            
            // اتصال کاربر تستی به مرکز درمانی
            $user = User::where('email', 'test@example.com')->first();
            if ($user) {
                $user->medical_center_id = $medicalCenter->id;
                $user->save();
                
                Log::info('کاربر تستی با موفقیت به مرکز درمانی متصل شد', [
                    'user_id' => $user->id,
                    'medical_center_id' => $medicalCenter->id
                ]);
            } else {
                Log::warning('کاربر تستی یافت نشد');
            }
        } catch (\Exception $e) {
            Log::error('خطا در ایجاد مرکز درمانی تستی: ' . $e->getMessage());
        }
    }
}
