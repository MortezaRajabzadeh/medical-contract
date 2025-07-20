<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Contract extends Model
{
    use HasFactory, SoftDeletes;
    
    /**
     * روابطی که همیشه باید بارگذاری شوند (eager loading).
     *
     * @var array
     */
    protected $with = ['medicalCenter', 'createdBy', 'approvedBy'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'contract_number',
        'medical_center_id',
        'title',
        'description',
        'contract_type',
        'vendor_name',
        'vendor_contact',
        'start_date',
        'end_date',
        'renewal_date',
        'status',
        'created_by',
        'approved_by',
        'approved_at',
        'file_path',
        'original_filename',
        'file_hash',
        'file_size',
        'metadata',
        'notes',
        'signed_file_path',
        'signed_date',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'renewal_date' => 'date',
        'approved_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Get the medical center that owns the contract.
     */
    public function medicalCenter(): BelongsTo
    {
        return $this->belongsTo(MedicalCenter::class);
    }

    /**
     * Get the user that created the contract.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user that approved the contract.
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Check if the contract is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active' || $this->status === 'approved';
    }

    /**
     * Check if the contract is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if the contract is expired.
     */
    public function isExpired(): bool
    {
        return $this->status === 'expired' || ($this->end_date && $this->end_date->isPast());
    }

    /**
     * Get the status badge HTML.
     */
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'pending' => '<span class="px-2 py-1 rounded-full text-xs text-yellow-800 bg-yellow-100">در انتظار امضا</span>',
            'uploaded' => '<span class="px-2 py-1 rounded-full text-xs text-blue-800 bg-blue-100">بارگزاری شده</span>',
            'under_review' => '<span class="px-2 py-1 rounded-full text-xs text-indigo-800 bg-indigo-100">در دست بررسی</span>',
            'approved' => '<span class="px-2 py-1 rounded-full text-xs text-green-800 bg-green-100">تایید نهایی</span>',
            default => '<span class="px-2 py-1 rounded-full text-xs text-gray-800 bg-gray-100">' . ucfirst($this->status) . '</span>',
        };
    }

    /**
     * Calculate days remaining in the contract.
     */
    public function getDaysRemainingAttribute(): int
    {
        if (!$this->end_date) {
            return 0;
        }
        
        return max(0, now()->diffInDays($this->end_date, false));
    }

    /**
     * Check if contract needs renewal soon (within 30 days).
     */
    public function getNeedsRenewalSoonAttribute(): bool
    {
        return $this->days_remaining > 0 && $this->days_remaining <= 30;
    }

    // متد getFormattedValueAttribute حذف شد چون مبلغ قرارداد دیگر استفاده نمی‌شود
    
    /**
     * رابطه برای نمایش کاربرانی که این قرارداد را مشاهده کرده‌اند
     */
    public function viewedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'contract_user')
                    ->withPivot('viewed_at')
                    ->withTimestamps();
    }
}
