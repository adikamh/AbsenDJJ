<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('office_locations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('latitude');
            $table->string('longitude');
            $table->integer('radius');
            $table->timestamps();
        });

        // Seed initial coordinate from Spatie Settings if exists
        try {
            $settings = app(\App\Settings\GeneralSettings::class);
            if ($settings && !empty($settings->latitude_kantor) && !empty($settings->longitude_kantor)) {
                DB::table('office_locations')->insert([
                    'name' => 'Kantor Utama (Default)',
                    'latitude' => $settings->latitude_kantor,
                    'longitude' => $settings->longitude_kantor,
                    'radius' => $settings->radius_meter ?? 380,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        } catch (\Exception $e) {
            // Silently ignore if settings class or table is not loaded/configured yet
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('office_locations');
    }
};
