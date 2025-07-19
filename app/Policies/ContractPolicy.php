<?php

namespace App\Policies;

use App\Models\Contract;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Log;

class ContractPolicy
{
    use HandlesAuthorization;

    /**
     * تعیین اینکه آیا کاربر می‌تواند قراردادها را مشاهده کند
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Contract  $contract
     * @return bool
     */
    public function view(User $user, Contract $contract)
    {
        // لاگ کردن اطلاعات برای دیباگ
        Log::info('بررسی مجوز مشاهده قرارداد', [
            'user_id' => $user->id,
            'user_type' => $user->user_type,
            'medical_center_id' => $user->medical_center_id,
            'contract_id' => $contract->id,
            'contract_medical_center_id' => $contract->medical_center_id,
            'has_view_contracts_permission' => $user->can('view_contracts'),
            'roles' => $user->roles->pluck('name')->toArray()
        ]);

        // اگر کاربر مرتبط با مرکز درمانی باشد که قرارداد برای آن است
        // مراکز درمانی باید قراردادهایی که برای آنها آپلود شده را ببینند
        if ($user->medical_center_id && $user->medical_center_id == $contract->medical_center_id) {
            return $user->can('view_contracts');
        }

        // اگر کاربر ادمین سیستم باشد، همه قراردادها را می‌تواند ببیند
        if ($user->hasRole('system_admin') || $user->hasAnyRole(['system_admin', 'admin', 'super_admin'])) {
            return true;
        }

        // بررسی مستقیم مجوز view_contracts
        if ($user->can('view_contracts')) {
            // برای کاربرانی که مجوز مشاهده دارند ولی به مرکز درمانی متصل نیستند
            // (مثل ادمین‌ها یا ناظران) باید دسترسی داشته باشند
            if ($user->user_type == 'admin' || !$user->medical_center_id) {
                return true;
            }
        }

        return false; // سایر کاربران دسترسی ندارند
    }

    /**
     * تعیین اینکه آیا کاربر می‌تواند قرارداد را دانلود کند
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Contract  $contract
     * @return bool
     */
    public function download(User $user, Contract $contract)
    {
        // لاگ کردن اطلاعات برای دیباگ
        Log::info('بررسی مجوز دانلود قرارداد', [
            'user_id' => $user->id,
            'user_type' => $user->user_type,
            'medical_center_id' => $user->medical_center_id,
            'contract_id' => $contract->id,
            'contract_medical_center_id' => $contract->medical_center_id,
            'has_download_files_permission' => $user->can('download_files'),
            'roles' => $user->roles->pluck('name')->toArray()
        ]);

        // اگر کاربر مرتبط با مرکز درمانی باشد که قرارداد برای آن است
        if ($user->medical_center_id && $user->medical_center_id == $contract->medical_center_id) {
            return $user->can('download_files');
        }

        // اگر کاربر ادمین سیستم باشد
        if ($user->hasRole('system_admin') || $user->hasAnyRole(['system_admin', 'admin', 'super_admin'])) {
            return true;
        }

        // بررسی مستقیم مجوز download_files
        if ($user->can('download_files')) {
            if ($user->user_type == 'admin' || !$user->medical_center_id) {
                return true;
            }
        }

        return false;
    }

    /**
     * تعیین اینکه آیا کاربر می‌تواند وضعیت قرارداد را بروزرسانی کند
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Contract  $contract
     * @return bool
     */
    public function updateStatus(User $user, Contract $contract)
    {
        // فقط ادمین می‌تواند وضعیت قرارداد را تغییر دهد (تایید یا رد کند)
        return $user->hasRole('admin');
    }

    /**
     * تعیین اینکه آیا کاربر می‌تواند قرارداد را امضا کند
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Contract  $contract
     * @return bool
     */
    public function sign(User $user, Contract $contract)
    {
        // فقط مرکز درمانی مرتبط با قرارداد می‌تواند آن را امضا کند
        return $user->medical_center_id && $user->medical_center_id == $contract->medical_center_id;
    }

    /**
     * تعیین اینکه آیا کاربر می‌تواند قرارداد امضا شده را آپلود کند
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Contract  $contract
     * @return bool
     */
    public function upload(User $user, Contract $contract)
    {
        // اگر کاربر ادمین سیستم باشد، اجازه آپلود دارد
        if ($user->hasAnyRole(['system_admin', 'admin', 'super_admin'])) {
            return true;
        }

        // یا اگر کاربر متعلق به همان مرکز درمانی قرارداد باشد
        return $user->medical_center_id && $user->medical_center_id == $contract->medical_center_id;
    }
}
