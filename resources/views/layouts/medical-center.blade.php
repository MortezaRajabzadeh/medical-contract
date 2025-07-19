<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'پنل مرکز درمانی') - سامانه قراردادهای پزشکی</title>

    <!-- فونت‌ها -->
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet" type="text/css" />

    <!-- استایل‌های CSS و JS اصلی (مستقیم بدون Vite) -->
    <link rel="stylesheet" href="{{ asset('build/assets/app--gi-JHlW.css') }}">

    <!-- لود مستقیم Alpine.js قبل از script اصلی -->

    <script src="{{ asset('build/assets/app-DNxiirP_.js') }}" defer></script>

    <!-- استایل‌های Livewire 3 بعد از Vite -->
    @livewireStyles

    <style>
        body {
            font-family: 'Vazirmatn', sans-serif;
        }
        .rtl {
            direction: rtl;
            text-align: right;
        }
    </style>
</head>
<body class="font-sans antialiased bg-gray-100">
    <div class="min-h-screen">
        <!-- منوی ناوبری -->
        <nav class="bg-gray-800" x-data="{ open: false }">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <a href="{{ route('medical-center.dashboard') }}" class="text-white font-bold text-xl">
                                سامانه قراردادهای پزشکی
                            </a>
                        </div>
                        <div class="hidden md:block">
                            <div class="mr-10 flex items-baseline space-x-4">
                                <a href="{{ route('medical-center.dashboard') }}"
                                   class="{{ request()->routeIs('medical-center.dashboard') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }} mr-4 px-3 py-2 rounded-md text-sm font-medium">
                                    داشبورد
                                </a>
                                <a href="{{ route('medical-center.contracts.index') }}"
                                   class="{{ request()->routeIs('medical-center.contracts.*') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }} mr-4 px-3 py-2 rounded-md text-sm font-medium">
                                    قراردادها
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="hidden md:block">
                        <div class="mr-4 flex items-center md:mr-6">
                            <!-- پروفایل کاربر -->
                            <div class="mr-3 relative" x-data="{ open: false }">
                                <div>
                                    <button @click="open = !open" class="max-w-xs bg-gray-800 rounded-full flex items-center text-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-800 focus:ring-white" id="user-menu" aria-haspopup="true">
                                        <span class="sr-only">منوی کاربر</span>
                                        <span class="text-gray-300 text-sm px-4 py-2">{{ auth()->user()->name ?? 'کاربر' }}</span>
                                    </button>
                                </div>
                                <div x-show="open"
                                     @click.away="open = false"
                                     x-transition:enter="transition ease-out duration-100"
                                     x-transition:enter-start="transform opacity-0 scale-95"
                                     x-transition:enter-end="transform opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-75"
                                     x-transition:leave-start="transform opacity-100 scale-100"
                                     x-transition:leave-end="transform opacity-0 scale-95"
                                     class="origin-top-right absolute left-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5"
                                     role="menu"
                                     aria-orientation="vertical"
                                     aria-labelledby="user-menu">

                                    <form method="POST" action="{{ route('medical-center.logout') }}">
                                        @csrf
                                        <button type="submit" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-right">
                                            خروج از سیستم
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="-mr-2 flex md:hidden">
                        <!-- منوی موبایل -->
                        <button @click="open = !open" class="bg-gray-800 inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-800 focus:ring-white">
                            <span class="sr-only">باز کردن منو</span>
                            <svg class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path x-show="!open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                <path x-show="open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- منو موبایل -->
            <div x-show="open" class="md:hidden">
                <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
                    <a href="{{ route('medical-center.dashboard') }}"
                        class="{{ request()->routeIs('medical-center.dashboard') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }} block px-3 py-2 rounded-md text-base font-medium">
                        داشبورد
                    </a>
                    <a href="{{ route('medical-center.contracts.index') }}"
                        class="{{ request()->routeIs('medical-center.contracts.*') ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }} block px-3 py-2 rounded-md text-base font-medium">
                        قراردادها
                    </a>
                </div>
                <div class="pt-4 pb-3 border-t border-gray-700">
                    <div class="flex items-center px-5">
                        <div class="mr-3">
                            <div class="text-base font-medium leading-none text-white">{{ auth()->user()->name ?? 'کاربر' }}</div>
                            <div class="text-sm font-medium leading-none text-gray-400">{{ auth()->user()->email ?? '' }}</div>
                        </div>
                    </div>
                    <div class="mt-3 px-2 space-y-1">
                        <form method="POST" action="{{ route('medical-center.logout') }}">
                            @csrf
                            <button type="submit" class="block w-full text-right px-3 py-2 rounded-md text-base font-medium text-gray-400 hover:text-white hover:bg-gray-700">
                                خروج از سیستم
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </nav>

        <!-- هدر و خطاها -->
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 rtl">
                <!-- نمایش پیام‌های خطا -->
                @if (session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline">{{ session('error') }}</span>
                    </div>
                @endif

                <!-- نمایش پیام‌های موفقیت -->
                @if (session('status') || session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline">{{ session('status') ?? session('success') }}</span>
                    </div>
                @endif

                <!-- نمایش خطاهای اعتبارسنجی -->
                @if ($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </header>

        <!-- محتوای اصلی -->
        <main>
            @yield('content')
        </main>

        <!-- فوتر -->
        <footer class="bg-white">
            <div class="max-w-7xl mx-auto py-6 px-4 overflow-hidden sm:px-6 lg:px-8 rtl">
                <p class="mt-5 text-center text-sm text-gray-500">
                    &copy; {{ date('Y') }} سامانه قراردادهای پزشکی - تمامی حقوق محفوظ است
                </p>
            </div>
        </footer>
    </div>

    <!-- Alpine.js از طریق Vite لود می‌شود -->

    <!-- اسکریپت‌های Livewire 3 -->
    @livewireScripts

    <script>
        // تعریف رویدادهای Livewire برای اعلانات
        document.addEventListener('livewire:initialized', () => {
            console.log('Livewire 3 initialized successfully!');

            // اطمینان از وجود Livewire.on
            if (typeof Livewire !== 'undefined' && typeof Livewire.on === 'function') {
                // اضافه کردن رویدادهای برای پیغام‌های سراسری
                Livewire.on('contractUploaded', (message) => {
                    console.log('Contract uploaded event received:', message);
                    // می‌توانید اینجا اعلان سراسری نشان دهید
                });

                Livewire.on('uploadError', (message) => {
                    console.log('Upload error event received:', message);
                    // می‌توانید اینجا اعلان خطای سراسری نشان دهید
                });
            } else {
                console.error('Livewire or Livewire.on is not defined!');
            }
        });
    </script>
</body>
</html>
