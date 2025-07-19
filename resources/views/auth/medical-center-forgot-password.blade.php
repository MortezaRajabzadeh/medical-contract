<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- فونت وزیرمتن برای متون فارسی -->
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet" type="text/css" />
    <title>بازیابی رمز عبور | پورتال قراردادهای مرکز پزشکی</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100" style="font-family: 'Vazirmatn', 'Tahoma', sans-serif;">
    <div class="min-h-screen flex flex-col items-center justify-center">
        <div class="max-w-md w-full bg-white shadow-lg rounded-lg overflow-hidden">
            <div class="bg-blue-600 py-6">
                <h1 class="text-center text-white text-xl font-bold">بازیابی رمز عبور</h1>
                <p class="text-center text-blue-100 text-sm mt-1">پورتال قراردادهای مرکز پزشکی</p>
            </div>

            <div class="px-8 py-6">
                @if (session('status'))
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                        {{ session('status') }}
                    </div>
                @endif

                <p class="text-gray-700 mb-6 text-right">
                    لطفاً آدرس ایمیل خود را وارد کنید. ما یک لینک برای بازیابی رمز عبور برای شما ارسال خواهیم کرد.
                </p>

                <form method="POST" action="{{ route('medical-center.password.email') }}">
                    @csrf

                    <div class="mb-6">
                        <label for="email" class="block text-gray-700 text-sm font-bold mb-2 text-right">
                            آدرس ایمیل
                        </label>
                        <input id="email" type="email" 
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline text-right @error('email') border-red-500 @enderror" 
                            name="email" value="{{ old('email') }}" required autocomplete="email" autofocus dir="rtl">

                        @error('email')
                            <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-center mb-4">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full">
                            ارسال لینک بازیابی رمز عبور
                        </button>
                    </div>
                </form>
                
                <div class="text-center mt-4">
                    <a href="{{ route('medical-center.login') }}" class="text-sm text-blue-600 hover:text-blue-800">
                        بازگشت به صفحه ورود
                    </a>
                </div>
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
