<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create code_sequences table for SEQ counters
        Schema::create('code_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->unsignedBigInteger('value')->default(0);
            $table->timestamps();
        });

        // Seed initial counters
        DB::table('code_sequences')->insert([
            ['key' => 'user_code_seq',    'value' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'logbook_code_seq', 'value' => 0, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 2. Add user_code to users
        Schema::table('users', function (Blueprint $table) {
            $table->string('user_code', 60)->nullable()->unique()->after('id');
        });

        // 3. Add logbook_code to logbooks
        Schema::table('logbooks', function (Blueprint $table) {
            $table->string('logbook_code', 60)->nullable()->unique()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('logbooks', function (Blueprint $table) {
            $table->dropUnique(['logbook_code']);
            $table->dropColumn('logbook_code');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['user_code']);
            $table->dropColumn('user_code');
        });

        Schema::dropIfExists('code_sequences');
    }
};
