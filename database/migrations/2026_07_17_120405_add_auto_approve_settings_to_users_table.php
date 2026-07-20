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
            // For supervisors (pembimbing)
            $table->boolean('auto_approve_logbook_global')->default(false)->after('role_id');
            
            // For students (peserta)
            $table->boolean('auto_approve_logbook')->default(false)->after('pembimbing_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['auto_approve_logbook_global', 'auto_approve_logbook']);
        });
    }
};
