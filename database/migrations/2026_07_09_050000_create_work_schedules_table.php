<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_schedules', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['day', 'date'])->comment('day = hari dalam seminggu, date = tanggal spesifik');
            $table->tinyInteger('day_of_week')->nullable()->comment('0=Minggu, 1=Senin, ..., 6=Sabtu');
            $table->date('specific_date')->nullable();
            $table->time('jam_masuk')->nullable();
            $table->time('batas_keterlambatan')->nullable();
            $table->time('jam_pulang')->nullable();
            $table->boolean('is_holiday')->default(false);
            $table->string('keterangan')->nullable();
            $table->timestamps();

            $table->unique('day_of_week');
            $table->unique('specific_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_schedules');
    }
};
