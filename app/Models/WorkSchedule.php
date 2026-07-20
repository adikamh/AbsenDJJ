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
        'specific_date' => 'date:Y-m-d',
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

    /**
     * Write/update a static today_holiday.json file inside the public/ directory.
     * This avoids booting Laravel and hitting the database on every 5s polling check.
     */
    public static function updateTodayHolidayCacheFile()
    {
        $now = \Carbon\Carbon::now();
        $schedule = static::getScheduleForDate($now);
        $settings = app(\App\Settings\GeneralSettings::class);
        
        $isHoliday = false;
        $holidayName = null;
        if ($schedule) {
            $isHoliday = $schedule->is_holiday;
            $holidayName = $schedule->keterangan ?? 'Hari Libur';
        }

        $jamMasukRaw = ($schedule && $schedule->jam_masuk) ? $schedule->jam_masuk : $settings->jam_masuk;
        $jamPulangRaw = ($schedule && $schedule->jam_pulang) ? $schedule->jam_pulang : $settings->jam_pulang;
        $batasKeterlambatanRaw = ($schedule && $schedule->batas_keterlambatan) ? $schedule->batas_keterlambatan : $settings->batas_keterlambatan;
        
        $data = [
            'date' => $now->toDateString(),
            'is_holiday' => $isHoliday,
            'holiday_name' => $holidayName,
            'jam_masuk' => $jamMasukRaw ? \Carbon\Carbon::parse($jamMasukRaw)->format('H:i') : null,
            'jam_pulang' => $jamPulangRaw ? \Carbon\Carbon::parse($jamPulangRaw)->format('H:i') : null,
            'batas_keterlambatan' => $batasKeterlambatanRaw ? \Carbon\Carbon::parse($batasKeterlambatanRaw)->format('H:i') : null,
            'updated_at' => time()
        ];
        
        @file_put_contents(public_path('today_holiday.json'), json_encode($data));
    }

    /**
     * Send holiday notification to all participants.
     */
    public static function sendHolidayNotification(string $dateStr, ?string $keterangan)
    {
        $pesertaUsers = \App\Models\User::whereHas('role', function($q) {
            $q->where('nama_role', 'peserta');
        })->get();
        
        $formattedDate = \Carbon\Carbon::parse($dateStr)->translatedFormat('l, d F Y');
        $title = 'Hari Libur Ditetapkan';
        $message = "Admin menetapkan hari {$formattedDate} sebagai hari libur (" . ($keterangan ?? 'Libur') . "). Absensi ditiadakan.";
        
        foreach ($pesertaUsers as $user) {
            $user->notify(new \App\Notifications\AbsenNotification($title, $message, 'holiday'));
        }
    }

    /**
     * Send weekly holiday override notification to all participants.
     */
    public static function sendWeeklyHolidayNotification(int $dayOfWeek, ?string $keterangan)
    {
        $pesertaUsers = \App\Models\User::whereHas('role', function($q) {
            $q->where('nama_role', 'peserta');
        })->get();
        
        $dayName = static::dayLabel($dayOfWeek);
        $title = 'Jadwal Libur Mingguan';
        $message = "Admin menetapkan hari {$dayName} sebagai hari libur mingguan (" . ($keterangan ?? 'Libur') . ").";
        
        foreach ($pesertaUsers as $user) {
            $user->notify(new \App\Notifications\AbsenNotification($title, $message, 'holiday'));
        }
    }
}
