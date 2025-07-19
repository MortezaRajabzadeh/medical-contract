<?php

namespace App\Services;

use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use App\Models\Contract;
use App\Models\User;
use Carbon\Carbon;

class MedicalAuditLogger
{
    protected $logChannel = 'medical_audit';
    
    /**
     * Log contract access
     *
     * @param Contract $contract
     * @param string $action
     * @param array $additionalData
     * @return Activity
     */
    public function logContractAccess(Contract $contract, string $action, array $additionalData = []): Activity
    {
        $user = Auth::user();
        
        $logData = [
            'user_id' => $user ? $user->id : null,
            'user_name' => $user ? $user->name : 'System',
            'contract_id' => $contract->id,
            'contract_title' => $contract->title,
            'contract_number' => $contract->contract_number,
            'medical_center_id' => $contract->medical_center_id,
            'action' => $action,
            'ip_address' => request() ? request()->ip() : null,
            'user_agent' => request() ? request()->userAgent() : null,
            'timestamp' => now()->toDateTimeString(),
            'additional_data' => $this->sanitizeData($additionalData),
        ];
        
        // Log to activity log
        $activity = activity()
            ->causedBy($user)
            ->performedOn($contract)
            ->withProperties($logData)
            ->log($action);
        
        // Log to dedicated medical audit log
        Log::channel($this->logChannel)->info('Contract access logged', $logData);
        
        return $activity;
    }
    
    /**
     * Log user action
     *
     * @param User $targetUser
     * @param string $action
     * @param array $additionalData
     * @return Activity
     */
    public function logUserAction(User $targetUser, string $action, array $additionalData = []): Activity
    {
        $user = Auth::user();
        
        $logData = [
            'target_user_id' => $targetUser->id,
            'target_user_name' => $targetUser->name,
            'action' => $action,
            'performed_by' => $user ? $user->id : null,
            'performed_by_name' => $user ? $user->name : 'System',
            'ip_address' => request() ? request()->ip() : null,
            'user_agent' => request() ? request()->userAgent() : null,
            'timestamp' => now()->toDateTimeString(),
            'additional_data' => $this->sanitizeData($additionalData),
        ];
        
        // Log to activity log
        $activity = activity()
            ->causedBy($user)
            ->performedOn($targetUser)
            ->withProperties($logData)
            ->log($action);
        
        // Log to dedicated medical audit log
        Log::channel($this->logChannel)->info('User action logged', $logData);
        
        return $activity;
    }
    
    /**
     * Log system event
     *
     * @param string $event
     * @param string $description
     * @param Model|null $subject
     * @param array $additionalData
     * @return Activity
     */
    public function logSystemEvent(string $event, string $description, ?Model $subject = null, array $additionalData = []): Activity
    {
        $user = Auth::user();
        
        $logData = [
            'event' => $event,
            'description' => $description,
            'performed_by' => $user ? $user->id : null,
            'performed_by_name' => $user ? $user->name : 'System',
            'ip_address' => request() ? request()->ip() : null,
            'user_agent' => request() ? request()->userAgent() : null,
            'timestamp' => now()->toDateTimeString(),
            'additional_data' => $this->sanitizeData($additionalData),
        ];
        
        // Log to activity log
        $activity = activity()
            ->causedBy($user)
            ->withProperties($logData)
            ->log("System Event: {$event}");
            
        if ($subject) {
            $activity->performedOn($subject);
        }
        
        // Log to dedicated medical audit log
        Log::channel($this->logChannel)->info('System event logged', $logData);
        
        return $activity;
    }
    
    /**
     * Get activity logs for a contract
     *
     * @param Contract $contract
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getContractActivityLogs(Contract $contract, int $limit = 100)
    {
        return Activity::causedBy($contract->creator)
            ->orWhere('subject_type', get_class($contract))
            ->where('subject_id', $contract->id)
            ->latest()
            ->limit($limit)
            ->get();
    }
    
    /**
     * Get user activity logs
     *
     * @param User $user
     * @param int $days
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserActivityLogs(User $user, int $days = 30, int $limit = 100)
    {
        return Activity::causedBy($user)
            ->where('created_at', '>=', now()->subDays($days))
            ->latest()
            ->limit($limit)
            ->get();
    }
    
    /**
     * Generate audit report
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param array $filters
     * @return array
     */
    public function generateAuditReport(Carbon $startDate, Carbon $endDate, array $filters = []): array
    {
        $query = Activity::whereBetween('created_at', [$startDate, $endDate]);
        
        // Apply filters
        if (!empty($filters['user_id'])) {
            $query->where('causer_id', $filters['user_id']);
        }
        
        if (!empty($filters['action'])) {
            $query->where('description', 'LIKE', "%{$filters['action']}%");
        }
        
        if (!empty($filters['subject_type'])) {
            $query->where('subject_type', $filters['subject_type']);
        }
        
        $activities = $query->latest()->get();
        
        // Generate report data
        $report = [
            'period' => [
                'start' => $startDate->toDateString(),
                'end' => $endDate->toDateString(),
            ],
            'total_activities' => $activities->count(),
            'activities_by_type' => $activities->groupBy('description')->map->count(),
            'activities_by_user' => $activities->groupBy('causer_id')->map->count(),
            'activities' => $activities->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'description' => $activity->description,
                    'subject_type' => $activity->subject_type,
                    'subject_id' => $activity->subject_id,
                    'causer_name' => $activity->causer->name ?? 'System',
                    'properties' => $activity->properties,
                    'created_at' => $activity->created_at->toDateTimeString(),
                ];
            }),
        ];
        
        return $report;
    }
    
    /**
     * Sanitize log data to remove sensitive information
     *
     * @param array $data
     * @return array
     */
    protected function sanitizeData(array $data): array
    {
        $sensitiveKeys = [
            'password', 'password_confirmation', 'token', 'api_key', 'secret',
            'credit_card', 'cvv', 'ssn', 'social_security', 'dob', 'birth_date'
        ];
        
        array_walk_recursive($data, function (&$value, $key) use ($sensitiveKeys) {
            if (in_array(strtolower($key), $sensitiveKeys)) {
                $value = '***REDACTED***';
            }
        });
        
        return $data;
    }
}
