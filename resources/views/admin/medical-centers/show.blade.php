@extends('admin.layouts.app')

@section('title', 'جزئیات مرکز درمانی')

@section('page-title', 'جزئیات مرکز درمانی')

@section('page-actions')
    <div class="btn-group">
        <a href="{{ route('admin.medical-centers.edit', $medicalCenter) }}" class="btn btn-warning">
            <i class="fas fa-edit ml-1"></i> ویرایش
        </a>
        <a href="{{ route('admin.medical-centers.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-right ml-1"></i> بازگشت به لیست
        </a>
    </div>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">{{ $medicalCenter->name }}</h5>
        </div>
        
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="p-3 bg-light rounded">
                        <h6 class="border-bottom pb-2 mb-3">اطلاعات اصلی</h6>
                        
                        <div class="mb-3">
                            <label class="fw-bold">نام مرکز:</label>
                            <div>{{ $medicalCenter->name }}</div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="fw-bold">کد مرکز:</label>
                            <div>{{ $medicalCenter->center_id }}</div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="fw-bold">مدیر مرکز:</label>
                            <div>{{ $medicalCenter->manager ?? 'تعیین نشده' }}</div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="fw-bold">تاریخ ایجاد:</label>
                            <div>{{ \Morilog\Jalali\Jalalian::fromDateTime($medicalCenter->created_at)->format('Y/m/d H:i') }}</div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 mb-4">
                    <div class="p-3 bg-light rounded">
                        <h6 class="border-bottom pb-2 mb-3">اطلاعات تماس</h6>
                        
                        <div class="mb-3">
                            <label class="fw-bold">شماره تلفن:</label>
                            <div>{{ $medicalCenter->phone }}</div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="fw-bold">ایمیل:</label>
                            <div>{{ $medicalCenter->email ?? 'تعیین نشده' }}</div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="fw-bold">آدرس:</label>
                            <div>{{ $medicalCenter->address ?? 'تعیین نشده' }}</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-12">
                    <div class="p-3 bg-light rounded">
                        <h6 class="border-bottom pb-2 mb-3">قراردادهای فعال</h6>
                        
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>شماره قرارداد</th>
                                        <th>عنوان</th>
                                        <th>تاریخ شروع</th>
                                        <th>تاریخ پایان</th>
                                        <th>وضعیت</th>
                                        <th>عملیات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if($medicalCenter->contracts && $medicalCenter->contracts->count() > 0)
                                        @foreach($medicalCenter->contracts as $key => $contract)
                                            <tr>
                                                <td>{{ $key + 1 }}</td>
                                                <td>{{ $contract->contract_number }}</td>
                                                <td>{{ $contract->title }}</td>
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
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="7" class="text-center py-4">هیچ قراردادی یافت نشد!</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
