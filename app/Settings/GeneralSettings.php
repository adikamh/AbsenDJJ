<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    public string $jam_masuk;
    public string $jam_pulang;
    public string $batas_keterlambatan;
    public string $latitude_kantor;
    public string $longitude_kantor;
    public int $radius_meter;

    public static function group(): string
    {
        return 'general';
    }
}