@extends('admin.layouts.app')

@section('title', 'داشبورد مدیریت')

@section('page-title', 'داشبورد مدیریت')

@section('content')
    <!-- کارت‌های آمار -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-right-primary h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                مراکز درمانی</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['medicalCenters'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-hospital fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer py-1 px-3">
                    <a href="{{ route('admin.medical-centers.index') }}" class="text-primary small">مشاهده لیست <i class="fas fa-chevron-left"></i></a>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-right-success h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                قراردادهای فعال</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['contracts']['active'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-contract fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer py-1 px-3">
                    <a href="{{ route('admin.contracts.index') }}" class="text-success small">مشاهده لیست <i class="fas fa-chevron-left"></i></a>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-right-warning h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                قراردادهای در انتظار</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['contracts']['pending'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer py-1 px-3">
                    <a href="{{ route('admin.contracts.index') }}?status=pending" class="text-warning small">مشاهده لیست <i class="fas fa-chevron-left"></i></a>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-right-danger h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                قراردادهای منقضی</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['contracts']['expired'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-times fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer py-1 px-3">
                    <a href="{{ route('admin.contracts.index') }}?status=expired" class="text-danger small">مشاهده لیست <i class="fas fa-chevron-left"></i></a>
                </div>
            </div>
        </div>
    </div>

    <!-- ردیف محتوای اصلی -->
    <div class="row">
        <!-- ستون سمت راست -->
        <div class="col-lg-8 mb-4">
            <!-- قراردادهای اخیر -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold">قراردادهای اخیر</h6>
                    <a href="{{ route('admin.contracts.index') }}" class="btn btn-sm btn-primary">مشاهده همه</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>شماره قرارداد</th>
                                    <th>مرکز درمانی</th>
                                    <th>تاریخ شروع</th>
                                    <th>تاریخ پایان</th>
                                    <th>وضعیت</th>
                                    <th>عملیات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($stats['recentContracts'] ?? [] as $contract)
                                    <tr>
                                        <td>{{ $contract->contract_number }}</td>
                                        <td>{{ $contract->medicalCenter->name }}</td>
                                        <td>{{ \Morilog\Jalali\Jalalian::fromDateTime($contract->start_date)->format('Y/m/d') }}</td>
                                        <td>{{ \Morilog\Jalali\Jalalian::fromDateTime($contract->end_date)->format('Y/m/d') }}</td>
                                        <td>
                                            @if($contract->status === 'active')
                                                <span class="badge bg-success">فعال</span>
                                            @elseif($contract->status === 'expired')
                                                <span class="badge bg-danger">منقضی شده</span>
                                            @elseif($contract->status === 'pending')
                                                <span class="badge bg-warning">در انتظار امضا</span>
                                            @else
                                                <span class="badge bg-secondary">{{ $contract->status }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.contracts.show', $contract) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-3">هیچ قراردادی یافت نشد!</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- ستون سمت چپ -->
        <div class="col-lg-4 mb-4">
            <!-- وضعیت سیستم -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold">وضعیت سیستم</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-primary">مراکز درمانی:</span>
                        <span class="text-dark">{{ $stats['medicalCenters'] ?? 0 }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-primary">کل قراردادها:</span>
                        <span class="text-dark">{{ $stats['contracts']['total'] ?? 0 }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-primary">کاربران:</span>
                        <span class="text-dark">{{ $stats['users'] ?? 0 }}</span>
                    </div>
                    <hr>
                    <div class="d-flex flex-column">
                        <div class="mb-2">
                            <span class="fw-bold">وضعیت قراردادها:</span>
                        </div>
                        <div class="progress mb-1" style="height: 15px;">
                            @php
                                $total = $stats['contracts']['total'] ?? 1; // جلوگیری از تقسیم بر صفر
                                $activePercent = ($stats['contracts']['active'] ?? 0) / $total * 100;
                                $pendingPercent = ($stats['contracts']['pending'] ?? 0) / $total * 100;
                                $expiredPercent = ($stats['contracts']['expired'] ?? 0) / $total * 100;
                            @endphp
                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $activePercent }}%" title="فعال: {{ $stats['contracts']['active'] ?? 0 }}"></div>
                            <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $pendingPercent }}%" title="در انتظار: {{ $stats['contracts']['pending'] ?? 0 }}"></div>
                            <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $expiredPercent }}%" title="منقضی: {{ $stats['contracts']['expired'] ?? 0 }}"></div>
                        </div>
                        <div class="d-flex justify-content-between small mt-2">
                            <span class="text-success">{{ $stats['contracts']['active'] ?? 0 }} فعال</span>
                            <span class="text-warning">{{ $stats['contracts']['pending'] ?? 0 }} در انتظار</span>
                            <span class="text-danger">{{ $stats['contracts']['expired'] ?? 0 }} منقضی</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- دسترسی سریع -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold">دسترسی سریع</h6>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <a href="{{ route('admin.medical-centers.create') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-plus-circle ml-2 text-success"></i> افزودن مرکز درمانی</span>
                            <i class="fas fa-chevron-left"></i>
                        </a>
                        <a href="{{ route('admin.contracts.create') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-plus-circle ml-2 text-success"></i> افزودن قرارداد جدید</span>
                            <i class="fas fa-chevron-left"></i>
                        </a>
                        <a href="{{ route('admin.users.create') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-plus-circle ml-2 text-success"></i> افزودن کاربر جدید</span>
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('styles')
<style>
    .border-right-primary {
        border-right: 0.25rem solid #4e73df !important;
    }
    
    .border-right-success {
        border-right: 0.25rem solid #1cc88a !important;
    }
    
    .border-right-warning {
        border-right: 0.25rem solid #f6c23e !important;
    }
    
    .border-right-danger {
        border-right: 0.25rem solid #e74a3b !important;
    }
</style>
@endsection
