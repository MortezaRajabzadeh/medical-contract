<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Filament\Facades\Filament;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\Facades\Log;

class FilamentServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        try {
            // CSS سفارشی را به طور مستقیم اعمال می‌کنیم بدون نیاز به فایل جداگانه

            // اضافه کردن استایل‌های RTL از طریق render hook
            Filament::registerRenderHook(
                'panels::head.end',
                fn (): string => '<script>document.dir = "rtl";</script>
                <style>
                    /* استایل‌های RTL برای Filament */
                    body[dir="rtl"] .fi-sidebar {
                        direction: rtl !important;
                    }
                    body[dir="rtl"] .fi-ta-content {
                        direction: rtl !important;
                    }
                    body[dir="rtl"] .fi-ta-header {
                        direction: rtl !important;
                    }
                    body[dir="rtl"] .fi-ta-table th {
                        text-align: right !important;
                    }
                    body[dir="rtl"] .fi-ta-table td {
                        text-align: right !important;
                    }
                    body[dir="rtl"] .fi-btn {
                        direction: rtl !important;
                    }
                    body[dir="rtl"] .fi-dropdown-panel {
                        direction: rtl !important;
                        text-align: right !important;
                    }
                    /* سایر المان‌های Filament */
                    body[dir="rtl"] * {
                        font-family: Vazirmatn, sans-serif !important;
                    }
                    
                    /* راست‌چین کردن کل پنل */
                    [dir="rtl"] .fi-layout,
                    [dir="rtl"] .fi-main,
                    [dir="rtl"] .fi-sidebar,
                    [dir="rtl"] .fi-topbar {
                        direction: rtl !important;
                    }
                    
                    /* انتقال سایدبار به سمت راست */
                    [dir="rtl"] .fi-sidebar {
                        right: 0 !important;
                        left: auto !important;
                    }
                    
                    /* تنظیم فضای اصلی */
                    [dir="rtl"] .fi-main {
                        margin-right: 20rem !important;
                        margin-left: 0 !important;
                    }
                    
                    /* تنظیم آیتم‌های منو */
                    [dir="rtl"] .fi-sidebar-nav-item {
                        text-align: right !important;
                    }
                    
                    [dir="rtl"] .fi-sidebar-nav-item-icon {
                        margin-left: 0.5rem !important;
                        margin-right: 0 !important;
                    }
                    
                    /* راست‌چین کردن جداول */
                    [dir="rtl"] .fi-ta-table,
                    [dir="rtl"] .fi-ta-header-cell,
                    [dir="rtl"] .fi-ta-cell {
                        text-align: right !important;
                    }
                    
                    /* راست‌چین کردن فرم‌ها */
                    [dir="rtl"] .fi-fo-field-wrp,
                    [dir="rtl"] .fi-fo-field-wrp-label {
                        text-align: right !important;
                    }
                    
                    /* راست‌چین کردن دکمه‌ها */
                    [dir="rtl"] .fi-btn-group {
                        flex-direction: row-reverse !important;
                    }
                    
                    /* تنظیم dropdown menu */
                    [dir="rtl"] .fi-dropdown-panel {
                        right: 0 !important;
                        left: auto !important;
                    }
                    
                    /* راست‌چین کردن breadcrumb */
                    [dir="rtl"] .fi-breadcrumbs {
                        flex-direction: row-reverse !important;
                    }
                    
                    /* تنظیم نوتیفیکیشن‌ها */
                    [dir="rtl"] .fi-no-notification {
                        right: 1rem !important;
                        left: auto !important;
                    }
                    
                    /* حالت موبایل */
                    @media (max-width: 1024px) {
                        [dir="rtl"] .fi-main {
                            margin-right: 0 !important;
                        }
                    }
                    
                    /* تنظیم pagination */
                    [dir="rtl"] .fi-ta-pagination {
                        direction: rtl !important;
                    }
                    
                    /* تنظیم search box */
                    [dir="rtl"] .fi-global-search-field {
                        text-align: right !important;
                    }
                    
                    /* تنظیم modal */
                    [dir="rtl"] .fi-modal-content {
                        direction: rtl !important;
                    }
                </style>'
            );
            
            // اضافه کردن attribute dir="rtl" به body
            Filament::registerRenderHook(
                'body.start',
                fn (): string => '
                    <script>
                        document.addEventListener("DOMContentLoaded", function() {
                            document.documentElement.setAttribute("dir", "rtl");
                            document.body.setAttribute("dir", "rtl");
                        });
                    </script>
                '
            );
            
            // لاگ کردن اطلاعات راه‌اندازی موفق
            Log::info('Filament RTL styles registered successfully', [
                'timestamp' => now()->toDateTimeString(),
                'provider' => self::class
            ]);
            
        } catch (\Throwable $e) {
            // لاگ کردن خطا
            Log::error('Error registering Filament RTL styles', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'line' => $e->getLine(),
                'user_id' => auth()->id() ?? 'مهمان',
                'timestamp' => now()->toDateTimeString(),
                'provider' => self::class
            ]);
            
            // در صورت خطا، همچنان exception را پرتاب کن
            throw $e;
        }
    }
}
