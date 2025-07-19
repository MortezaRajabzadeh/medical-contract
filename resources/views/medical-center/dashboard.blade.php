@extends('layouts.medical-center')

@section('title', 'داشبورد مرکز درمانی')

@section('content')
<div class="py-12 rtl" dir="rtl">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="flex flex-col md:flex-row justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-800 mb-4 md:mb-0">
                        خوش آمدید، {{ $medicalCenter->name }}
                    </h2>
                    <div class="flex items-center">
                        <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-3 py-1 rounded-full">
                            {{ $medicalCenter->license_number }}
                        </span>
                    </div>
                </div>

                <!-- وضعیت قرارداد -->
                <div class="bg-gray-50 rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">وضعیت قرارداد</h3>
                    <div class="flex flex-col md:flex-row md:items-center">
                        <div class="bg-white p-4 rounded-lg shadow-sm flex-grow md:mr-4 mb-4 md:mb-0">
                            @if(count($contracts) > 0)
                                @php
                                    $latestContract = $contracts->first();
                                    $statusColor = [
                                        'pending' => 'text-yellow-600 bg-yellow-100',
                                        'review' => 'text-blue-600 bg-blue-100',
                                        'approved' => 'text-green-600 bg-green-100',
                                        'rejected' => 'text-red-600 bg-red-100',
                                    ][$latestContract->status] ?? 'text-gray-600 bg-gray-100';
                                    
                                    $statusText = [
                                        'pending' => 'در انتظار امضاء',
                                        'review' => 'در حال بررسی',
                                        'approved' => 'تایید شده',
                                        'rejected' => 'رد شده',
                                    ][$latestContract->status] ?? 'نامشخص';
                                @endphp
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm text-gray-500 mb-1">شماره قرارداد</p>
                                        <p class="font-semibold">{{ $latestContract->contract_number }}</p>
                                    </div>
                                    <div>
                                        <span class="px-3 py-1 rounded-full text-xs font-medium {{ $statusColor }}">
                                            {{ $statusText }}
                                        </span>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <p class="text-sm text-gray-500 mb-1">تاریخ آخرین بروزرسانی</p>
                                    <p>{{ \Morilog\Jalali\Jalalian::fromDateTime($latestContract->updated_at)->format('%d %B %Y') }}</p>
                                </div>
                                <div class="mt-4 flex">
                                    <a href="{{ route('medical-center.contracts.view', $latestContract) }}" 
                                       class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:border-blue-700 focus:ring focus:ring-blue-200 active:bg-blue-600 disabled:opacity-25 transition ml-2">
                                        مشاهده قرارداد
                                    </a>
                                    <a href="{{ route('medical-center.contracts.download', $latestContract) }}" 
                                       class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:outline-none focus:border-gray-700 focus:ring focus:ring-gray-200 active:bg-gray-800 disabled:opacity-25 transition">
                                        دانلود قرارداد
                                    </a>
                                </div>
                            @else
                                <div class="text-center py-6">
                                    <p class="text-gray-500">هنوز هیچ قراردادی برای شما تعریف نشده است.</p>
                                </div>
                            @endif
                        </div>
                        
                        <div class="bg-white p-4 rounded-lg shadow-sm md:w-1/3">
                            <h4 class="font-semibold text-gray-700 mb-3">اطلاعات مرکز</h4>
                            <div class="space-y-2">
                                <p class="text-sm flex justify-between">
                                    <span class="text-gray-500">نام مرکز:</span>
                                    <span class="font-medium">{{ $medicalCenter->name }}</span>
                                </p>
                                <p class="text-sm flex justify-between">
                                    <span class="text-gray-500">شماره مجوز:</span>
                                    <span class="font-medium">{{ $medicalCenter->license_number }}</span>
                                </p>
                                <p class="text-sm flex justify-between">
                                    <span class="text-gray-500">تلفن:</span>
                                    <span class="font-medium">{{ $medicalCenter->phone }}</span>
                                </p>
                                <p class="text-sm flex justify-between">
                                    <span class="text-gray-500">استان:</span>
                                    <span class="font-medium">{{ $medicalCenter->province }}</span>
                                </p>
                                <p class="text-sm flex justify-between">
                                    <span class="text-gray-500">شهر:</span>
                                    <span class="font-medium">{{ $medicalCenter->city }}</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- آخرین قراردادها -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-700">آخرین قراردادها</h3>
                        <a href="{{ route('medical-center.contracts.index') }}" class="text-blue-600 hover:text-blue-800 text-sm">
                            مشاهده همه قراردادها
                        </a>
                    </div>
                    
                    @if(count($contracts) > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            شماره قرارداد
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            تاریخ ایجاد
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            وضعیت
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            عملیات
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($contracts as $contract)
                                        @php
                                            $statusColor = [
                                                'pending' => 'text-yellow-600 bg-yellow-100',
                                                'review' => 'text-blue-600 bg-blue-100',
                                                'approved' => 'text-green-600 bg-green-100',
                                                'rejected' => 'text-red-600 bg-red-100',
                                            ][$contract->status] ?? 'text-gray-600 bg-gray-100';
                                            
                                            $statusText = [
                                                'pending' => 'در انتظار امضاء',
                                                'review' => 'در حال بررسی',
                                                'approved' => 'تایید شده',
                                                'rejected' => 'رد شده',
                                            ][$contract->status] ?? 'نامشخص';
                                        @endphp
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $contract->contract_number }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-500">
                                                    {{ \Morilog\Jalali\Jalalian::fromDateTime($contract->created_at)->format('%d %B %Y') }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusColor }}">
                                                    {{ $statusText }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <a href="{{ route('medical-center.contracts.view', $contract) }}" class="text-blue-600 hover:text-blue-900 ml-4">مشاهده</a>
                                                <a href="{{ route('medical-center.contracts.download', $contract) }}" class="text-indigo-600 hover:text-indigo-900">دانلود</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <p class="text-gray-500">هیچ قراردادی یافت نشد.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
