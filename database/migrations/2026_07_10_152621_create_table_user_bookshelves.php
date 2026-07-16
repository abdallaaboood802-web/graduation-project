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
        Schema::create('table_user_bookshelves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('table_users')->cascadeOnDelete();
            $table->foreignId('book_id')->constrained('table_books')->cascadeOnDelete();
            $table->enum('status', ['want_to_read', 'reading', 'read']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_user_bookshelves');
    }
};
