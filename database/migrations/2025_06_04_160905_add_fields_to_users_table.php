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
        Schema::table('users', function (Blueprint $table) {
            // Add division_id as nullable first
            $table->unsignedBigInteger('division_id')->nullable();
            $table->enum('role', ['admin', 'moderator', 'user'])->default('user');
        });

        // Create a default division if none exists
        $defaultDivision = DB::table('divisions')->first();
        if (!$defaultDivision) {
            $defaultDivisionId = DB::table('divisions')->insertGetId([
                'name' => 'General',
                'description' => 'Default division for existing users',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $defaultDivisionId = $defaultDivision->id;
        }

        // Update existing users to have the default division
        DB::table('users')->whereNull('division_id')->update(['division_id' => $defaultDivisionId]);

        // Now make division_id NOT NULL and add the foreign key constraint
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('division_id')->nullable(false)->change();
            $table->foreign('division_id')->references('id')->on('divisions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['division_id']);
            $table->dropColumn(['division_id', 'role']);
        });
    }
};
