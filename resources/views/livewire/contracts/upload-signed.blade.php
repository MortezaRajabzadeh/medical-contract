<div class="max-w-4xl mx-auto p-6">
    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    @error('access')
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
            <span class="block sm:inline">{{ $message }}</span>
        </div>
    @enderror

    <div class="bg-white shadow-lg rounded-lg p-6">
        <h2 class="text-2xl font-bold mb-6 text-right">آپلود قرارداد امضا شده</h2>
        
        <!-- وضعیت فعلی قرارداد -->
        <div class="mb-6 border-r-4 border-blue-500 pr-4 py-2 text-right">
            <p class="text-gray-700 mb-1">وضعیت فعلی قرارداد:</p>
            <div class="flex justify-end items-center">
                <span class="
                    {{ $currentStatus === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                    {{ $currentStatus === 'review' ? 'bg-blue-100 text-blue-800' : '' }}
                    {{ $currentStatus === 'approved' ? 'bg-green-100 text-green-800' : '' }}
                    {{ $currentStatus === 'rejected' ? 'bg-red-100 text-red-800' : '' }}
                    px-3 py-1 rounded-full text-sm font-medium">
                    {{ $currentStatus === 'pending' ? 'در انتظار امضاء' : '' }}
                    {{ $currentStatus === 'review' ? 'در حال بررسی' : '' }}
                    {{ $currentStatus === 'approved' ? 'تایید شده' : '' }}
                    {{ $currentStatus === 'rejected' ? 'رد شده' : '' }}
                </span>
            </div>
        </div>

        <!-- فایل امضا شده فعلی (اگر وجود داشته باشد) -->
        @if ($currentSignedFile)
            <div class="mb-6 p-4 border border-gray-300 rounded-md bg-gray-50 text-right">
                <h3 class="font-semibold text-lg mb-2">قرارداد امضا شده فعلی</h3>
                <div class="flex items-center justify-between">
                    <div class="flex space-x-2">
                        <!-- دکمه حذف فایل -->
                        @if ($currentStatus !== 'approved')
                            <button type="button" 
                                    wire:click="deleteSignedContract"
                                    wire:confirm="آیا از حذف فایل امضا شده اطمینان دارید؟"
                                    class="px-3 py-2 text-xs bg-red-600 text-white rounded hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                                <span wire:loading.remove wire:target="deleteSignedContract">حذف</span>
                                <span wire:loading wire:target="deleteSignedContract">در حال حذف...</span>
                            </button>
                        @endif
                        
                        <!-- دکمه دانلود فایل -->
                        <a href="{{ route('contracts.download.signed', ['id' => $contractId]) }}" 
                           class="px-3 py-2 text-xs bg-blue-600 text-white rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            دانلود
                        </a>
                    </div>
                    
                    <div class="flex items-center space-x-2 ml-4">
                        <p class="text-gray-700">{{ $currentSignedFile['filename'] }}</p>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V8a2 2 0 00-2-2h-5L9 4H4zm7 5a1 1 0 10-2 0v1.586l-.293-.293a1 1 0 10-1.414 1.414l2 2 .002.002a1 1 0 001.414-.002l2-2a1 1 0 00-1.414-1.414l-.293.293V9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                </div>
            </div>
        @endif

        <!-- اگر قرارداد تایید شده نیست، فرم آپلود را نمایش بده -->
        @if ($currentStatus !== 'approved')
            <form wire:submit="save" class="mt-6 text-right">
                <!-- آپلود فایل امضا شده -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2 text-right">فایل قرارداد امضا شده</label>
                    <div x-data="{ 
                        uploading: false, 
                        progress: 0,
                        dragOver: false 
                    }" 
                        x-on:livewire-upload-start="uploading = true"
                        x-on:livewire-upload-finish="uploading = false; progress = 0"
                        x-on:livewire-upload-progress="progress = $event.detail.progress">
                        
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center"
                            :class="{ 'border-blue-500 bg-blue-50': dragOver }"
                            @dragover.prevent="dragOver = true"
                            @dragleave.prevent="dragOver = false"
                            @drop.prevent="
                                dragOver = false;
                                const files = $event.dataTransfer.files;
                                if (files.length > 0) {
                                    $wire.upload('signedFile', files[0]);
                                }
                            ">
                            
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                            
                            <p class="mt-2 text-sm text-gray-600">
                                <span class="font-medium">برای آپلود کلیک کنید</span> یا فایل را اینجا بکشید و رها کنید
                            </p>
                            <p class="text-xs text-gray-500">فایل PDF تا حداکثر 10MB</p>
                            
                            <input type="file" wire:model="signedFile" 
                                class="hidden" accept=".pdf">
                        </div>
                        
                        <!-- نمایش پیشرفت آپلود -->
                        <div x-show="uploading" x-transition class="mt-4">
                            <div class="bg-gray-200 rounded-full h-2">
                                <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" 
                                    :style="`width: ${progress}%`"></div>
                            </div>
                            <p class="text-sm text-gray-600 mt-2 text-left">در حال آپلود... <span x-text="progress"></span>%</p>
                        </div>
                    </div>
                    @error('signedFile') <span class="text-red-500 text-sm block text-right">{{ $message }}</span> @enderror
                </div>

                <div class="flex justify-end space-x-4">
                    <button type="submit" 
                            class="bg-green-600 text-white px-6 py-2 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                            wire:loading.attr="disabled"
                            wire:target="save">
                        <span wire:loading.remove wire:target="save">آپلود قرارداد امضا شده</span>
                        <span wire:loading wire:target="save">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            در حال پردازش...
                        </span>
                    </button>
                </div>
            </form>
        @else
            <div class="mt-6 p-4 border border-green-300 rounded-md bg-green-50 text-right">
                <p class="text-green-700">قرارداد شما تایید شده است و نیازی به آپلود مجدد نمی‌باشد.</p>
            </div>
        @endif
    </div>
</div>
