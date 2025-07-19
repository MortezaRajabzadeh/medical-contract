@extends('admin.layouts.app')

@section('title', 'مراکز درمانی')

@section('page-title', 'مراکز درمانی')

@section('page-actions')
    <a href="{{ route('admin.medical-centers.create') }}" class="btn btn-primary">
        <i class="fas fa-plus ml-1"></i> افزودن مرکز درمانی
    </a>
@endsection

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">لیست مراکز درمانی</h5>
            
            <form action="{{ route('admin.medical-centers.index') }}" method="GET" class="d-flex">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="جستجو..." value="{{ request('search') }}">
                    <button class="btn btn-outline-secondary" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>نام مرکز</th>
                            <th>کد مرکز</th>
                            <th>تلفن</th>
                            <th>ایمیل</th>
                            <th>تعداد قراردادها</th>
                            <th>تاریخ ایجاد</th>
                            <th class="text-center">عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($medicalCenters as $key => $center)
                            <tr>
                                <td>{{ $medicalCenters->firstItem() + $key }}</td>
                                <td>{{ $center->name }}</td>
                                <td>{{ $center->center_id }}</td>
                                <td>{{ $center->phone }}</td>
                                <td>{{ $center->email }}</td>
                                <td>{{ $center->contracts_count ?? $center->contracts()->count() }}</td>
                                <td>{{ \Morilog\Jalali\Jalalian::fromDateTime($center->created_at)->format('Y/m/d') }}</td>
                                <td class="text-center">
                                    <div class="btn-group btn-actions">
                                        <a href="{{ route('admin.medical-centers.show', $center) }}" class="btn btn-sm btn-info" title="نمایش">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.medical-centers.edit', $center) }}" class="btn btn-sm btn-warning" title="ویرایش">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.medical-centers.destroy', $center) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="حذف" onclick="return confirm('آیا از حذف این مرکز درمانی اطمینان دارید؟')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">هیچ مرکز درمانی یافت نشد!</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="card-footer">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    نمایش {{ $medicalCenters->firstItem() }} تا {{ $medicalCenters->lastItem() }} از {{ $medicalCenters->total() }} مورد
                </div>
                
                <div>
                    {{ $medicalCenters->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
