<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Services\UniqueCodeGenerator;

class Logbook extends Model
{
    protected $fillable = [
        'logbook_code',
        'user_id',
        'tanggal',
        'kegiatan',
        'tags',
        'deskripsi',
        'status_approval',
        'catatan_pembimbing',
    ];

    /**
     * Auto-generate logbook_code when creating a new logbook entry.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $logbook) {
            if (empty($logbook->logbook_code)) {
                try {
                    $generator = app(UniqueCodeGenerator::class);
                    $logbook->logbook_code = $generator->generateLogbookCode();
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::error('Failed to generate logbook_code: ' . $e->getMessage());
                }
            }
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
        ];
    }

    /**
     * Use logbook_code as the route model binding key.
     * This hides the internal auto-increment `id` from URLs.
     */
    public function getRouteKeyName(): string
    {
        return 'logbook_code';
    }

    /**
     * Get the user who owns this logbook entry.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
