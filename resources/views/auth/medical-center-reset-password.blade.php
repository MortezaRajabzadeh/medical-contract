<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- فونت وزیرمتن برای متون فارسی -->
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet" type="text/css" />
    <title>بازنشانی رمز عبور | پورتال قراردادهای مرکز پزشکی</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100" style="font-family: 'Vazirmatn', 'Tahoma', sans-serif;">
    <div class="min-h-screen flex flex-col items-center justify-center">
        <div class="max-w-md w-full bg-white shadow-lg rounded-lg overflow-hidden">
            <div class="bg-blue-600 py-6">
                <h1 class="text-center text-white text-xl font-bold">بازنشانی رمز عبور</h1>
                <p class="text-center text-blue-100 text-sm mt-1">پورتال قراردادهای مرکز پزشکی</p>
            </div>

            <div class="px-8 py-6">
                @if (session('status'))
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('medical-center.password.update') }}">
                    @csrf

                    <input type="hidden" name="token" value="{{ $token }}">

                    <div class="mb-4">
                        <label for="email" class="block text-gray-700 text-sm font-bold mb-2 text-right">
                            آدرس ایمیل
                        </label>
                        <input id="email" type="email" 
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline text-right @error('email') border-red-500 @enderror" 
                            name="email" value="{{ $email ?? old('email') }}" required autocomplete="email" autofocus readonly dir="rtl">

                        @error('email')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="password" class="block text-gray-700 text-sm font-bold mb-2 text-right">
                            رمز عبور جدید
                        </label>
                        <input id="password" type="password" 
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline text-right @error('password') border-red-500 @enderror" 
                            name="password" required autocomplete="new-password" dir="rtl">

                        @error('password')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-6">
                        <label for="password-confirm" class="block text-gray-700 text-sm font-bold mb-2 text-right">
                            تایید رمز عبور جدید
                        </label>
                        <input id="password-confirm" type="password" 
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline text-right" 
                            name="password_confirmation" required autocomplete="new-password" dir="rtl">
                    </div>

                    <div class="flex items-center justify-center">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full">
                            بازنشانی رمز عبور
                        </button>
                    </div>
                </form>
            </div>
            
            <div class="bg-gray-100 px-8 py-4">
                <p class="text-center text-gray-500 text-xs">
                    &copy; {{ date('Y') }} سامانه مدیریت قراردادهای پزشکی. تمامی حقوق محفوظ است.
                </p>
            </div>
        </div>
    </div>
    
    <script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
