<div dir="rtl"
     x-data="{
        showNotification: false,
        notificationType: '',
        notificationMessage: '',
        showUploadSuccess: false,
        showUploadError: false,
        pdfVisible: false,

        init() {
            // استفاده از مکانیزم رویداد Livewire نسخه 3
            document.addEventListener('livewire:initialized', () => {
                // هماهنگی مقدار اولیه با متغیر Livewire
                this.pdfVisible = {{ $showViewer ? 'true' : 'false' }};

                // گوش دادن به تغییرات متغیر Livewire
                Livewire.on('showViewerChanged', (value) => {
                    this.pdfVisible = value;
                    console.log('PDF viewer visibility changed:', value);
                });

                Livewire.on('contractUploaded', (message) => {
                    this.handleSuccessNotification(message);
                });

                Livewire.on('uploadError', (message) => {
                    this.handleErrorNotification(message);
                });
            });
        },

        handleSuccessNotification(message) {
            this.notificationType = 'success';
            this.notificationMessage = message;
            this.showNotification = true;
            this.showUploadSuccess = true;
            this.showUploadError = false;
            this.autoHideNotification();
            console.log('Contract uploaded notification shown:', message);
        },

        handleErrorNotification(message) {
            this.notificationType = 'error';
            this.notificationMessage = message;
            this.showNotification = true;
            this.showUploadSuccess = false;
            this.showUploadError = true;
            this.autoHideNotification();
            console.log('Upload error notification shown:', message);
        },

        autoHideNotification() {
            setTimeout(() => {
                this.showNotification = false;
            }, 5000);
        }
     }">

    <!-- PDF Viewer Section -->
    <div x-show="pdfVisible"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform scale-95"
         x-transition:enter-end="opacity-100 transform scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 transform scale-100"
         x-transition:leave-end="opacity-0 transform scale-95"
         class="mt-4 bg-gray-100 rounded-lg overflow-hidden shadow-lg">

        @if (isset($contract->file_path) && pathinfo($contract->file_path, PATHINFO_EXTENSION) === 'pdf')
            <div class="relative">
                <iframe
                    src="{{ route('medical-center.contracts.file', $contract->id) }}"
                    class="w-full h-[600px] border-0"
                    title="سند قرارداد"
                    loading="lazy">
                </iframe>

                <!-- Loading indicator -->
                <div class="absolute inset-0 flex items-center justify-center bg-gray-100"
                     x-show="false"
                     x-transition>
                    <div class="flex items-center space-x-2">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                        <span class="text-gray-600">در حال بارگذاری...</span>
                    </div>
                </div>
            </div>
        @else
            <div class="p-8 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">پیش‌نمایش سند</h3>
                <p class="mt-1 text-sm text-gray-500">
                    پیش‌نمایش برای این نوع فایل در دسترس نیست. لطفاً برای مشاهده آن را دانلود کنید.
                </p>
            </div>
        @endif
    </div>

    <!-- Document Details Section -->
    <div class="mt-6 border-t border-gray-200 pt-4">
        <h3 class="text-lg font-medium text-gray-900 mb-4">جزئیات سند</h3>

        <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
            <div class="sm:col-span-1">
                <dt class="text-sm font-medium text-gray-500 text-right">نام فایل</dt>
                <dd class="mt-1 text-sm text-gray-900 text-right">
                    {{ $contract->original_filename ?? 'نامشخص' }}
                </dd>
            </div>

            <div class="sm:col-span-1">
                <dt class="text-sm font-medium text-gray-500 text-right">حجم فایل</dt>
                <dd class="mt-1 text-sm text-gray-900 text-right">
                    @if(isset($contract->file_size))
                        {{ number_format($contract->file_size / 1024, 2) }} کیلوبایت
                    @else
                        نامشخص
                    @endif
                </dd>
            </div>

            <div class="sm:col-span-1">
                <dt class="text-sm font-medium text-gray-500 text-right">تاریخ بارگذاری</dt>
                <dd class="mt-1 text-sm text-gray-900 text-right">
                    @if(isset($contract->created_at))
                        {{ \Morilog\Jalali\Jalalian::fromDateTime($contract->created_at)->format('%d %B %Y ساعت %H:%M') }}
                    @else
                        نامشخص
                    @endif
                </dd>
            </div>

            <div class="sm:col-span-1">
                <dt class="text-sm font-medium text-gray-500 text-right">بارگذاری توسط</dt>
                <dd class="mt-1 text-sm text-gray-900 text-right">
                    {{ $contract->createdBy->name ?? 'نامشخص' }}
                </dd>
            </div>

            <div class="sm:col-span-2">
                <dt class="text-sm font-medium text-gray-500 text-right">کد امنیتی فایل (SHA-256)</dt>
                <dd class="mt-1 text-sm font-mono text-gray-900 break-all text-right" dir="ltr">
                    {{ $contract->file_hash ?? 'نامشخص' }}
                </dd>
            </div>
        </dl>
    </div>

    <!-- Upload Signed Contract Section -->
    <div class="mt-8 border-t border-gray-200 pt-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">آپلود قرارداد امضا شده</h3>

        <form wire:submit.prevent="uploadSignedContract" class="space-y-4">
            <div class="flex flex-col space-y-2">
                <label for="signed_contract" class="block text-sm font-medium text-gray-700">
                    فایل PDF امضا شده
                </label>

                <div class="mt-1 flex items-center">
                    <input type="file"
                           wire:model="signedContractFile"
                           id="signed_contract"
                           accept=".pdf,application/pdf"
                           class="flex-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>

                @error('signedContractFile')
                    <span class="text-red-500 text-xs mt-1">
                        {{ $message }}
                    </span>
                @enderror
            </div>

            <div class="flex justify-end">
                <button type="submit"
                        class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
                        wire:loading.attr="disabled"
                        wire:target="uploadSignedContract">

                    <span wire:loading.remove wire:target="uploadSignedContract">
                        آپلود قرارداد
                    </span>

                    <span wire:loading wire:target="uploadSignedContract" class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        در حال آپلود...
                    </span>
                </button>
            </div>

            <!-- Alpine.js Notification -->
            <div x-data="{ 
                     showNotification: $wire.showNotification,
                     notificationType: $wire.notificationType,
                     notificationMessage: $wire.notificationMessage 
                 }"
                 x-show="showNotification"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform translate-y-2"
                 x-transition:enter-end="opacity-100 transform translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 transform translate-y-0"
                 x-transition:leave-end="opacity-0 transform translate-y-2"
                 class="mt-3 p-3 rounded-md"
                 :class="{
                     'bg-green-50 border border-green-200': notificationType === 'success',
                     'bg-red-50 border border-red-200': notificationType === 'error'
                 }">
                 <!-- Alpine.js binding to Livewire properties -->
                 <div x-effect="showNotification = $wire.showNotification"></div>
                 <div x-effect="notificationType = $wire.notificationType"></div>
                 <div x-effect="notificationMessage = $wire.notificationMessage"></div>

                <div class="flex">
                    <div class="flex-shrink-0" x-show="notificationType === 'success'">
                        <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.707a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>

                    <div class="flex-shrink-0" x-show="notificationType === 'error'">
                        <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>

                    <div class="mr-3">
                        <p class="text-sm font-medium"
                           :class="{
                               'text-green-800': notificationType === 'success',
                               'text-red-800': notificationType === 'error'
                           }"
                           x-text="notificationMessage">
                        </p>
                    </div>

                    <div class="ml-auto pl-3">
                        <div class="-mx-1.5 -my-1.5">
                            <button @click="showNotification = false"
                                    class="inline-flex rounded-md p-1.5 focus:outline-none focus:ring-2 focus:ring-offset-2"
                                    :class="{
                                        'text-green-500 hover:bg-green-100 focus:ring-green-600': notificationType === 'success',
                                        'text-red-500 hover:bg-red-100 focus:ring-red-600': notificationType === 'error'
                                    }">
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Session-based Notifications (Fallback) -->
            @if (session()->has('signed-contract-message'))
                <div x-data="{ showNotification: $wire.showNotification }" 
                     class="mt-3 p-3 bg-green-50 border border-green-200 rounded-md"
                     x-show="!showNotification">
                     <div x-effect="showNotification = $wire.showNotification"></div>
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.707a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="mr-3">
                            <p class="text-sm font-medium text-green-800">
                                {{ session('signed-contract-message') }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            @if (session()->has('signed-contract-error'))
                <div x-data="{ showNotification: $wire.showNotification }" 
                     class="mt-3 p-3 bg-red-50 border border-red-200 rounded-md"
                     x-show="!showNotification">
                     <div x-effect="showNotification = $wire.showNotification"></div>
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="mr-3">
                            <p class="text-sm font-medium text-red-800">
                                {{ session('signed-contract-error') }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('livewire:initialized', () => {
        console.log('PDF Viewer component initialized');

        // Handle page unload
        window.addEventListener('beforeunload', () => {
            try {
                const wireElement = document.querySelector('[wire\\:id]');
                if (wireElement) {
                    const component = Livewire.find(wireElement.getAttribute('wire:id'));
                    if (component && component.get('showViewer')) {
                        component.set('showViewer', false);
                    }
                }
            } catch (error) {
                console.warn('Error during page unload:', error);
            }
        });

        // Enhanced debugging
        try {
            const wireElement = document.querySelector('[wire\\:id]');
            if (wireElement) {
                const component = Livewire.find(wireElement.getAttribute('wire:id'));
                console.log('Livewire component found:', component);
                console.log('Component state:', component?.get?.());
            }
        } catch (error) {
            console.warn('Debugging error:', error);
        }
    });

    // Global error handler for iframe loading
    window.addEventListener('error', (event) => {
        if (event.target.tagName === 'IFRAME') {
            console.error('PDF loading error:', event);
            // Could emit event to show error message
        }
    });
</script>
@endpush
