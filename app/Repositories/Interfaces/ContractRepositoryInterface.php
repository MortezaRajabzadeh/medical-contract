<?php

namespace App\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * رابط مخزن قرارداد
 */
interface ContractRepositoryInterface
{
    /**
     * دریافت تمام قراردادها
     * 
     * @param array $columns ستون‌های مورد نیاز
     * @return Collection
     */
    public function getAll(array $columns = ['*']): Collection;
    
    /**
     * دریافت قرارداد با شناسه خاص
     * 
     * @param int $id شناسه قرارداد
     * @param array $columns ستون‌های مورد نیاز
     * @return Model|null
     */
    public function findById(int $id, array $columns = ['*']): ?Model;
    
    /**
     * دریافت قراردادها به صورت صفحه‌بندی شده
     * 
     * @param int $perPage تعداد آیتم‌ها در هر صفحه
     * @param array $columns ستون‌های مورد نیاز
     * @return LengthAwarePaginator
     */
    public function getPaginated(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator;
    
    /**
     * دریافت قراردادها با فیلتر مرکز درمانی
     * 
     * @param int $medicalCenterId شناسه مرکز درمانی
     * @param array $columns ستون‌های مورد نیاز
     * @return Collection
     */
    public function getByMedicalCenter(int $medicalCenterId, array $columns = ['*']): Collection;
    
    /**
     * دریافت قراردادها با فیلتر وضعیت
     * 
     * @param string $status وضعیت قرارداد
     * @param array $columns ستون‌های مورد نیاز
     * @return Collection
     */
    public function getByStatus(string $status, array $columns = ['*']): Collection;
    
    /**
     * جستجو در قراردادها
     * 
     * @param string $query عبارت جستجو
     * @param array $columns ستون‌های مورد نیاز
     * @return Collection
     */
    public function search(string $query, array $columns = ['*']): Collection;
    
    /**
     * ذخیره قرارداد جدید
     * 
     * @param array $data داده‌های قرارداد
     * @return Model
     */
    public function create(array $data): Model;
    
    /**
     * به‌روزرسانی قرارداد
     * 
     * @param int $id شناسه قرارداد
     * @param array $data داده‌های جدید
     * @return bool
     */
    public function update(int $id, array $data): bool;
    
    /**
     * حذف قرارداد
     * 
     * @param int $id شناسه قرارداد
     * @return bool
     */
    public function delete(int $id): bool;
}
