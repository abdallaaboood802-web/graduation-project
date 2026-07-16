<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration لتحسينات قاعدة البيانات:
 * 1. إضافة slug فريد لجداول books و categories
 * 2. إضافة email_verified_at لجدول users (إن لم يكن موجوداً)
 * 3. إضافة soft deletes لجداول users و books
 * 4. إنشاء جدول book_downloads لتتبع التحميلات
 *
 * ملاحظة: يستخدم Schema::hasColumn() للتحقق قبل الإضافة
 *         لتجنب خطأ "Column already exists" عند التشغيل أكثر من مرة.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ─── 1. تحسينات جدول users ──────────────────────────────────────────
        // Schema::table('table_users', function (Blueprint $table) {

        //     if (! Schema::hasColumn('table_users', 'email_verified_at')) {
        //         $table->timestamp('email_verified_at')->nullable()->after('email');
        //     }

        //     if (! Schema::hasColumn('table_users', 'deleted_at')) {
        //         $table->softDeletes()->after('avatar');
        //     }
        // });

        // ─── 2. تحسينات جدول categories ─────────────────────────────────────
        Schema::table('table_categories', function (Blueprint $table) {

            if (! Schema::hasColumn('table_categories', 'slug')) {
                $table->string('slug')->unique()->after('name');
            }

            if (! Schema::hasColumn('table_categories', 'created_at')) {
                $table->timestamps();
            }
        });

        // ─── 3. تحسينات جدول books ───────────────────────────────────────────
        Schema::table('table_books', function (Blueprint $table) {

            if (! Schema::hasColumn('table_books', 'slug')) {
                $table->string('slug')->unique()->after('title');
            }

            if (! Schema::hasColumn('table_books', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }
        });

        // ─── 4. إنشاء جدول book_downloads (إن لم يكن موجوداً) ───────────────
        if (! Schema::hasTable('table_book_downloads')) {
            Schema::create('table_book_downloads', function (Blueprint $table) {
                $table->id();
                $table->foreignId('book_id')->constrained('table_books')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('table_users')->nullOnDelete();
                $table->string('ip_address', 45)->nullable(); // دعم IPv6
                $table->timestamp('downloaded_at')->useCurrent();
                $table->timestamps();

                // منع تكرار التحميل من نفس المستخدم للكتاب نفسه
                $table->unique(['book_id', 'user_id'], 'unique_user_book_download');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('table_book_downloads');

        Schema::table('table_books', function (Blueprint $table) {
            if (Schema::hasColumn('table_books', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
            if (Schema::hasColumn('table_books', 'slug')) {
                $table->dropColumn('slug');
            }
        });

        Schema::table('table_categories', function (Blueprint $table) {
            $columns = [];
            foreach (['slug', 'created_at', 'updated_at'] as $col) {
                if (Schema::hasColumn('table_categories', $col)) {
                    $columns[] = $col;
                }
            }
            if (! empty($columns)) {
                $table->dropColumn($columns);
            }
        });

        Schema::table('table_users', function (Blueprint $table) {
            if (Schema::hasColumn('table_users', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
            if (Schema::hasColumn('table_users', 'email_verified_at')) {
                $table->dropColumn('email_verified_at');
            }
        });
    }
};
