<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'tanggal', 'jam_masuk', 'jam_pulang', 'koordinat_masuk', 'koordinat_pulang', 'foto_masuk', 'foto_pulang', 'status'])]
class Attendance extends Model
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
            'jam_masuk' => 'datetime:H:i:s',
            'jam_pulang' => 'datetime:H:i:s',
        ];
    }

    /**
     * Get the user who owns this attendance record.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get calculated check-in distance in meters dynamically.
     */
    public function getJarakMeterMasukAttribute()
    {
        if (!$this->koordinat_masuk) {
            return null;
        }

        $settings = app(\App\Settings\GeneralSettings::class);
        $officeLat = (float) $settings->latitude_kantor;
        $officeLng = (float) $settings->longitude_kantor;

        return $this->calculateHaversineDistance($this->koordinat_masuk, $officeLat, $officeLng);
    }

    /**
     * Get calculated check-out distance in meters dynamically.
     */
    public function getJarakMeterPulangAttribute()
    {
        if (!$this->koordinat_pulang) {
            return null;
        }

        $settings = app(\App\Settings\GeneralSettings::class);
        $officeLat = (float) $settings->latitude_kantor;
        $officeLng = (float) $settings->longitude_kantor;

        return $this->calculateHaversineDistance($this->koordinat_pulang, $officeLat, $officeLng);
    }

    /**
     * Calculate distance using Haversine formula between coordinate string and office coordinates.
     */
    private function calculateHaversineDistance($koordinatUser, $latOffice, $lngOffice)
    {
        $parts = explode(',', $koordinatUser);
        if (count($parts) < 2) {
            return null;
        }

        $latUser = (float) trim($parts[0]);
        $lngUser = (float) trim($parts[1]);

        $earthRadius = 6371000; // Radius of the earth in meters

        $latDelta = deg2rad($latUser - $latOffice);
        $lonDelta = deg2rad($lngUser - $lngOffice);

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos(deg2rad($latOffice)) * cos(deg2rad($latUser)) *
             sin($lonDelta / 2) * sin($lonDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
