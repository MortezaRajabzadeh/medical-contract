<?php

return [
    'breadcrumb' => 'مسیر',
    'dashboard' => [
        'title' => 'داشبورد',
    ],
    'widgets' => [
        'account' => [
            'heading' => 'سلام، :name',
            'links' => [
                'account' => [
                    'label' => 'مدیریت حساب کاربری',
                ],
            ],
        ],
    ],
    'pagination' => [
        'label' => 'صفحه‌بندی',
        'overview' => 'نمایش :first تا :last از :total نتیجه',
        'fields' => [
            'records_per_page' => [
                'label' => 'در هر صفحه',
            ],
        ],
        'buttons' => [
            'go_to_page' => [
                'label' => 'برو به صفحه :page',
            ],
            'next' => [
                'label' => 'بعدی',
            ],
            'previous' => [
                'label' => 'قبلی',
            ],
        ],
    ],
    'buttons' => [
        'filter' => [
            'label' => 'فیلتر',
        ],
        'open_actions' => [
            'label' => 'باز کردن عملیات‌ها',
        ],
        'toggle_columns' => [
            'label' => 'تغییر وضعیت ستون‌ها',
        ],
    ],
    'empty_state' => [
        'heading' => 'هیچ موردی یافت نشد',
        'description' => 'هیچ :model ای یافت نشد.',
    ],
    'filters' => [
        'buttons' => [
            'apply' => [
                'label' => 'اعمال فیلترها',
            ],
            'remove' => [
                'label' => 'حذف فیلتر',
            ],
            'remove_all' => [
                'label' => 'حذف همه فیلترها',
                'tooltip' => 'حذف همه فیلترها',
            ],
            'reset' => [
                'label' => 'بازنشانی فیلترها',
            ],
        ],
        'indicator' => 'فیلترهای فعال',
        'multi_select' => [
            'placeholder' => 'همه',
        ],
        'select' => [
            'placeholder' => 'همه',
        ],
        'trashed' => [
            'label' => 'موارد حذف‌شده',
            'subtext' => 'نمایش موارد حذف‌شده',
            'without' => 'بدون موارد حذف‌شده',
            'with' => 'با موارد حذف‌شده',
            'only' => 'فقط موارد حذف‌شده',
        ],
    ],
    'forms' => [
        'required' => 'الزامی',
        'buttons' => [
            'create' => [
                'label' => 'ایجاد :label',
            ],
            'save' => [
                'label' => 'ذخیره',
            ],
            'update' => [
                'label' => 'بروزرسانی',
            ],
            'cancel' => [
                'label' => 'انصراف',
            ],
            'create_another' => [
                'label' => 'ایجاد و ایجاد دیگر',
            ],
            'save_and_continue' => [
                'label' => 'ذخیره و ادامه',
            ],
        ],
    ],
    'actions' => [
        'modal' => [
            'requires_confirmation_subheading' => 'آیا از انجام این کار اطمینان دارید؟',
            'buttons' => [
                'cancel' => [
                    'label' => 'انصراف',
                ],
                'confirm' => [
                    'label' => 'تأیید',
                ],
                'submit' => [
                    'label' => 'ارسال',
                ],
            ],
        ],
        'replicate' => [
            'label' => 'تکثیر',
            'messages' => [
                'replicated' => 'مورد تکثیر شد',
            ],
        ],
        'delete' => [
            'label' => 'حذف',
            'messages' => [
                'deleted' => 'مورد حذف شد',
            ],
        ],
        'restore' => [
            'label' => 'بازیابی',
            'messages' => [
                'restored' => 'مورد بازیابی شد',
            ],
        ],
        'force_delete' => [
            'label' => 'حذف کامل',
            'messages' => [
                'deleted' => 'مورد به طور کامل حذف شد',
            ],
        ],
    ],
    'table' => [
        'columns' => [
            'searchable' => 'جستجو',
            'sortable' => 'مرتب‌سازی',
        ],
        'fields' => [
            'search' => [
                'label' => 'جستجو',
                'placeholder' => 'جستجو',
            ],
        ],
    ],
    'resources' => [
        'buttons' => [
            'create' => [
                'label' => 'ایجاد :label',
            ],
            'edit' => [
                'label' => 'ویرایش',
            ],
            'view' => [
                'label' => 'مشاهده',
            ],
        ],
    ],
];
