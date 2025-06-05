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
        Schema::table('book_reviews', function (Blueprint $table) {
            // Drop the content column
            $table->dropColumn('content');
            
            // Add rating and comment columns
            $table->integer('rating')->after('book_id');
            $table->text('comment')->after('rating');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('book_reviews', function (Blueprint $table) {
            // Drop rating and comment columns
            $table->dropColumn(['rating', 'comment']);
            
            // Add back content column
            $table->text('content')->after('book_id');
        });
    }
};
