<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'medical_center_id',
        'employee_id',
        'department',
        'position',
        'hire_date',
        'user_type',
        'contact_details',
        'is_active'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'hire_date' => 'date',
            'last_login_at' => 'datetime',
            'is_active' => 'boolean',
            'contact_details' => 'array',
        ];
    }

    /**
     * Get the medical center that the user belongs to.
     */
    public function medicalCenter(): BelongsTo
    {
        return $this->belongsTo(MedicalCenter::class);
    }

    /**
     * Check if the user belongs to a medical center.
     */
    public function belongsToMedicalCenter(): bool
    {
        return !is_null($this->medical_center_id);
    }

    /**
     * Check if the user is a medical staff.
     */
    public function isMedicalStaff(): bool
    {
        return $this->user_type === 'medical_staff';
    }

    /**
     * Check if the user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->user_type === 'admin';
    }
    
    /**
     * تعیین اینکه کاربر می‌تواند به پنل Filament دسترسی داشته باشد یا خیر
     *
     * @param \Filament\Panel $panel
     * @return bool
     */
    public function canAccessPanel(Panel $panel): bool
    {
        try {
            // کاربر باید نقش admin داشته باشد یا user_type آن admin باشد
            return $this->hasRole('admin') || $this->isAdmin();
        } catch (\Exception $e) {
            // در صورت بروز خطا، دسترسی داده نمی‌شود
            logger()->error('خطا در بررسی دسترسی به پنل Filament: ' . $e->getMessage(), [
                'user_id' => $this->id,
                'user_email' => $this->email
            ]);
            return false;
        }
    }

    /**
     * Get all contracts created by this user.
     */
    public function createdContracts()
    {
        return $this->hasMany(Contract::class, 'created_by');
    }

    /**
     * Get all contracts approved by this user.
     */
    public function approvedContracts()
    {
        return $this->hasMany(Contract::class, 'approved_by');
    }

    /**
     * Record the user's login time.
     */
    public function recordLogin(): void
    {
        $this->last_login_at = now();
        $this->save();
    }
}
