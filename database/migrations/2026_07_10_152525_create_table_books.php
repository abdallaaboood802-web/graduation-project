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
        Schema::create('table_books', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('pdf_path');
            $table->integer('pages_count')->nullable();
            $table->string('file_size')->nullable();
            $table->string('language')->default('ar');
            
            // العلاقات
            $table->foreignId('category_id')->constrained('table_categories')->cascadeOnDelete();
            $table->foreignId('author_id')->constrained('table_authors')->cascadeOnDelete();
            $table->foreignId('uploader_id')->nullable()->constrained('table_users')->nullOnDelete();
            
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->integer('views_count')->default(0);
            $table->integer('downloads_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_books');
    }
};
