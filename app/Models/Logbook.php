<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'tanggal', 'kegiatan', 'tags', 'deskripsi', 'status_approval', 'catatan_pembimbing'])]
class Logbook extends Model
{
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
     * Get the user who owns this logbook entry.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
