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
        Schema::create('table_followers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('follower_id')->constrained('table_users')->cascadeOnDelete();
            $table->foreignId('following_id')->constrained('table_users')->cascadeOnDelete();
            $table->timestamps();
            
            // لمنع تكرار متابعة نفس الشخص أكثر من مرة
            $table->unique(['follower_id', 'following_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_followers');
    }
};
