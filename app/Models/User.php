<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',    
        'email',
        'password',
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
        ];
    }

    public function getNameAttribute()
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function employee()
    {
        return $this->hasOne(\App\Models\Employee::class, 'user_id');
    }

    public function customer()
    {
        return $this->hasOne(\App\Models\Customer::class,'user_id');
    }

    public function orders()
    {
        return $this->hasMany(\App\Models\Order::class,'user_id');
    }

    public function reviews()
    {
        return $this->hasMany(\App\Models\Review::class);
    }

    /**
     * Get the branch assigned to this user (via employee record)
     */
    public function getBranch()
    {
        return $this->employee ? $this->employee->branch : null;
    }

    /**
     * Check if user has access to a specific branch
     */
    public function canAccessBranch($branchId)
    {
        // Admins can access all branches
        if ($this->is_admin || $this->hasRole('Super Admin')) {
            return true;
        }

        // Check if user's employee record matches the branch
        if ($this->employee && $this->employee->branch_id == $branchId) {
            return true;
        }

        return false;
    }

    /**
     * Check if user should be restricted to their branch
     */
    public function isBranchRestricted()
    {
        // Admins are not restricted
        if ($this->is_admin || $this->hasRole('Super Admin')) {
            return false;
        }

        // Users with employee records are restricted to their branch
        return $this->employee && $this->employee->branch_id;
    }

}
