@extends('layouts.medical-center')

@section('title', 'مشاهده قرارداد')

@section('content')
<div class="py-12 rtl" dir="rtl">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <!-- بخش هدر و اطلاعات قرارداد -->
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-800">
                        قرارداد شماره {{ $contract->contract_number }}
                    </h2>
                    <a href="{{ route('medical-center.contracts.index') }}" 
                       class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:outline-none focus:border-gray-700 focus:ring focus:ring-gray-200 active:bg-gray-800 disabled:opacity-25 transition">
                        بازگشت به لیست قراردادها
                    </a>
                </div>
                
                <!-- خلاصه اطلاعات قرارداد -->
                <div class="bg-gray-50 rounded-lg p-6 mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div class="p-4 bg-white rounded-lg shadow-sm">
                            <p class="text-sm text-gray-500 mb-1">شماره قرارداد</p>
                            <p class="font-semibold">{{ $contract->contract_number }}</p>
                        </div>
                        
                        @php
                            $statusColor = [
                                'pending' => 'text-yellow-600 bg-yellow-100',
                                'review' => 'text-blue-600 bg-blue-100',
                                'approved' => 'text-green-600 bg-green-100',
                                'rejected' => 'text-red-600 bg-red-100',
                                'uploaded' => 'text-purple-600 bg-purple-100', // اضافه کردن وضعیت uploaded
                                'service' => 'text-indigo-600 bg-indigo-100', // اضافه کردن وضعیت service
                            ][$contract->status] ?? 'text-gray-600 bg-gray-100';
                            
                            $statusText = [
                                'pending' => 'در انتظار امضاء',
                                'review' => 'در حال بررسی',
                                'approved' => 'تایید شده',
                                'rejected' => 'رد شده',
                                'uploaded' => 'آپلود شده', // اضافه کردن وضعیت uploaded
                                'service' => 'سرویس', // اضافه کردن وضعیت service
                            ][$contract->status] ?? 'نامشخص';
                        @endphp
                        
                        <div class="p-4 bg-white rounded-lg shadow-sm">
                            <p class="text-sm text-gray-500 mb-1">وضعیت</p>
                            <span class="px-3 py-1 rounded-full text-xs font-medium {{ $statusColor }}">
                                {{ $statusText }}
                            </span>
                        </div>
                        
                        <div class="p-4 bg-white rounded-lg shadow-sm">
                            <p class="text-sm text-gray-500 mb-1">تاریخ ایجاد</p>
                            <p>{{ \Morilog\Jalali\Jalalian::fromDateTime($contract->created_at)->format('%d %B %Y') }}</p>
                        </div>
                        
                        <div class="p-4 bg-white rounded-lg shadow-sm">
                            <p class="text-sm text-gray-500 mb-1">تاریخ انقضا</p>
                            <p>
                                @if($contract->expiry_date)
                                    {{ \Morilog\Jalali\Jalalian::fromDateTime($contract->expiry_date)->format('%d %B %Y') }}
                                @else
                                    -
                                @endif
                            </p>
                        </div>
                    </div>
                    
                    @if($contract->description)
                        <div class="mt-4 p-4 bg-white rounded-lg shadow-sm">
                            <p class="text-sm text-gray-500 mb-1">توضیحات</p>
                            <p>{{ $contract->description }}</p>
                        </div>
                    @endif
                    
                    <div class="mt-4 flex">
                        <a href="{{ route('medical-center.contracts.download', $contract->id) }}" 
                           class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:border-blue-700 focus:ring focus:ring-blue-200 active:bg-blue-600 disabled:opacity-25 transition ml-2">
                            دانلود قرارداد
                        </a>
                        
                        @if($contract->status == 'pending')
                            <a href="#upload" 
                               class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:border-green-700 focus:ring focus:ring-green-200 active:bg-green-600 disabled:opacity-25 transition">
                                آپلود قرارداد امضا شده
                            </a>
                        @endif
                    </div>
                </div>
                
                <!-- مشاهده قرارداد با استفاده از کامپوننت PDF Viewer -->
                <div class="bg-white rounded-lg shadow-lg p-1 mb-6">
                    <h3 class="text-lg font-semibold text-gray-700 px-6 py-4 border-b border-gray-200">
                        مشاهده فایل قرارداد
                    </h3>
                    <div class="p-2">
                        @livewire('contracts.pdf-viewer', ['contractId' => $contract->id])
                    </div>
                </div>
                
                <!-- بخش آپلود فایل امضا شده - فقط اگر وضعیت در انتظار امضا باشد -->
                @if($contract->status == 'pending')
                    <div id="upload" class="bg-white rounded-lg shadow-lg p-1">
                        <h3 class="text-lg font-semibold text-gray-700 px-6 py-4 border-b border-gray-200">
                            آپلود قرارداد امضا شده
                        </h3>
                        <div class="p-2">
                            @livewire('contracts.contract-upload', [
                                'contractId' => $contract->id, 
                                'medicalCenterId' => $contract->medical_center_id,
                                'mode' => 'signed'
                            ])
                        </div>
                    </div>
                @endif

                <!-- نمایش تاریخچه قرارداد -->
                <div class="mt-6 bg-gray-50 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">تاریخچه وضعیت</h3>
                    
                    <!-- ما فعلاً از جدول activity برای نمایش تاریخچه استفاده می‌کنیم -->
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center" aria-hidden="true">
                            <div class="w-full border-t border-gray-300"></div>
                        </div>
                        
                        <ol class="relative border-r border-gray-200 mr-3">
                            <li class="mb-6 mr-4">
                                <div class="absolute w-3 h-3 bg-gray-200 rounded-full mt-1.5 -right-1.5 border border-white"></div>
                                <time class="mb-1 text-sm font-normal leading-none text-gray-400 dark:text-gray-500">
                                    {{ \Morilog\Jalali\Jalalian::fromDateTime($contract->created_at)->format('%d %B %Y') }}
                                </time>
                                <h3 class="text-lg font-semibold text-gray-900">ایجاد قرارداد</h3>
                                <p class="text-base font-normal text-gray-500">
                                    قرارداد در سیستم ایجاد شد و در وضعیت "در انتظار امضاء" قرار گرفت.
                                </p>
                            </li>
                            
                            @if($contract->status != 'pending')
                                <li class="mb-6 mr-4">
                                    <div class="absolute w-3 h-3 bg-blue-200 rounded-full mt-1.5 -right-1.5 border border-white"></div>
                                    <time class="mb-1 text-sm font-normal leading-none text-gray-400">
                                        {{ \Morilog\Jalali\Jalalian::fromDateTime($contract->updated_at)->format('%d %B %Y') }}
                                    </time>
                                    <h3 class="text-lg font-semibold text-gray-900">تغییر وضعیت</h3>
                                    <p class="text-base font-normal text-gray-500">
                                        وضعیت قرارداد به "{{ $statusText }}" تغییر یافت.
                                    </p>
                                </li>
                            @endif
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
