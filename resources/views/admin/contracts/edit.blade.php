@extends('admin.layouts.app')

@section('title', 'ویرایش قرارداد')

@section('page-title', 'ویرایش قرارداد')

@section('page-actions')
    <div class="btn-group">
        <a href="{{ route('admin.contracts.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-right ml-1"></i> بازگشت به لیست
        </a>
        <a href="{{ route('admin.contracts.show', $contract) }}" class="btn btn-info">
            <i class="fas fa-eye ml-1"></i> نمایش جزئیات
        </a>
    </div>
@endsection

@section('styles')
    <!-- افزودن استایل‌های مربوط به انتخاب‌گر تاریخ -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/persian-datepicker@1.2.0/dist/css/persian-datepicker.min.css">
    <style>
        .datepicker-plot-area {
            font-family: 'Vazirmatn', Tahoma, Arial, sans-serif;
        }
        
        .required:after {
            content: " *";
            color: red;
        }
    </style>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">ویرایش اطلاعات قرارداد</h5>
        </div>
        
        <div class="card-body">
            <form action="{{ route('admin.contracts.update', $contract) }}" method="POST" id="edit-contract-form" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="medical_center_id" class="form-label required">مرکز درمانی</label>
                        <select id="medical_center_id" name="medical_center_id" class="form-select @error('medical_center_id') is-invalid @enderror" required>
                            <option value="">انتخاب کنید...</option>
                            @foreach ($medicalCenters as $id => $name)
                                <option value="{{ $id }}" {{ (old('medical_center_id', $contract->medical_center_id) == $id) ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                        @error('medical_center_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="contract_number" class="form-label required">شماره قرارداد</label>
                        <input type="text" id="contract_number" name="contract_number" value="{{ old('contract_number', $contract->contract_number) }}" class="form-control @error('contract_number') is-invalid @enderror" required>
                        @error('contract_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-12 mb-3">
                        <label for="title" class="form-label required">عنوان قرارداد</label>
                        <input type="text" id="title" name="title" value="{{ old('title', $contract->title) }}" class="form-control @error('title') is-invalid @enderror" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label for="start_date" class="form-label required">تاریخ شروع</label>
                        <input type="text" id="start_date" name="start_date" value="{{ old('start_date', \Morilog\Jalali\Jalalian::fromDateTime($contract->start_date)->format('Y/m/d')) }}" class="form-control datepicker @error('start_date') is-invalid @enderror" required>
                        <input type="hidden" id="start_date_gregorian" name="start_date_gregorian" value="{{ $contract->start_date->format('Y-m-d') }}">
                        @error('start_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label for="end_date" class="form-label required">تاریخ پایان</label>
                        <input type="text" id="end_date" name="end_date" value="{{ old('end_date', \Morilog\Jalali\Jalalian::fromDateTime($contract->end_date)->format('Y/m/d')) }}" class="form-control datepicker @error('end_date') is-invalid @enderror" required>
                        <input type="hidden" id="end_date_gregorian" name="end_date_gregorian" value="{{ $contract->end_date->format('Y-m-d') }}">
                        @error('end_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label for="signed_date" class="form-label">تاریخ امضا</label>
                        <input type="text" id="signed_date" name="signed_date" value="{{ old('signed_date', $contract->signed_date ? \Morilog\Jalali\Jalalian::fromDateTime($contract->signed_date)->format('Y/m/d') : '') }}" class="form-control datepicker @error('signed_date') is-invalid @enderror">
                        <input type="hidden" id="signed_date_gregorian" name="signed_date_gregorian" value="{{ $contract->signed_date ? $contract->signed_date->format('Y-m-d') : '' }}">
                        @error('signed_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="amount" class="form-label required">مبلغ قرارداد (ریال)</label>
                        <input type="number" id="amount" name="amount" value="{{ old('amount', $contract->amount) }}" class="form-control @error('amount') is-invalid @enderror" required min="0">
                        @error('amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="status" class="form-label required">وضعیت</label>
                        <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="">انتخاب کنید...</option>
                            <option value="active" {{ old('status', $contract->status) == 'active' ? 'selected' : '' }}>فعال</option>
                            <option value="pending" {{ old('status', $contract->status) == 'pending' ? 'selected' : '' }}>در انتظار امضا</option>
                            <option value="expired" {{ old('status', $contract->status) == 'expired' ? 'selected' : '' }}>منقضی شده</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-12 mb-3">
                        <label for="description" class="form-label">توضیحات</label>
                        <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror" rows="5">{{ old('description', $contract->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-12 mb-3">
                        <label for="contract_file" class="form-label">فایل قرارداد</label>
                        <input type="file" id="contract_file" name="contract_file" class="form-control @error('contract_file') is-invalid @enderror">
                        <div class="form-text text-muted">فرمت‌های مجاز: PDF، Word، Excel، Image - حداکثر حجم: ۱۰ مگابایت</div>
                        @error('contract_file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        
                        @if($contract->file_path)
                        <div class="mt-2">
                            <div class="d-flex align-items-center">
                                <strong class="ml-2">فایل فعلی:</strong>
                                <a href="{{ asset('storage/' . $contract->file_path) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-file ml-1"></i> مشاهده فایل قرارداد
                                </a>
                            </div>
                            <span class="text-muted">برای تغییر فایل، یک فایل جدید انتخاب کنید.</span>
                        </div>
                        @endif
                    </div>
                </div>
                
                <div class="d-flex justify-content-end mt-4">
                    <a href="{{ route('admin.contracts.index') }}" class="btn btn-light ml-2">انصراف</a>
                    <button type="submit" class="btn btn-primary">ذخیره تغییرات</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- اسکریپت‌های مربوط به انتخاب‌گر تاریخ -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/persian-datepicker@1.2.0/dist/js/persian-datepicker.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/persian-date@1.1.0/dist/persian-date.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // تنظیمات مشترک انتخاب‌گر تاریخ
            const datePickerOptions = {
                format: 'YYYY/MM/DD',
                autoClose: true,
                initialValueType: 'persian',
                persianDigit: false,
                observer: true,
                calendar: {
                    persian: {
                        locale: 'fa'
                    }
                },
                onSelect: function(unixDate) {
                    // تبدیل تاریخ شمسی به میلادی برای ذخیره در دیتابیس
                    const pdate = new persianDate(unixDate);
                    const gdate = pdate.toCalendar('gregorian').format('YYYY-MM-DD');
                    
                    // ذخیره تاریخ میلادی در فیلد مخفی مربوطه
                    const inputId = $(this.model.inputElement).attr('id');
                    $(`#${inputId}_gregorian`).val(gdate);
                }
            };
            
            // اعمال انتخاب‌گر تاریخ به همه فیلدهای با کلاس datepicker
            $('.datepicker').each(function() {
                $(this).pDatepicker(datePickerOptions);
            });
            
            // اعتبارسنجی سمت کلاینت فرم
            $('#edit-contract-form').on('submit', function(e) {
                let hasError = false;
                const requiredFields = $(this).find('[required]');
                
                // بررسی فیلدهای الزامی
                requiredFields.each(function() {
                    if (!$(this).val().trim()) {
                        $(this).addClass('is-invalid');
                        hasError = true;
                    } else {
                        $(this).removeClass('is-invalid');
                    }
                });
                
                // بررسی تاریخ پایان بعد از تاریخ شروع
                const startDateG = $('#start_date_gregorian').val();
                const endDateG = $('#end_date_gregorian').val();
                
                if (startDateG && endDateG && startDateG > endDateG) {
                    $('#end_date').addClass('is-invalid');
                    alert('تاریخ پایان باید بعد از تاریخ شروع باشد.');
                    hasError = true;
                }
                
                // کد اعتبارسنجی مبلغ قرارداد حذف شد
                
                if (hasError) {
                    e.preventDefault();
                    alert('لطفاً خطاهای فرم را برطرف کنید.');
                }
            });
            
            // پاک کردن خطاها با تغییر مقدار فیلدها
            $('input, select, textarea').on('change', function() {
                $(this).removeClass('is-invalid');
            });
        });
    </script>
@endsection
