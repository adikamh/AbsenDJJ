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
        Schema::create('logbooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            $table->date('tanggal');
            $table->string('kegiatan'); // Judul kegiatan harian
            $table->text('deskripsi'); // Detail pekerjaan
            
            $table->string('status_approval')->default('Pending'); // 'Pending', 'Approved', 'Rejected'
            $table->text('catatan_pembimbing')->nullable(); // Komentar atau revisi dari Admin
            
            $table->timestamps();

            // Compound index for timeline queries
            $table->index(['user_id', 'tanggal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logbooks');
    }
};
