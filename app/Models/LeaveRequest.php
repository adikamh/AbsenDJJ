<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'tanggal_mulai', 'tanggal_selesai', 'jenis', 'alasan', 'file_bukti', 'status_approval', 'catatan_pembimbing'])]
class LeaveRequest extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
        ];
    }

    /**
     * Get the user who owns this leave request.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
