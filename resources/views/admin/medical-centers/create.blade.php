@extends('admin.layouts.app')

@section('title', 'افزودن مرکز درمانی جدید')

@section('page-title', 'افزودن مرکز درمانی جدید')

@section('page-actions')
    <a href="{{ route('admin.medical-centers.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-right ml-1"></i> بازگشت به لیست
    </a>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">اطلاعات مرکز درمانی</h5>
        </div>
        
        <div class="card-body">
            <form action="{{ route('admin.medical-centers.store') }}" method="POST" id="create-medical-center-form">
                @csrf
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label required">نام مرکز</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="center_id" class="form-label required">کد مرکز</label>
                        <input type="text" id="center_id" name="center_id" value="{{ old('center_id') }}" class="form-control @error('center_id') is-invalid @enderror" required>
                        @error('center_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="phone" class="form-label required">شماره تلفن</label>
                        <input type="text" id="phone" name="phone" value="{{ old('phone') }}" class="form-control @error('phone') is-invalid @enderror" required>
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">ایمیل</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="manager" class="form-label">مدیر مرکز</label>
                        <input type="text" id="manager" name="manager" value="{{ old('manager') }}" class="form-control @error('manager') is-invalid @enderror">
                        @error('manager')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-12 mb-3">
                        <label for="address" class="form-label">آدرس</label>
                        <textarea id="address" name="address" rows="3" class="form-control @error('address') is-invalid @enderror">{{ old('address') }}</textarea>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="d-flex justify-content-end mt-4">
                    <button type="reset" class="btn btn-light ml-2">پاک کردن</button>
                    <button type="submit" class="btn btn-primary">ذخیره</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    // اعتبارسنجی سمت کلاینت فرم
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('create-medical-center-form');
        
        form.addEventListener('submit', function(event) {
            let hasError = false;
            const requiredFields = form.querySelectorAll('[required]');
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    hasError = true;
                } else {
                    field.classList.remove('is-invalid');
                }
            });
            
            // اعتبارسنجی ایمیل
            const emailField = form.querySelector('#email');
            if (emailField.value.trim() && !isValidEmail(emailField.value.trim())) {
                emailField.classList.add('is-invalid');
                hasError = true;
            }
            
            if (hasError) {
                event.preventDefault();
                alert('لطفاً تمام فیلدهای الزامی را به درستی پر کنید.');
            }
        });
        
        // تابع بررسی فرمت ایمیل
        function isValidEmail(email) {
            const re = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            return re.test(email);
        }
    });
</script>
@endsection
