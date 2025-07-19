@extends('admin.layouts.app')

@section('title', 'مدیریت کاربران')

@section('page-title', 'مدیریت کاربران')

@section('page-actions')
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
        <i class="fas fa-plus ml-1"></i> افزودن کاربر جدید
    </a>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">جستجو در کاربران</h5>
        </div>
        
        <div class="card-body">
            <form action="{{ route('admin.users.index') }}" method="GET">
                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label for="search" class="form-label">جستجو</label>
                        <input type="text" name="search" id="search" class="form-control" placeholder="نام، ایمیل یا شماره موبایل..." value="{{ request('search') }}">
                    </div>
                    
                    <div class="col-md-4 d-flex align-items-end mb-3">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search ml-1"></i> جستجو
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <div class="card mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">لیست کاربران</h5>
            
            <span class="badge bg-primary">
                تعداد: {{ $users->total() }}
            </span>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>نام و نام خانوادگی</th>
                            <th>ایمیل</th>
                            <th>شماره موبایل</th>
                            <th>نقش</th>
                            <th>وضعیت</th>
                            <th>تاریخ ثبت‌نام</th>
                            <th class="text-center">عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $key => $user)
                            <tr>
                                <td>{{ $users->firstItem() + $key }}</td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->mobile ?? '-' }}</td>
                                <td>
                                    @if($user->role === 'admin')
                                        <span class="badge bg-danger">مدیر سیستم</span>
                                    @else
                                        <span class="badge bg-info">کاربر عادی</span>
                                    @endif
                                </td>
                                <td>
                                    @if($user->is_active)
                                        <span class="badge bg-success">فعال</span>
                                    @else
                                        <span class="badge bg-secondary">غیرفعال</span>
                                    @endif
                                </td>
                                <td>{{ \Morilog\Jalali\Jalalian::fromDateTime($user->created_at)->format('Y/m/d') }}</td>
                                <td class="text-center">
                                    <div class="btn-group btn-actions">
                                        <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-info" title="نمایش">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-warning" title="ویرایش">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @if(auth()->id() !== $user->id)
                                            <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="حذف" onclick="return confirm('آیا از حذف این کاربر اطمینان دارید؟')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">هیچ کاربری یافت نشد!</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="card-footer">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    نمایش {{ $users->firstItem() ?? 0 }} تا {{ $users->lastItem() ?? 0 }} از {{ $users->total() }} مورد
                </div>
                
                <div>
                    {{ $users->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
