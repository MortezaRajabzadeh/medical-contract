<div dir="rtl">
    <!-- حالت در حال بارگذاری -->
    @if($loading)
        <div class="flex justify-center items-center h-64">
            <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
        </div>
    @else
        <!-- کارت‌های آمار -->
        <div class="grid grid-cols-1 gap-5 mt-6 sm:grid-cols-2 lg:grid-cols-4">
            <!-- کل قراردادها -->
            <div class="p-5 bg-white rounded-lg shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm font-medium text-gray-500 truncate text-right">کل قراردادها</div>
                        <div class="mt-1 text-3xl font-semibold text-gray-900 text-right">{{ number_format($stats['total_contracts']) }}</div>
                    </div>
                    <div class="p-3 rounded-full bg-blue-50">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- قراردادهای فعال -->
            <div class="p-5 bg-white rounded-lg shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm font-medium text-gray-500 truncate text-right">قراردادهای فعال</div>
                        <div class="mt-1 text-3xl font-semibold text-green-600 text-right">{{ number_format($stats['active_contracts']) }}</div>
                    </div>
                    <div class="p-3 rounded-full bg-green-50">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- در انتظار تایید -->
            <div class="p-5 bg-white rounded-lg shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm font-medium text-gray-500 truncate text-right">در انتظار تایید</div>
                        <div class="mt-1 text-3xl font-semibold text-yellow-600 text-right">{{ number_format($stats['pending_approval']) }}</div>
                    </div>
                    <div class="p-3 rounded-full bg-yellow-50">
                        <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- جدول قراردادها -->
            <div class="p-5 bg-white rounded-lg shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm font-medium text-gray-500 truncate text-right">در انتظار تایید</div>
                        <div class="mt-1 text-3xl font-semibold text-yellow-600 text-right">{{ number_format($stats['pending_approval']) }}</div>
                    </div>
                </div>
            </div>

            <!-- به زودی منقضی می‌شوند -->
            <div class="p-5 bg-white rounded-lg shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm font-medium text-gray-500 truncate text-right">به زودی منقضی می‌شوند</div>
                        <div class="mt-1 text-3xl font-semibold text-red-600 text-right">{{ number_format($stats['expiring_soon']) }}</div>
                    </div>
                    <div class="p-3 rounded-full bg-red-50">
                        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- نمودارها و فعالیت اخیر -->
        <div class="grid grid-cols-1 gap-6 mt-6 xl:grid-cols-3">
            <!-- نمودار توزیع وضعیت -->
            <div class="p-5 bg-white rounded-lg shadow xl:col-span-2">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="mb-1 text-lg font-medium text-gray-900 text-right">نمودار توزیع وضعیت قراردادها</h2>
                    <button wire:click="refreshData" class="flex items-center text-sm text-blue-500 hover:text-blue-700" style="margin-right: auto;">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </button>
                </div>
                <div class="h-64">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>

            <!-- فعالیت‌های اخیر -->
            <div class="p-5 bg-white rounded-lg shadow">
                <h2 class="mb-4 text-lg font-medium text-gray-900 text-right">فعالیت‌های اخیر</h2>
                <div class="flow-root">
                    <ul class="-mb-8">
                        @forelse($recentContracts as $contract)
                            <li>
                                <div class="relative pb-8">
                                    @if(!$loop->last)
                                        <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                    @endif
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white">
                                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z" clip-rule="evenodd" />
                                                </svg>
                                            </span>
                                        </div>
                                        <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                            <div>
                                                <p class="text-sm text-gray-500">
                                                    <span class="font-medium text-gray-900">{{ $contract->createdBy->name ?? 'System' }}</span>
                                                    created a new contract
                                                </p>
                                                <p class="text-sm text-gray-500">{{ $contract->created_at->diffForHumans() }}</p>
                                            </div>
                                            <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    {{ ucfirst($contract->status) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @empty
                            <li class="text-center text-gray-500 py-4">فعالیت اخیری یافت نشد</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        <!-- جدول قراردادهای نزدیک به انقضا -->
        <div class="mt-6">
            <div class="p-5 bg-white rounded-lg shadow">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-medium text-gray-900 text-right">قراردادهای نزدیک به انقضا</h2>
                    <a href="{{ route('contracts.index', ['filter' => 'expiring_soon']) }}" class="text-sm font-medium text-blue-600 hover:text-blue-500">
                        مشاهده همه
                    </a>
                </div>
                <div class="flex flex-col">
                    <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                        <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                            <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                قرارداد
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                نوع
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                تاریخ انقضا
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                روزهای باقی‌مانده
                                            </th>
                                            <th scope="col" class="relative px-6 py-3">
                                                <span class="sr-only">عملیات</span>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @forelse($expiringContracts as $contract)
                                            @php
                                                $daysLeft = now()->diffInDays($contract->end_date, false);
                                                $isExpired = $daysLeft < 0;
                                            @endphp
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-gray-900">{{ $contract->title }}</div>
                                                    <div class="text-sm text-gray-500">#{{ $contract->contract_number }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">{{ $contract->vendor_name }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-900">{{ $contract->end_date->format('M d, Y') }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $isExpired ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                        {{ $isExpired ? abs($daysLeft) . ' روز پیش منقضی شده' : $daysLeft . ' روز باقی‌مانده' }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                    <a href="{{ route('contracts.show', $contract) }}" class="text-blue-600 hover:text-blue-900">مشاهده</a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                                                    هیچ قرارداد نزدیک به انقضایی وجود ندارد
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('livewire:load', function () {
                const ctx = document.getElementById('statusChart').getContext('2d');
                const chart = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: @json($chartData['labels']),
                        datasets: @json($chartData['datasets'])
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'right',
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const label = context.label || '';
                                        const value = context.raw || 0;
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = Math.round((value / total) * 100);
                                        return `${label}: ${value} (${percentage}%)`;
                                    }
                                }
                            }
                        }
                    }
                });

                // Refresh chart when Livewire updates
                Livewire.hook('message.processed', (message, component) => {
                    if (component.fingerprint.name === 'dashboard.medical-center-dashboard') {
                        chart.data.labels = @this.chartData.labels;
                        chart.data.datasets = @this.chartData.datasets;
                        chart.update();
                    }
                });
            });
        </script>
        @endpush
    @endif
</div>
