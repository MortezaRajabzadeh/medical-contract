<div class="bg-white shadow sm:rounded-lg" dir="rtl">
    <div class="px-4 py-5 sm:p-6">
        <h3 class="text-lg leading-6 font-medium text-gray-900 text-right">وضعیت قرارداد</h3>
        
        <div class="mt-5">
            <div class="rounded-md bg-gray-50 px-6 py-5">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="mr-3">
                        <h4 class="text-sm font-medium text-gray-800 text-right">
                            وضعیت فعلی: 
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                @switch($contract->status)
                                    @case('pending')
                                        bg-yellow-100 text-yellow-800
                                        @break
                                    @case('uploaded')
                                        bg-blue-100 text-blue-800
                                        @break
                                    @case('approved')
                                        bg-green-100 text-green-800
                                        @break
                                    @case('active')
                                        bg-green-100 text-green-800
                                        @break
                                    @case('expired')
                                        bg-red-100 text-red-800
                                        @break
                                    @case('terminated')
                                        bg-red-100 text-red-800
                                        @break
                                    @default
                                        bg-gray-100 text-gray-800
                                @endswitch">
                                @switch($contract->status)
                                    @case('pending')
                                        در انتظار امضا
                                    @break
                                    @case('uploaded')
                                        بارگزاری شده
                                    @break
                                    @case('under_review')
                                        در دست بررسی
                                    @break
                                    @case('approved')
                                        تایید نهایی
                                    @break
                                    @default
                                        نامشخص
                                @endswitch
                            </span>
                        </h4>
                        @if($contract->approved_at)
                            <div class="mt-1 text-sm text-gray-600">
                                آخرین به‌روزرسانی توسط {{ $contract->approvedBy->name ?? 'سیستم' }} در {{ \Morilog\Jalali\Jalalian::fromDateTime($contract->approved_at)->format('%d %B %Y %H:%i') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <form wire:submit.prevent="updateStatus" class="mt-6">
                <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                    <div class="sm:col-span-4">
                        <label for="currentStatus" class="block text-sm font-medium text-gray-700 text-right">به‌روزرسانی وضعیت</label>
                        <select id="currentStatus" 
                                wire:model="currentStatus"
                                class="mt-1 block w-full pr-3 pl-10 py-2 text-base text-right border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md" dir="rtl">
                            @foreach($statusOptions as $value => $label)
                                <option value="{{ $value }}" {{ $contract->status === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="sm:col-span-6">
                        <label for="statusComment" class="block text-sm font-medium text-gray-700 text-right">
                            توضیحات (اختیاری)
                        </label>
                        <div class="mt-1">
                            <textarea id="statusComment" 
                                    wire:model="statusComment"
                                    rows="3" 
                                    class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border border-gray-300 rounded-md text-right" dir="rtl"
                                    placeholder="توضیحی درباره تغییر وضعیت اضافه کنید"></textarea>
                        </div>
                        <p class="mt-2 text-sm text-gray-500 text-right">
                            توضیح مختصری درباره علت تغییر وضعیت.
                        </p>
                    </div>
                </div>

                <div class="mt-5">
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            wire:loading.attr="disabled"
                            wire:target="updateStatus">
                        <svg wire:loading wire:target="updateStatus" class="animate-spin -mr-1 ml-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span wire:loading.remove wire:target="updateStatus">به‌روزرسانی وضعیت</span>
                        <span wire:loading wire:target="updateStatus">در حال به‌روزرسانی...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('livewire:load', function () {
        Livewire.on('contract-status-updated', (contractId) => {
            // Refresh any components that depend on contract status
            Livewire.emit('refreshContractStatus', contractId);
        });
    });
</script>
@endpush
