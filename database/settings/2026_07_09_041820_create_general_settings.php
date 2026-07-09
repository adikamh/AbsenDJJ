<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.jam_masuk', '08:00:00');
        $this->migrator->add('general.jam_pulang', '17:00:00');
        $this->migrator->add('general.batas_keterlambatan', '08:15:00');
        $this->migrator->add('general.latitude_kantor', '-6.8988');
        $this->migrator->add('general.longitude_kantor', '107.6358');
        $this->migrator->add('general.radius_meter', 100);
    }
};
