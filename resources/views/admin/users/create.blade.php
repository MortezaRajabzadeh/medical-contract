@extends('admin.layouts.app')

@section('title', 'افزودن کاربر جدید')

@section('page-title', 'افزودن کاربر جدید')

@section('page-actions')
    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-right ml-1"></i> بازگشت به لیست
    </a>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">اطلاعات کاربر</h5>
        </div>
        
        <div class="card-body">
            <form action="{{ route('admin.users.store') }}" method="POST" id="create-user-form">
                @csrf
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label required">نام و نام خانوادگی</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label required">آدرس ایمیل</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="mobile" class="form-label">شماره موبایل</label>
                        <input type="text" id="mobile" name="mobile" value="{{ old('mobile') }}" class="form-control @error('mobile') is-invalid @enderror" dir="ltr">
                        @error('mobile')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">فرمت: 09123456789</small>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="user_type" class="form-label required">نقش کاربر</label>
                        <select id="user_type" name="user_type" class="form-select @error('user_type') is-invalid @enderror" required>
                            <option value="">انتخاب کنید...</option>
                            <option value="admin" {{ old('user_type') == 'admin' ? 'selected' : '' }}>مدیر سیستم</option>
                            <option value="medical_staff" {{ old('user_type') == 'medical_staff' ? 'selected' : '' }}>کادر پزشکی</option>
                            <option value="administrative_staff" {{ old('user_type') == 'administrative_staff' ? 'selected' : '' }}>کادر اداری</option>
                        </select>
                        @error('user_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label required">رمز عبور</label>
                        <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">حداقل 8 کاراکتر شامل حروف بزرگ و کوچک و اعداد</small>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="password_confirmation" class="form-label required">تکرار رمز عبور</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required>
                    </div>
                    
                    <div class="col-md-12 mb-3">
                        <label class="form-label required">وضعیت کاربر</label>
                        <div class="d-flex">
                            <div class="form-check ml-4">
                                <input class="form-check-input" type="radio" name="is_active" id="is_active_1" value="1" {{ old('is_active', '1') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active_1">فعال</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="is_active" id="is_active_0" value="0" {{ old('is_active') == '0' ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active_0">غیرفعال</label>
                            </div>
                        </div>
                        @error('is_active')
                            <div class="text-danger mt-1">{{ $message }}</div>
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

@section('styles')
<style>
    .required:after {
        content: " *";
        color: red;
    }
</style>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // اعتبارسنجی سمت کلاینت فرم
        const form = document.getElementById('create-user-form');
        
        form.addEventListener('submit', function(e) {
            let hasError = false;
            const requiredFields = form.querySelectorAll('[required]');
            
            // بررسی فیلدهای الزامی
            requiredFields.forEach(function(field) {
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    hasError = true;
                } else {
                    field.classList.remove('is-invalid');
                }
            });
            
            // بررسی فرمت ایمیل
            const emailField = document.getElementById('email');
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (emailField.value.trim() && !emailRegex.test(emailField.value)) {
                emailField.classList.add('is-invalid');
                hasError = true;
            }
            
            // بررسی تطابق رمز عبور
            const password = document.getElementById('password');
            const passwordConfirm = document.getElementById('password_confirmation');
            
            if (password.value !== passwordConfirm.value) {
                password.classList.add('is-invalid');
                passwordConfirm.classList.add('is-invalid');
                hasError = true;
            }
            
            // بررسی پیچیدگی رمز عبور
            if (password.value) {
                // حداقل 8 کاراکتر، حداقل یک حرف کوچک، یک حرف بزرگ و یک عدد
                const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/;
                
                if (!passwordRegex.test(password.value)) {
                    password.classList.add('is-invalid');
                    hasError = true;
                }
            }
            
            if (hasError) {
                e.preventDefault();
                alert('لطفاً خطاهای فرم را برطرف کنید.');
            }
        });
        
        // پاک کردن خطاها با تغییر مقدار فیلدها
        form.querySelectorAll('input, select').forEach(function(field) {
            field.addEventListener('change', function() {
                this.classList.remove('is-invalid');
            });
        });
    });
</script>
@endsection
