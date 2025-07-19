<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicalCenter extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'license_number',
        'address',
        'phone',
        'email',
        'director_name',
        'status',
        'operating_hours',
        'latitude',
        'longitude',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'operating_hours' => 'array',
        'status' => 'string',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    /**
     * Get the contracts for the medical center.
     */
    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    /**
     * Get the users associated with the medical center.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get active contracts for this medical center.
     */
    public function activeContracts(): HasMany
    {
        return $this->contracts()->whereIn('status', ['active', 'approved']);
    }

    /**
     * Get pending contracts for this medical center.
     */
    public function pendingContracts(): HasMany
    {
        return $this->contracts()->where('status', 'pending');
    }

    /**
     * Get the status badge HTML.
     */
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'active' => '<span class="px-2 py-1 rounded-full text-xs text-green-800 bg-green-100">Active</span>',
            'inactive' => '<span class="px-2 py-1 rounded-full text-xs text-gray-800 bg-gray-100">Inactive</span>',
            'suspended' => '<span class="px-2 py-1 rounded-full text-xs text-red-800 bg-red-100">Suspended</span>',
            default => '<span class="px-2 py-1 rounded-full text-xs text-gray-800 bg-gray-100">' . ucfirst($this->status) . '</span>',
        };
    }
}
