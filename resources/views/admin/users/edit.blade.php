@extends('admin.layouts.app')

@section('title', 'ویرایش کاربر')

@section('page-title', 'ویرایش کاربر')

@section('page-actions')
    <div class="btn-group">
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-right ml-1"></i> بازگشت به لیست
        </a>
        <a href="{{ route('admin.users.show', $user) }}" class="btn btn-info">
            <i class="fas fa-eye ml-1"></i> نمایش جزئیات
        </a>
    </div>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">ویرایش اطلاعات کاربر</h5>
        </div>
        
        <div class="card-body">
            <form action="{{ route('admin.users.update', $user) }}" method="POST" id="edit-user-form">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label required">نام و نام خانوادگی</label>
                        <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" class="form-control @error('name') is-invalid @enderror" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label required">آدرس ایمیل</label>
                        <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" class="form-control @error('email') is-invalid @enderror" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="mobile" class="form-label">شماره موبایل</label>
                        <input type="text" id="mobile" name="mobile" value="{{ old('mobile', $user->mobile) }}" class="form-control @error('mobile') is-invalid @enderror" dir="ltr">
                        @error('mobile')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">فرمت: 09123456789</small>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="role" class="form-label required">نقش کاربر</label>
                        <select id="role" name="role" class="form-select @error('role') is-invalid @enderror" required>
                            <option value="">انتخاب کنید...</option>
                            <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>مدیر سیستم</option>
                            <option value="user" {{ old('role', $user->role) == 'user' ? 'selected' : '' }}>کاربر عادی</option>
                        </select>
                        @error('role')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label">تغییر رمز عبور <small class="text-muted">(در صورت عدم تمایل به تغییر خالی بگذارید)</small></label>
                        <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">حداقل 8 کاراکتر شامل حروف بزرگ و کوچک و اعداد</small>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="password_confirmation" class="form-label">تکرار رمز عبور</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control">
                    </div>
                    
                    <div class="col-md-12 mb-3">
                        <label class="form-label required">وضعیت کاربر</label>
                        <div class="d-flex">
                            <div class="form-check ml-4">
                                <input class="form-check-input" type="radio" name="is_active" id="is_active_1" value="1" {{ old('is_active', $user->is_active) == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active_1">فعال</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="is_active" id="is_active_0" value="0" {{ old('is_active', $user->is_active) == '0' ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active_0">غیرفعال</label>
                            </div>
                        </div>
                        @error('is_active')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="d-flex justify-content-end mt-4">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-light ml-2">انصراف</a>
                    <button type="submit" class="btn btn-primary">ذخیره تغییرات</button>
                </div>
            </form>
        </div>
    </div>
    
    @if(auth()->id() === $user->id)
        <div class="alert alert-warning mt-4">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-triangle fa-2x ml-3"></i>
                <div>
                    <h5 class="mb-1">هشدار</h5>
                    <p class="mb-0">
                        شما در حال ویرایش حساب کاربری خود هستید. تغییر وضعیت کاربری به «غیرفعال» یا تغییر نقش کاربری ممکن است دسترسی شما را به سیستم محدود کند.
                    </p>
                </div>
            </div>
        </div>
    @endif
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
        const form = document.getElementById('edit-user-form');
        
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
            
            // بررسی تطابق رمز عبور (فقط اگر رمز عبور وارد شده باشد)
            const password = document.getElementById('password');
            const passwordConfirm = document.getElementById('password_confirmation');
            
            if (password.value && password.value !== passwordConfirm.value) {
                password.classList.add('is-invalid');
                passwordConfirm.classList.add('is-invalid');
                hasError = true;
            }
            
            // بررسی پیچیدگی رمز عبور (فقط اگر رمز عبور وارد شده باشد)
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
