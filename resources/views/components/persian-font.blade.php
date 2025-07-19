{{-- کامپوننت فونت فارسی برای پنل مدیریت --}}
<link rel="preload" href="{{ asset('fonts/vazirmatn/Vazirmatn-Regular.woff2') }}" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="{{ asset('fonts/vazirmatn/Vazirmatn-Medium.woff2') }}" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="{{ asset('fonts/vazirmatn/Vazirmatn-Bold.woff2') }}" as="font" type="font/woff2" crossorigin>

<style>
    /* تعریف فونت وزیر متن */
    @font-face {
        font-family: 'VazirMatn';
        src: url('{{ asset('fonts/vazirmatn/Vazirmatn-Regular.woff2') }}') format('woff2');
        font-weight: normal;
        font-style: normal;
        font-display: swap;
    }

    @font-face {
        font-family: 'VazirMatn';
        src: url('{{ asset('fonts/vazirmatn/Vazirmatn-Medium.woff2') }}') format('woff2');
        font-weight: 500;
        font-style: normal;
        font-display: swap;
    }

    @font-face {
        font-family: 'VazirMatn';
        src: url('{{ asset('fonts/vazirmatn/Vazirmatn-Bold.woff2') }}') format('woff2');
        font-weight: bold;
        font-style: normal;
        font-display: swap;
    }

    /* اعمال استایل‌های فارسی به المان‌های Filament */
    body {
        font-family: 'VazirMatn', 'Tahoma', sans-serif !important;
    }

    /* راست‌چین کردن تمام بخش‌های اصلی */
    .fi-sidebar, .fi-main, .fi-topbar, 
    .fi-dropdown-panel, .fi-modal, .fi-form,
    .fi-ta, .fi-ta-text, .fi-input, .fi-input-wrapper,
    .fi-btn, .fi-badge, .fi-tabs, .fi-card,
    .fi-header, .fi-sidebar-nav, .fi-breadcrumbs,
    .fi-ac, .fi-dropdown-list, .fi-resource-header,
    .fi-section, .fi-fo-field-wrp, .fi-ac-actions,
    .fi-select, .fi-ta-ctn, .fi-pagination,
    .filament-notifications, .filament-tables, .filament-resources-list {
        direction: rtl !important;
        text-align: right !important;
    }

    /* تنظیم حاشیه‌ها برای حالت RTL */
    .fi-sidebar-item-label {
        margin-left: 0 !important;
        margin-right: 0.5rem !important;
    }

    /* تنظیم آیکون‌های فلش جهت‌ها */
    .fi-pagination svg,
    .fi-dropdown-trigger svg {
        transform: scaleX(-1);
    }

    /* تنظیم جدول‌ها برای RTL */
    .fi-ta th,
    .fi-ta td {
        text-align: right !important;
    }

    /* تنظیمات اضافی برای فرم‌ها */
    .fi-fo-field-wrp {
        text-align: right !important;
    }

    /* تنظیم باکس فیلتر */
    .fi-filter-popover,
    .fi-filter-content,
    .fi-filter-group {
        text-align: right !important;
        direction: rtl !important;
    }
    /* تنظیم جهت برای برخی کلاس‌های خاص */
    .fi-modal-content,
    .fi-modal-header,
    .fi-modal-footer,
    .fi-header-heading,
    .fi-ta-content,
    .fi-fo-component-ctn,
    .fi-btn-actions,
    .fi-pagination, 
    .fi-ta-filters,
    .fi-ta-options,
    .fi-in-affixes {        
        direction: rtl !important;
        text-align: right !important;
    }

    /* اصلاح جهت دکمه‌ها در فرم‌ها */
    .fi-modal-footer .fi-btn:first-child {
        margin-right: 0 !important;
        margin-left: 0.5rem !important;
    }

    /* اصلاح جهت آیکون‌ها در منو */
    .fi-sidebar-group-items .fi-sidebar-item-icon {
        margin-right: 0 !important;
        margin-left: 0.75rem !important;
    }

    /* اصلاح فاصله‌گذاری بین عناصر */
    .fi-tabs .flex .ml-1, 
    .fi-modal-header .mr-auto {
        margin-left: auto !important;
        margin-right: 0 !important;
    }
</style>
