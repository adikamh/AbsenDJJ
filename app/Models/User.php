<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Services\UniqueCodeGenerator;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'user_code',
        'role_id',
        'instansi_id',
        'pembimbing_id',
        'nip',
        'nama_lengkap',
        'jabatan',
        'email',
        'no_telepon',
        'alamat',
        'nama_darurat_1',
        'no_darurat_1',
        'hubungan_darurat_1',
        'nama_darurat_2',
        'no_darurat_2',
        'hubungan_darurat_2',
        'password',
        'signature_path',
        'status_aktif',
        'auto_approve_logbook_global',
        'auto_approve_logbook',
        'require_photo_attendance_global',
        'require_photo_attendance',
    ];

    /**
     * Auto-generate user_code when creating a new user.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::created(function (self $user) {
            // Generated after create so we have the real auto-increment `id`
            if (empty($user->user_code)) {
                try {
                    $generator = app(UniqueCodeGenerator::class);
                    $user->updateQuietly(['user_code' => $generator->generateUserCode($user->id)]);
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::error('Failed to generate user_code for user #' . $user->id . ': ' . $e->getMessage());
                }
            }
        });
    }

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
     * Use user_code as the route model binding key.
     * This hides the internal auto-increment `id` from URLs.
     */
    public function getRouteKeyName(): string
    {
        return 'user_code';
    }

    /**
     * Get the value of the model's route key with fallback to `id`.
     */
    public function getRouteKey(): mixed
    {
        return $this->user_code ?: $this->id;
    }

    /**
     * Retrieve the model for a bound value (supports both user_code and id).
     */
    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where('user_code', $value)
            ->orWhere('id', $value)
            ->first();
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
