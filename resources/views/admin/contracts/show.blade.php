@extends('admin.layouts.app')

@section('title', 'جزئیات قرارداد')

@section('page-title', 'جزئیات قرارداد')

@section('page-actions')
    <div class="action-buttons-container d-flex flex-wrap justify-content-end mb-3">
        <a href="{{ route('admin.contracts.index') }}" class="btn btn-secondary mx-1 mb-2">
            <i class="fas fa-arrow-right ml-1"></i> بازگشت به لیست
        </a>
        <a href="{{ route('admin.contracts.edit', $contract) }}" class="btn btn-warning mx-1 mb-2">
            <i class="fas fa-edit ml-1"></i> ویرایش
        </a>
        
        <!-- دکمه‌های دانلود فایل قرارداد -->
        @if($contract->file_path)
        <a href="{{ route('admin.contracts.download', $contract) }}" class="btn btn-info mx-1 mb-2">
            <i class="fas fa-download ml-1"></i> دانلود قرارداد
        </a>
        @endif
        
        @if($contract->signed_file_path)
        <a href="{{ route('admin.contracts.download-signed', $contract) }}" class="btn btn-success mx-1 mb-2">
            <i class="fas fa-file-signature ml-1"></i> دانلود امضا شده
        </a>
        @endif
        
        <!-- دکمه تغییر وضعیت -->
        <button type="button" class="btn btn-primary mx-1 mb-2" data-bs-toggle="modal" data-bs-target="#changeStatusModal">
            <i class="fas fa-exchange-alt ml-1"></i> تغییر وضعیت
        </button>
        
        <form action="{{ route('admin.contracts.destroy', $contract) }}" method="POST" class="mx-1 mb-2" id="delete-contract-form">
            @csrf
            @method('DELETE')
            <button type="button" class="btn btn-danger" id="delete-btn">
                <i class="fas fa-trash ml-1"></i> حذف
            </button>
        </form>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">اطلاعات اصلی قرارداد</h5>
                </div>
                
                <div class="card-body">
                    <div class="mb-4">
                        <h4>{{ $contract->title }}</h4>
                        <div class="small text-muted">شماره قرارداد: {{ $contract->contract_number }}</div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label class="fw-bold">مرکز درمانی:</label>
                                <div>{{ $contract->medicalCenter->name ?? 'نامشخص' }}</div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label class="fw-bold">وضعیت:</label>
                                <div>
                                    @if($contract->status === 'draft')
                                        <span class="badge bg-secondary">پیش‌نویس</span>
                                    @elseif($contract->status === 'pending')
                                        <span class="badge bg-warning">در انتظار امضا</span>
                                    @elseif($contract->status === 'uploaded')
                                        <span class="badge bg-info">بارگزاری شده</span>
                                    @elseif($contract->status === 'under_review')
                                        <span class="badge bg-indigo">در دست بررسی</span>
                                    @elseif($contract->status === 'approved')
                                        <span class="badge bg-success">تایید نهایی</span>
                                    @elseif($contract->status === 'rejected')
                                        <span class="badge bg-danger">رد شده</span>
                                    @elseif($contract->status === 'active')
                                        <span class="badge bg-success">فعال</span>
                                    @elseif($contract->status === 'expired')
                                        <span class="badge bg-danger">منقضی شده</span>
                                    @elseif($contract->status === 'terminated')
                                        <span class="badge bg-dark">خاتمه یافته</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $contract->status }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label class="fw-bold">تاریخ شروع:</label>
                                <div>{{ \Morilog\Jalali\Jalalian::fromDateTime($contract->start_date)->format('Y/m/d') }}</div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label class="fw-bold">تاریخ پایان:</label>
                                <div>{{ \Morilog\Jalali\Jalalian::fromDateTime($contract->end_date)->format('Y/m/d') }}</div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label class="fw-bold">تاریخ امضا:</label>
                                <div>
                                    @if($contract->signed_date)
                                        {{ \Morilog\Jalali\Jalalian::fromDateTime($contract->signed_date)->format('Y/m/d') }}
                                    @else
                                        <span class="text-muted">ثبت نشده</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label class="fw-bold">مبلغ قرارداد:</label>
                                <div>{{ number_format($contract->amount) }} ریال</div>
                            </div>
                        </div>
                    </div>
                    
                    @if($contract->description)
                        <div class="mt-3 mb-3">
                            <label class="fw-bold">توضیحات:</label>
                            <div class="p-3 bg-light rounded">{{ $contract->description }}</div>
                        </div>
                    @endif
                    
                    <!-- نمایش اطلاعات فایل‌های قرارداد -->
                    @if($contract->file_path)
                        <div class="mt-4 mb-3">
                            <label class="fw-bold">فایل قرارداد:</label>
                            <div class="d-flex align-items-center mt-2">
                                <div class="me-3">
                                    <a href="{{ asset('storage/' . $contract->file_path) }}" target="_blank" class="btn btn-outline-primary">
                                        <i class="fas fa-file-alt ml-1"></i> مشاهده فایل قرارداد
                                    </a>
                                </div>
                                <div>
                                    <a href="{{ route('admin.contracts.download', $contract) }}" class="btn btn-outline-info">
                                        <i class="fas fa-download ml-1"></i> دانلود فایل
                                    </a>
                                </div>
                            </div>
                            <div class="small text-muted mt-1">
                                <i class="fas fa-info-circle ml-1"></i>
                                نام فایل: {{ basename($contract->file_path) }}
                            </div>
                        </div>
                    @else
                        <div class="alert alert-warning mt-4">
                            <i class="fas fa-exclamation-triangle ml-1"></i>
                            فایلی برای این قرارداد آپلود نشده است.
                        </div>
                    @endif
                    
                    <!-- گزارش روند فایل قرارداد -->
                    <div class="mt-5 mb-3">
                        <h5 class="border-bottom pb-2">تاریخچه روند قرارداد</h5>
                        <div class="timeline mt-3">
                            <div class="timeline-item">
                                <div class="timeline-marker bg-primary"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">ایجاد قرارداد</h6>
                                    <div>توسط: {{ $contract->createdBy->name ?? 'نامشخص' }}</div>
                                    <div class="text-muted small">{{ \Morilog\Jalali\Jalalian::fromDateTime($contract->created_at)->format('Y/m/d H:i:s') }}</div>
                                </div>
                            </div>
                            
                            @if($contract->file_path)
                            <div class="timeline-item">
                                <div class="timeline-marker bg-info"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">آپلود فایل قرارداد</h6>
                                    <div>نام فایل: {{ basename($contract->file_path) }}</div>
                                    @if($contract->file_size)
                                        <div>حجم فایل: {{ number_format($contract->file_size / 1024, 2) }} کیلوبایت</div>
                                    @endif
                                    <div class="text-muted small">{{ \Morilog\Jalali\Jalalian::fromDateTime($contract->updated_at)->format('Y/m/d H:i:s') }}</div>
                                </div>
                            </div>
                            @endif
                            
                            @if($contract->status === 'pending')
                            <div class="timeline-item">
                                <div class="timeline-marker bg-warning"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">در انتظار امضا</h6>
                                    <div>قرارداد در انتظار امضا توسط مرکز درمانی است</div>
                                    <div class="text-muted small">{{ \Morilog\Jalali\Jalalian::fromDateTime($contract->updated_at)->format('Y/m/d H:i:s') }}</div>
                                </div>
                            </div>
                            @endif
                            
                            @if($contract->status === 'active' || $contract->status === 'approved')
                            <div class="timeline-item">
                                <div class="timeline-marker bg-success"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">امضا و تأیید قرارداد</h6>
                                    <div>امضا کننده: {{ $contract->approvedBy->name ?? 'نامشخص' }}</div>
                                    <div class="text-muted small">{{ \Morilog\Jalali\Jalalian::fromDateTime($contract->approved_at ?? $contract->updated_at)->format('Y/m/d H:i:s') }}</div>
                                </div>
                            </div>
                            @endif
                            
                            @if($contract->status === 'expired')
                            <div class="timeline-item">
                                <div class="timeline-marker bg-danger"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">انقضای قرارداد</h6>
                                    <div>قرارداد در تاریخ {{ \Morilog\Jalali\Jalalian::fromDateTime($contract->end_date)->format('Y/m/d') }} منقضی شده است</div>
                                </div>
                            </div>
                            @endif
                            
                            @if($contract->status === 'terminated')
                            <div class="timeline-item">
                                <div class="timeline-marker bg-dark"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">خاتمه قرارداد</h6>
                                    <div>قرارداد پیش از موعد خاتمه یافته است</div>
                                    <div class="text-muted small">{{ \Morilog\Jalali\Jalalian::fromDateTime($contract->updated_at)->format('Y/m/d H:i:s') }}</div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                    <div class="mt-4">
                        <h5>فایل‌های قرارداد</h5>
                        <hr>
                        
                        @if($contract->file_path)
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center border p-2 rounded">
                                    <div>
                                        <i class="fas fa-file-contract fa-lg text-primary ml-2"></i>
                                        <span>{{ $contract->original_filename ?: 'فایل قرارداد' }}</span>
                                        <span class="text-muted small mx-2">({{ round($contract->file_size / 1024 / 1024, 2) }} MB)</span>
                                    </div>
                                    <a href="{{ route('admin.contracts.download', $contract) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-download"></i> دانلود
                                    </a>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle ml-1"></i>
                                فایل قرارداد آپلود نشده است.
                            </div>
                        @endif
                        
                        @if($contract->signed_file_path)
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center border p-2 rounded">
                                    <div>
                                        <i class="fas fa-file-signature fa-lg text-success ml-2"></i>
                                        <span>قرارداد امضا شده</span>
                                    </div>
                                    <a href="{{ route('admin.contracts.download-signed', $contract) }}" class="btn btn-sm btn-outline-success">
                                        <i class="fas fa-download"></i> دانلود
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">اطلاعات اضافی</h5>
                </div>
                
                <div class="card-body">
                    <div class="mb-3">
                        <label class="fw-bold">تاریخ ایجاد:</label>
                        <div>{{ \Morilog\Jalali\Jalalian::fromDateTime($contract->created_at)->format('Y/m/d H:i:s') }}</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="fw-bold">آخرین بروزرسانی:</label>
                        <div>{{ \Morilog\Jalali\Jalalian::fromDateTime($contract->updated_at)->format('Y/m/d H:i:s') }}</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="fw-bold">وضعیت زمانی:</label>
                        <div>
                            @php
                                $now = now();
                                $startDate = \Carbon\Carbon::parse($contract->start_date);
                                $endDate = \Carbon\Carbon::parse($contract->end_date);
                                $remainingDays = $now->diffInDays($endDate, false);
                            @endphp
                            
                            @if($now < $startDate)
                                <span class="badge bg-info">هنوز شروع نشده</span>
                                <div class="small mt-1">{{ $now->diffInDays($startDate) }} روز تا شروع</div>
                            @elseif($now > $endDate)
                                <span class="badge bg-danger">منقضی شده</span>
                                <div class="small mt-1">{{ $now->diffInDays($endDate) }} روز از انقضا گذشته</div>
                            @else
                                <span class="badge bg-success">در جریان</span>
                                <div class="small mt-1">{{ $remainingDays }} روز تا انقضا</div>
                                
                                @if($remainingDays <= 30)
                                    <div class="alert alert-warning mt-2 p-2 small">
                                        <i class="fas fa-exclamation-triangle ml-1"></i>
                                        این قرارداد به زودی منقضی می‌شود!
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                    
                    <!-- نمایش تاریخچه بازدیدها -->
                    <div class="mb-3">
                        <label class="fw-bold">وضعیت بازدید:</label>
                        <div>
                            @if($contract->viewedBy && $contract->viewedBy->count() > 0)
                                <span class="badge bg-info">{{ $contract->viewedBy->count() }} بار مشاهده شده</span>
                                <div class="small mt-2">
                                    <strong>آخرین بازدید:</strong>
                                    {{ \Morilog\Jalali\Jalalian::fromDateTime($contract->viewedBy->sortByDesc('pivot.viewed_at')->first()->pivot->viewed_at)->format('Y/m/d H:i') }}
                                </div>
                            @else
                                <span class="badge bg-secondary">مشاهده نشده</span>
                            @endif
                        </div>
                    </div>
                    
                    <!-- نمایش اطلاعات تأیید -->
                    @if($contract->approved_by)
                    <div class="mb-3">
                        <label class="fw-bold">تأیید کننده:</label>
                        <div>
                            {{ $contract->approvedBy->name ?? 'نامشخص' }}
                            <div class="small text-muted">
                                {{ \Morilog\Jalali\Jalalian::fromDateTime($contract->approved_at)->format('Y/m/d H:i:s') }}
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    // کد مربوط به تایید حذف قرارداد
    document.addEventListener('DOMContentLoaded', function() {
        const deleteBtn = document.getElementById('delete-btn');
        const deleteForm = document.getElementById('delete-contract-form');
        
        deleteBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            if (confirm('آیا از حذف این قرارداد اطمینان دارید؟ این عملیات قابل بازگشت نیست.')) {
                deleteForm.submit();
            }
        });
    });
