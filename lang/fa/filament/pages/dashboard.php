<?php

return [
    'title' => 'داشبورد',
    'navigation' => [
        'label' => 'داشبورد',
        'group' => 'سیستم',
    ],
    'widgets' => [
        'overview' => [
            'heading' => 'خلاصه سیستم',
            'subheading' => 'آمار و اطلاعات کلی',
            'medical_centers' => 'مراکز درمانی',
            'contracts' => 'قراردادها',
            'users' => 'کاربران',
        ],
        'latest_contracts' => [
            'heading' => 'آخرین قراردادها',
            'subheading' => 'قراردادهای اخیر',
            'view_all' => 'مشاهده همه',
        ],
        'contract_by_status' => [
            'heading' => 'وضعیت قراردادها',
            'subheading' => 'تفکیک قراردادها بر اساس وضعیت',
            'pending' => 'در انتظار امضاء',
            'review' => 'در حال بررسی',
            'approved' => 'تایید شده',
            'rejected' => 'رد شده',
        ],
    ],
];
