<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['role_id', 'instansi_id', 'pembimbing_id', 'nip', 'nama_lengkap', 'email', 'no_telepon', 'password', 'status_aktif'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

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
            'status_aktif' => 'boolean',
        ];
    }

    /**
     * Get the role associated with the user.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the educational institution associated with the user.
     */
    public function instansi(): BelongsTo
    {
        return $this->belongsTo(Instansi::class);
    }

    /**
     * Get the supervisor (pembimbing) for this user.
     */
    public function pembimbing(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pembimbing_id');
    }

    /**
     * Get the interns (anak bimbingan) guided by this supervisor.
     */
    public function anakBimbingan(): HasMany
    {
        return $this->hasMany(User::class, 'pembimbing_id');
    }

    /**
     * Get the attendance records for the user.
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Get the logbook entries for the user.
     */
    public function logbooks(): HasMany
    {
        return $this->hasMany(Logbook::class);
    }

    /**
     * Get the leave requests submitted by the user.
     */
    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    /**
     * Helper to check if user has the Super Admin role.
     */
    public function isSuperAdmin(): bool
    {
        return $this->role?->nama_role === 'super_admin';
    }

    /**
     * Helper to check if user has the Admin (Pembimbing Lapangan) role.
     */
    public function isAdmin(): bool
    {
        return $this->role?->nama_role === 'admin';
    }

    /**
     * Helper to check if user has the Peserta (Intern) role.
     */
    public function isPeserta(): bool
    {
        return $this->role?->nama_role === 'peserta';
    }
}
