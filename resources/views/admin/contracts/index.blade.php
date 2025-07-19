@extends('admin.layouts.app')

@section('title', 'مدیریت قراردادها')

@section('page-title', 'مدیریت قراردادها')

@section('page-actions')
    <a href="{{ route('admin.contracts.create') }}" class="btn btn-primary">
        <i class="fas fa-plus ml-1"></i> افزودن قرارداد جدید
    </a>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">فیلتر قراردادها</h5>
        </div>
        
        <div class="card-body">
            <form action="{{ route('admin.contracts.index') }}" method="GET" class="row">
                <div class="col-md-4 mb-3">
                    <label for="status" class="form-label">وضعیت</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">همه وضعیت‌ها</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>فعال</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>در انتظار امضا</option>
                        <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>منقضی شده</option>
                    </select>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="medical_center_id" class="form-label">مرکز درمانی</label>
                    <select name="medical_center_id" id="medical_center_id" class="form-select">
                        <option value="">همه مراکز درمانی</option>
                        @foreach ($medicalCenters as $id => $name)
                            <option value="{{ $id }}" {{ request('medical_center_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="search" class="form-label">جستجو</label>
                    <div class="input-group">
                        <input type="text" name="search" id="search" class="form-control" placeholder="شماره یا عنوان قرارداد..." value="{{ request('search') }}">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <div class="card mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">لیست قراردادها</h5>
            
            <span class="badge bg-primary">
                تعداد: {{ $contracts->total() }}
            </span>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>شماره قرارداد</th>
                            <th>عنوان</th>
                            <th>مرکز درمانی</th>
                            <th>تاریخ شروع</th>
                            <th>تاریخ پایان</th>
                            <th>وضعیت</th>
                            <th class="text-center">عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($contracts as $key => $contract)
                            <tr>
                                <td>{{ $contracts->firstItem() + $key }}</td>
                                <td>{{ $contract->contract_number }}</td>
                                <td>
                                    @if($contract->status === 'pending' && $contract->viewedBy->isEmpty())
                                        <span class="badge bg-danger rounded-circle me-1" style="width: 8px; height: 8px;" title="دیده نشده"></span>
                                    @endif
                                    {{ $contract->title }}
                                </td>
                                <td>{{ $contract->medicalCenter->name ?? 'نامشخص' }}</td>
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
                                <td class="text-center">
                                    <div class="btn-group btn-actions">
                                        <a href="{{ route('admin.contracts.show', $contract) }}" class="btn btn-sm btn-info" title="نمایش">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.contracts.edit', $contract) }}" class="btn btn-sm btn-warning" title="ویرایش">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.contracts.destroy', $contract) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="حذف" onclick="return confirm('آیا از حذف این قرارداد اطمینان دارید؟')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">هیچ قراردادی یافت نشد!</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="card-footer">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    نمایش {{ $contracts->firstItem() ?? 0 }} تا {{ $contracts->lastItem() ?? 0 }} از {{ $contracts->total() }} مورد
                </div>
                
                <div>
                    {{ $contracts->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    // ارسال خودکار فرم فیلتر با تغییر مقدار سلکت‌باکس‌ها
    document.addEventListener('DOMContentLoaded', function() {
        const filterForm = document.querySelector('form[action="{{ route('admin.contracts.index') }}"]');
        const selectInputs = filterForm.querySelectorAll('select');
        
        selectInputs.forEach(select => {
            select.addEventListener('change', () => {
                filterForm.submit();
            });
        });
    });
</script>
@endsection
