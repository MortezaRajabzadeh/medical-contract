
@extends('layouts.medical-center')

@section('title', 'قراردادهای مرکز درمانی')

@section('content')
<div class="py-12 rtl" dir="rtl">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-800">قراردادهای {{ $medicalCenter->name }}</h2>
                    <a href="{{ route('medical-center.dashboard') }}"
                       class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:outline-none focus:border-gray-700 focus:ring focus:ring-gray-200 active:bg-gray-800 disabled:opacity-25 transition">
                        بازگشت به داشبورد
                    </a>
                </div>

                <!-- جستجو و فیلترها -->
                <div class="bg-gray-50 rounded-lg p-6 mb-6">
                    <form method="GET" action="{{ route('medical-center.contracts.index') }}">
                        <div class="flex flex-col md:flex-row md:items-center space-y-4 md:space-y-0 md:space-x-4">
                            <div class="w-full md:w-1/3">
                                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">جستجو</label>
                                <input type="text" name="search" id="search" value="{{ request('search') }}"
                                    class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                    placeholder="شماره قرارداد...">
                            </div>
                            <div class="w-full md:w-1/3 md:mr-4">
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">وضعیت</label>
                                <select id="status" name="status"
                                    class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    <option value="">همه وضعیت‌ها</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>در انتظار امضاء</option>
                                    <option value="review" {{ request('status') == 'review' ? 'selected' : '' }}>در حال بررسی</option>
                                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>تایید شده</option>
                                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>رد شده</option>
                                </select>
                            </div>
                            <div class="w-full md:w-1/3 md:mr-4 md:flex md:items-end">
                                <button type="submit"
                                    class="w-full inline-flex items-center justify-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:border-blue-700 focus:ring focus:ring-blue-200 active:bg-blue-600 disabled:opacity-25 transition">
                                    جستجو
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- لیست قراردادها -->
                <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                    @if(count($contracts) > 0)
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        شماره قرارداد
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        تاریخ ایجاد
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        تاریخ انقضا
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        وضعیت
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
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
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $contract->contract_number }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ $contract->title }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500">
                                                {{ \Morilog\Jalali\Jalalian::fromDateTime($contract->created_at)->format('%d %B %Y') }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500">
                                                @if($contract->expiry_date)
                                                    @php
                                                        try {
                                                            echo \Morilog\Jalali\Jalalian::fromDateTime($contract->expiry_date)->format('%d %B %Y');
                                                        } catch (\Exception $e) {
                                                            echo $contract->expiry_date; // نمایش مستقیم تاریخ اگر تبدیل با خطا مواجه شود
                                                        }
                                                    @endphp
                                                @else
                                                    -
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusColor }}">
                                                {{ $statusText }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('medical-center.contracts.view', $contract->id) }}" class="text-blue-600 hover:text-blue-900 ml-3">مشاهده</a>
                                            <a href="{{ route('medical-center.contracts.download', $contract->id) }}" class="text-indigo-600 hover:text-indigo-900">دانلود</a>
                                            @if($contract->status == 'pending')
                                                <button type="button"
                                                    class="text-green-600 hover:text-green-900 mr-3"
                                                    onclick="window.location.href='{{ route('medical-center.contracts.view', $contract->id) }}#upload'">
                                                    آپلود امضا شده
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <!-- پیجینیشن -->
                        <div class="px-6 py-4 border-t border-gray-200">
                            {{ $contracts->links() }}
                        </div>
                    @else
                        <div class="text-center py-10">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V7a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">قراردادی یافت نشد</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                در حال حاضر هیچ قراردادی برای مرکز درمانی شما ثبت نشده است.
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
