@extends('admin.layouts.app')

@section('title', 'جزئیات کاربر')

@section('page-title', 'جزئیات کاربر')

@section('page-actions')
    <div class="btn-group">
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-right ml-1"></i> بازگشت به لیست
        </a>
        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-warning">
            <i class="fas fa-edit ml-1"></i> ویرایش
        </a>
        @if(auth()->id() !== $user->id)
            <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline" id="delete-user-form">
                @csrf
                @method('DELETE')
                <button type="button" class="btn btn-danger" id="delete-btn">
                    <i class="fas fa-trash ml-1"></i> حذف
                </button>
            </form>
        @endif
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">اطلاعات اصلی کاربر</h5>
                </div>
                
                <div class="card-body">
                    <div class="mb-4">
                        <h4>{{ $user->name }}</h4>
                        <div class="small text-muted">{{ $user->email }}</div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label class="fw-bold">نقش کاربری:</label>
                                <div>
                                    @if($user->role === 'admin')
                                        <span class="badge bg-danger">مدیر سیستم</span>
                                    @else
                                        <span class="badge bg-info">کاربر عادی</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label class="fw-bold">وضعیت:</label>
                                <div>
                                    @if($user->is_active)
                                        <span class="badge bg-success">فعال</span>
                                    @else
                                        <span class="badge bg-secondary">غیرفعال</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label class="fw-bold">شماره موبایل:</label>
                                <div>
                                    {{ $user->mobile ?? 'ثبت نشده' }}
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label class="fw-bold">تاریخ ثبت‌نام:</label>
                                <div>{{ \Morilog\Jalali\Jalalian::fromDateTime($user->created_at)->format('Y/m/d H:i') }}</div>
                            </div>
                        </div>
                        
                        <div class="col-12 mt-3">
                            <div class="alert {{ $user->is_active ? 'alert-success' : 'alert-warning' }}">
                                <div class="d-flex">
                                    <div class="ml-3">
                                        <i class="fas {{ $user->is_active ? 'fa-check-circle' : 'fa-exclamation-triangle' }} fa-2x"></i>
                                    </div>
                                    <div>
                                        <h5 class="alert-heading">{{ $user->is_active ? 'کاربر فعال است' : 'کاربر غیرفعال است' }}</h5>
                                        <p class="mb-0">
                                            @if($user->is_active)
                                                این کاربر می‌تواند وارد سیستم شده و از امکانات استفاده کند.
                                            @else
                                                این کاربر نمی‌تواند وارد سیستم شود. برای فعال‌سازی، وضعیت کاربر را ویرایش کنید.
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
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
                        <label class="fw-bold">آخرین بروزرسانی:</label>
                        <div>{{ \Morilog\Jalali\Jalalian::fromDateTime($user->updated_at)->format('Y/m/d H:i:s') }}</div>
                    </div>
                    
                    @if(auth()->id() === $user->id)
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle ml-1"></i>
                            این حساب کاربری شماست و نمی‌توانید آن را حذف کنید.
                        </div>
                    @endif
                    
                    <div class="mb-3">
                        <label class="fw-bold">وضعیت امنیتی:</label>
                        <div>
                            <span class="badge {{ $user->email_verified_at ? 'bg-success' : 'bg-warning' }}">
                                {{ $user->email_verified_at ? 'ایمیل تایید شده' : 'ایمیل تایید نشده' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            @if(!empty($user->remember_token))
                <div class="card mt-3">
                    <div class="card-header bg-warning text-white">
                        <h5 class="mb-0">نشست‌های فعال</h5>
                    </div>
                    
                    <div class="card-body">
                        <div class="alert alert-warning">
                            <i class="fas fa-user-shield ml-1"></i>
                            این کاربر دارای نشست فعال در سیستم است. با حذف کاربر یا تغییر رمز عبور، تمام نشست‌های فعال باطل خواهند شد.
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

@section('scripts')
<script>
    // کد مربوط به تایید حذف کاربر
    document.addEventListener('DOMContentLoaded', function() {
        const deleteBtn = document.getElementById('delete-btn');
        const deleteForm = document.getElementById('delete-user-form');
        
        if (deleteBtn && deleteForm) {
            deleteBtn.addEventListener('click', function(e) {
                e.preventDefault();
                
                if (confirm('آیا از حذف این کاربر اطمینان دارید؟ این عملیات قابل بازگشت نیست.')) {
                    deleteForm.submit();
                }
            });
        }
    });
</script>
@endsection
