<?php

namespace App\Repositories;

use App\Exceptions\RelationshipException;
use App\Models\Contract;
use App\Repositories\Interfaces\ContractRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Exception;
use Illuminate\Support\Facades\Log;

/**
 * پیاده‌سازی مخزن قرارداد
 */
class ContractRepository implements ContractRepositoryInterface
{
    /**
     * مدل قرارداد
     * 
     * @var Contract
     */
    protected $model;
    
    /**
     * سازنده کلاس
     * 
     * @param Contract $contract
     */
    public function __construct(Contract $contract)
    {
        $this->model = $contract;
    }
    
    /**
     * دریافت تمام قراردادها
     * 
     * @param array $columns ستون‌های مورد نیاز
     * @return Collection
     */
    public function getAll(array $columns = ['*']): Collection
    {
        try {
            return $this->model->get($columns);
        } catch (Exception $e) {
            Log::error('خطا در دریافت همه قراردادها: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            throw new RelationshipException('خطا در دریافت قراردادها', 
                Contract::class, null, null, $e->getCode(), $e);
        }
    }
    
    /**
     * دریافت قرارداد با شناسه خاص
     * 
     * @param int $id شناسه قرارداد
     * @param array $columns ستون‌های مورد نیاز
     * @return Model|null
     */
    public function findById(int $id, array $columns = ['*']): ?Model
    {
        try {
            return $this->model->find($id, $columns);
        } catch (Exception $e) {
            Log::error("خطا در دریافت قرارداد با شناسه {$id}: " . $e->getMessage(), [
                'id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            throw new RelationshipException("خطا در دریافت قرارداد با شناسه {$id}", 
                Contract::class, null, null, $e->getCode(), $e);
        }
    }
    
    /**
     * دریافت قراردادها به صورت صفحه‌بندی شده
     * 
     * @param int $perPage تعداد آیتم‌ها در هر صفحه
     * @param array $columns ستون‌های مورد نیاز
     * @return LengthAwarePaginator
     */
    public function getPaginated(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator
    {
        try {
            return $this->model->paginate($perPage, $columns);
        } catch (Exception $e) {
            Log::error('خطا در دریافت قراردادهای صفحه‌بندی شده: ' . $e->getMessage(), [
                'per_page' => $perPage,
                'trace' => $e->getTraceAsString()
            ]);
            throw new RelationshipException('خطا در دریافت قراردادهای صفحه‌بندی شده', 
                Contract::class, null, null, $e->getCode(), $e);
        }
    }
    
    /**
     * دریافت قراردادها با فیلتر مرکز درمانی
     * 
     * @param int $medicalCenterId شناسه مرکز درمانی
     * @param array $columns ستون‌های مورد نیاز
     * @return Collection
     */
    public function getByMedicalCenter(int $medicalCenterId, array $columns = ['*']): Collection
    {
        try {
            return $this->model->where('medical_center_id', $medicalCenterId)->get($columns);
        } catch (Exception $e) {
            Log::error("خطا در دریافت قراردادهای مرکز درمانی {$medicalCenterId}: " . $e->getMessage(), [
                'medical_center_id' => $medicalCenterId,
                'trace' => $e->getTraceAsString()
            ]);
            throw new RelationshipException('خطا در دریافت قراردادهای مرکز درمانی', 
                Contract::class, 'MedicalCenter', 'medicalCenter', $e->getCode(), $e);
        }
    }
    
    /**
     * دریافت قراردادها با فیلتر وضعیت
     * 
     * @param string $status وضعیت قرارداد
     * @param array $columns ستون‌های مورد نیاز
     * @return Collection
     */
    public function getByStatus(string $status, array $columns = ['*']): Collection
    {
        try {
            return $this->model->where('status', $status)->get($columns);
        } catch (Exception $e) {
            Log::error("خطا در دریافت قراردادها با وضعیت {$status}: " . $e->getMessage(), [
                'status' => $status,
                'trace' => $e->getTraceAsString()
            ]);
            throw new RelationshipException("خطا در دریافت قراردادها با وضعیت {$status}", 
                Contract::class, null, null, $e->getCode(), $e);
        }
    }
    
    /**
     * جستجو در قراردادها
     * 
     * @param string $query عبارت جستجو
     * @param array $columns ستون‌های مورد نیاز
     * @return Collection
     */
    public function search(string $query, array $columns = ['*']): Collection
    {
        try {
            return $this->model->where('title', 'LIKE', "%{$query}%")
                ->orWhere('contract_number', 'LIKE', "%{$query}%")
                ->orWhere('description', 'LIKE', "%{$query}%")
                ->get($columns);
        } catch (Exception $e) {
            Log::error("خطا در جستجوی قراردادها با کلیدواژه {$query}: " . $e->getMessage(), [
                'query' => $query,
                'trace' => $e->getTraceAsString()
            ]);
            throw new RelationshipException("خطا در جستجوی قراردادها", 
                Contract::class, null, null, $e->getCode(), $e);
        }
    }
    
    /**
     * ذخیره قرارداد جدید
     * 
     * @param array $data داده‌های قرارداد
     * @return Model
     */
    public function create(array $data): Model
    {
        try {
            return $this->model->create($data);
        } catch (Exception $e) {
            Log::error('خطا در ایجاد قرارداد جدید: ' . $e->getMessage(), [
                'data' => $data,
                'trace' => $e->getTraceAsString()
            ]);
            throw new RelationshipException('خطا در ایجاد قرارداد جدید', 
                Contract::class, null, null, $e->getCode(), $e);
        }
    }
    
    /**
     * به‌روزرسانی قرارداد
     * 
     * @param int $id شناسه قرارداد
     * @param array $data داده‌های جدید
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        try {
            $contract = $this->findById($id);
            if (!$contract) {
                return false;
            }
            
            return $contract->update($data);
        } catch (Exception $e) {
            Log::error("خطا در به‌روزرسانی قرارداد با شناسه {$id}: " . $e->getMessage(), [
                'id' => $id,
                'data' => $data,
                'trace' => $e->getTraceAsString()
            ]);
            throw new RelationshipException("خطا در به‌روزرسانی قرارداد با شناسه {$id}", 
                Contract::class, null, null, $e->getCode(), $e);
        }
    }
    
    /**
     * حذف قرارداد
     * 
     * @param int $id شناسه قرارداد
     * @return bool
     */
    public function delete(int $id): bool
    {
        try {
            $contract = $this->findById($id);
            if (!$contract) {
                return false;
            }
            
            return $contract->delete();
        } catch (Exception $e) {
            Log::error("خطا در حذف قرارداد با شناسه {$id}: " . $e->getMessage(), [
                'id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            throw new RelationshipException("خطا در حذف قرارداد با شناسه {$id}", 
                Contract::class, null, null, $e->getCode(), $e);
        }
    }
}
