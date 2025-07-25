<?php

return [
    'medical-center' => [
        'name' => 'مرکز درمانی',
        'plural' => 'مراکز درمانی',
        'navigation_label' => 'مراکز درمانی',
        'fields' => [
            'id' => 'شناسه',
            'name' => 'نام مرکز درمانی',
            'address' => 'آدرس',
            'phone' => 'تلفن',
            'email' => 'ایمیل',
            'logo' => 'لوگو',
            'status' => 'وضعیت',
            'created_at' => 'تاریخ ثبت',
            'updated_at' => 'تاریخ بروزرسانی',
            'users' => 'کاربران',
            'contracts' => 'قراردادها',
        ],
        'headings' => [
            'create' => 'ایجاد مرکز درمانی',
            'edit' => 'ویرایش مرکز درمانی',
            'view' => 'مشاهده مرکز درمانی',
            'list' => 'لیست مراکز درمانی',
        ],
    ],
    'contract' => [
        'name' => 'قرارداد',
        'plural' => 'قراردادها',
        'navigation_label' => 'قراردادها',
        'fields' => [
            'id' => 'شناسه',
            'contract_number' => 'شماره قرارداد',
            'medical_center_id' => 'مرکز درمانی',
            'file_path' => 'مسیر فایل',
            'signed_file_path' => 'مسیر فایل امضا شده',
            'status' => 'وضعیت',
            'expiry_date' => 'تاریخ انقضا',
            'description' => 'توضیحات',
            'created_at' => 'تاریخ ایجاد',
            'updated_at' => 'تاریخ بروزرسانی',
        ],
        'headings' => [
            'create' => 'ایجاد قرارداد',
            'edit' => 'ویرایش قرارداد',
            'view' => 'مشاهده قرارداد',
            'list' => 'لیست قراردادها',
        ],
        'status' => [
            'pending' => 'در انتظار امضاء',
            'review' => 'در حال بررسی',
            'approved' => 'تایید شده',
            'rejected' => 'رد شده',
        ],
    ],
    'user' => [
        'name' => 'کاربر',
        'plural' => 'کاربران',
        'navigation_label' => 'کاربران',
        'fields' => [
            'id' => 'شناسه',
            'name' => 'نام',
            'email' => 'ایمیل',
            'password' => 'رمز عبور',
            'password_confirmation' => 'تایید رمز عبور',
            'roles' => 'نقش‌ها',
            'medical_center_id' => 'مرکز درمانی',
            'user_type' => 'نوع کاربر',
            'created_at' => 'تاریخ ثبت',
            'updated_at' => 'تاریخ بروزرسانی',
        ],
        'headings' => [
            'create' => 'ایجاد کاربر',
            'edit' => 'ویرایش کاربر',
            'view' => 'مشاهده کاربر',
            'list' => 'لیست کاربران',
        ],
        'user_types' => [
            'admin' => 'مدیر سیستم',
            'medical_admin' => 'مدیر مرکز درمانی',
            'medical_staff' => 'کارمند مرکز درمانی',
        ],
    ],
];
