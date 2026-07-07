<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            $table->date('tanggal');
            $table->time('jam_masuk')->nullable();
            $table->time('jam_pulang')->nullable();
            
            $table->string('koordinat_masuk')->nullable(); // Latitude & Longitude saat datang
            $table->string('koordinat_pulang')->nullable(); // Latitude & Longitude saat pulang
            $table->string('foto_masuk')->nullable(); // Path file foto selfie masuk
            $table->string('foto_pulang')->nullable(); // Path file foto selfie pulang
            
            $table->string('status'); // 'Hadir', 'Terlambat', 'Izin', 'Sakit', 'Tanpa Keterangan'
            
            $table->timestamps();

            // Compound index for optimizing daily attendance checks
            $table->index(['user_id', 'tanggal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
