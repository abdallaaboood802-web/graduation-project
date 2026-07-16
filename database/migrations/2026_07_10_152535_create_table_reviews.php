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
        Schema::create('table_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('table_users')->cascadeOnDelete();
            $table->foreignId('book_id')->constrained('table_books')->cascadeOnDelete();
            $table->unsignedTinyInteger('rating'); // أرقام صغيرة موجبة من 1 لـ 5
            $table->text('review_text')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_reviews');
    }
};
