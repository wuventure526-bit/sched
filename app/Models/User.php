<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\HasRoles; // <-- ADD THIS

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasRoles; // <-- ADD HasRoles HERE

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'role',
        'address',
        'city',
        'photo',
        'about_me',
        'unit_id',
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected static function newFactory()
    {
        return \Database\Factories\UserFactory::new();
    }

    /**
     * Role identity.
     *
     * These read the `role` column directly and are the only reliable way to
     * ask "what is this user?". Do NOT use `can('unitadmin')` for that: the
     * Spatie role carries permissions literally named `unitadmin` and
     * `borrower`, and its Gate::before hook makes those checks return true for
     * an administrator — which silently drops admins into unit-scoped branches
     * with a null unit_id.
     */
    public function isAdministrator(): bool
    {
        if (in_array($this->normalisedRole(), ['administrator', 'admin'], true)) {
            return true;
        }

        // Also honour a Spatie role, so an account granted admin that way keeps
        // working even if the `role` column was never filled in.
        return $this->hasRole(['administrator', 'admin']);
    }

    public function isUnitAdmin(): bool
    {
        return in_array($this->normalisedRole(), ['unitadmin', 'unit admin'], true);
    }

    public function isBorrower(): bool
    {
        return $this->normalisedRole() === 'borrower';
    }

    /**
     * An administrator is not tied to a unit, so it sees every unit rather than
     * one. Call this wherever a query would otherwise be scoped to unit_id.
     */
    public function seesAllUnits(): bool
    {
        return $this->isAdministrator();
    }

    protected function normalisedRole(): string
    {
        return strtolower(trim((string) $this->role));
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function usages()
    {
        return $this->hasMany(Usage::class);
    }

    protected $primaryKey = 'id';
}
