<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkSchedule extends Model
{
    protected $fillable = [
        'type',
        'day_of_week',
        'specific_date',
        'jam_masuk',
        'batas_keterlambatan',
        'jam_pulang',
        'is_holiday',
        'keterangan',
    ];

    protected $casts = [
        'day_of_week' => 'integer',
        'specific_date' => 'date',
        'is_holiday' => 'boolean',
    ];

    /**
     * Get the schedule applicable for a given date with priority:
     * 1. Specific date override
     * 2. Day-of-week override
     * 3. null (caller should fall back to GeneralSettings defaults)
     */
    public static function getScheduleForDate(\Carbon\Carbon $date): ?self
    {
        // Priority 1: Specific date override
        $dateOverride = static::where('type', 'date')
            ->where('specific_date', $date->toDateString())
            ->first();

        if ($dateOverride) {
            return $dateOverride;
        }

        // Priority 2: Day-of-week override (Carbon: 0=Sunday, 6=Saturday)
        $dayOverride = static::where('type', 'day')
            ->where('day_of_week', $date->dayOfWeek)
            ->first();

        if ($dayOverride) {
            return $dayOverride;
        }

        // Priority 3: No override — caller uses GeneralSettings defaults
        return null;
    }

    /**
     * Get all day-of-week overrides indexed by day number.
     */
    public static function getDayOverrides(): array
    {
        return static::where('type', 'day')
            ->get()
            ->keyBy('day_of_week')
            ->toArray();
    }

    /**
     * Get all specific-date overrides ordered by date.
     */
    public static function getDateOverrides()
    {
        return static::where('type', 'date')
            ->orderBy('specific_date')
            ->get();
    }

    /**
     * Day-of-week label helper.
     */
    public static function dayLabel(int $day): string
    {
        $labels = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        return $labels[$day] ?? '-';
    }
}
