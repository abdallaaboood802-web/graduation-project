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
        Schema::create('table_users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable(); // ← مضاف هنا مباشرة
            $table->string('password_hash');
            $table->enum('role', ['reader', 'author', 'moderator', 'admin'])->default('reader');
            $table->text('bio')->nullable();
            $table->string('avatar')->nullable();
            $table->softDeletes();                              // ← deleted_at هنا أيضاً
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_users');
    }
};
