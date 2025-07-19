<div class="max-w-4xl mx-auto p-6" dir="rtl">
    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif

    <form wire:submit="save">
        <div class="bg-white shadow-lg rounded-lg p-6">
            <h2 class="text-2xl font-bold mb-6 text-right">آپلود قرارداد جدید</h2>
            
            <!-- اطلاعات پایه قرارداد -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2 text-right">عنوان قرارداد</label>
                    <input type="text" wire:model="title" dir="rtl"
                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 text-right">
                    @error('title') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2 text-right">نوع قرارداد</label>
                    <select wire:model="contractType" dir="rtl"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 text-right">
                        <option value="">نوع را انتخاب کنید</option>
                        <option value="service">خدمات</option>
                        <option value="equipment">تجهیزات</option>
                        <option value="pharmaceutical">دارویی</option>
                        <option value="maintenance">نگهداری</option>
                        <option value="consulting">مشاوره</option>
                    </select>
                    @error('contractType') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- آپلود فایل با نمایش پیشرفت -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2 text-right">سند قرارداد</label>
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
                                 $wire.upload('contractFile', files[0]);
                             }
                         ">
                         
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                        
                        <p class="mt-2 text-sm text-gray-600 text-right">
                            <span class="font-medium">برای آپلود کلیک کنید</span> یا فایل را بکشید و رها کنید
                        </p>
                        <p class="text-xs text-gray-500 text-right">فرمت PDF، DOC، DOCX تا حجم 10 مگابایت</p>
                        
                        <input type="file" wire:model="contractFile" 
                               class="hidden" accept=".pdf,.doc,.docx">
                    </div>
                    
                    <!-- Progress Bar -->
                    <div x-show="uploading" x-transition class="mt-4">
                        <div class="bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" 
                                 :style="`width: ${progress}%`"></div>
                        </div>
                        <p class="text-sm text-gray-600 mt-2 text-right">در حال آپلود... <span x-text="progress"></span>%</p>
                    </div>
                </div>
                @error('contractFile') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <!-- فیلدهای اضافی -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2 text-right">نام فروشنده</label>
                    <input type="text" wire:model="vendorName" dir="rtl"
                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 text-right">
                    @error('vendorName') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2 text-right">مبلغ قرارداد (تومان)</label>
                    <input type="number" step="0.01" wire:model="contractValue" 
                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 text-right" dir="ltr">
                    @error('contractValue') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2 text-right">تاریخ شروع</label>
                    <input type="date" wire:model="startDate" 
                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('startDate') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                
                <div class="md:col-span-3">
                    <label class="block text-sm font-medium text-gray-700 mb-2 text-right">توضیحات</label>
                    <textarea wire:model="description" rows="3" dir="rtl"
                              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 text-right"></textarea>
                    @error('description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2 text-right">تاریخ پایان</label>
                    <input type="date" wire:model="endDate" 
                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('endDate') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="flex justify-end space-x-reverse space-x-4">
                <button type="button" 
                        class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    لغو
                </button>
                <button type="submit" 
                        class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                        wire:loading.attr="disabled"
                        wire:target="save">
                    <span wire:loading.remove wire:target="save">آپلود قرارداد</span>
                    <span wire:loading wire:target="save">
                        <svg class="animate-spin -mr-1 ml-3 h-5 w-5 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        در حال پردازش...
                    </span>
                </button>
            </div>
        </div>
    </form>
</div>
