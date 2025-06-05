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
        Schema::table('books', function (Blueprint $table) {
            // Drop the foreign key constraint first
            $table->dropForeign(['division_id']);
            // Then drop the column
            $table->dropColumn('division_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            // Re-add the division_id column
            $table->foreignId('division_id')->after('cover_image')->constrained()->cascadeOnDelete();
        });
    }
};
