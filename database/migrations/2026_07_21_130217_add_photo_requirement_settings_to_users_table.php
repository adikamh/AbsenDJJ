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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('require_photo_attendance_global')->default(true)->after('auto_approve_logbook_global');
            $table->boolean('require_photo_attendance')->default(true)->after('auto_approve_logbook');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['require_photo_attendance_global', 'require_photo_attendance']);
        });
    }
};