</script>
@endsection

<!-- مودال تغییر وضعیت قرارداد -->
<div class="modal fade" id="changeStatusModal" tabindex="-1" aria-labelledby="changeStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changeStatusModalLabel">تغییر وضعیت قرارداد</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.contracts.change-status', $contract) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="status" class="form-label">وضعیت جدید:</label>
                        <select id="status" name="status" class="form-select" required>
                            <option value="">انتخاب کنید...</option>
                            @if($contract->status !== 'draft')<option value="draft">پیش‌نویس</option>@endif
                            @if($contract->status !== 'pending')<option value="pending">در انتظار امضا</option>@endif
                            @if($contract->status !== 'uploaded')<option value="uploaded">بارگزاری شده</option>@endif
                            @if($contract->status !== 'approved')<option value="approved">تایید نهایی</option>@endif
                            @if($contract->status !== 'rejected')<option value="rejected">رد شده</option>@endif
                            @if($contract->status !== 'active')<option value="active">فعال</option>@endif
                            @if($contract->status !== 'expired')<option value="expired">منقضی شده</option>@endif
                            @if($contract->status !== 'terminated')<option value="terminated">خاتمه یافته</option>@endif
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">توضیحات تغییر وضعیت:</label>
                        <textarea id="notes" name="notes" class="form-control" rows="3"></textarea>
                        <div class="form-text">توضیحات اختیاری در مورد دلیل تغییر وضعیت</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" class="btn btn-primary">اعمال تغییر وضعیت</button>
                </div>
            </form>
        </div>
    </div>
</div>
