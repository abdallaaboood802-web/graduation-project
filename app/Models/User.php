<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * اسم الجدول في قاعدة البيانات.
     */
    protected $table = 'table_users';

    /**
     * الحقول التي يُسمح بتعيينها جماعياً (Mass Assignment).
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'email',
        'password_hash',
        'role',
        'bio',
        'avatar',
        'email_verified_at',
    ];

    /**
     * الحقول المخفية عند تحويل النموذج إلى مصفوفة أو JSON.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    /**
     * تحويل أنواع الحقول (Casts).
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password_hash'     => 'hashed',
            'role'              => UserRole::class,
        ];
    }

    // =========================================================================
    // العلاقات (Relationships)
    // =========================================================================

    /**
     * الكتب التي رفعها هذا المستخدم.
     */
    public function uploadedBooks(): HasMany
    {
        return $this->hasMany(Book::class, 'uploader_id');
    }

    /**
     * التقييمات التي كتبها المستخدم.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'user_id');
    }

    /**
     * الاقتباسات التي أضافها المستخدم.
     */
    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class, 'user_id');
    }

    /**
     * مكتبة الكتب الخاصة بالمستخدم (Bookshelf).
     */
    public function bookshelf(): HasMany
    {
        return $this->hasMany(UserBookshelf::class, 'user_id');
    }

    /**
     * الكتب الموجودة في مكتبة المستخدم عبر Pivot.
     */
    public function shelfBooks(): BelongsToMany
    {
        return $this->belongsToMany(Book::class, 'table_user_bookshelves', 'user_id', 'book_id')
                    ->withPivot('status')
                    ->withTimestamps();
    }

    /**
     * سجل تحميلات الكتب من قِبل هذا المستخدم.
     */
    public function bookDownloads(): HasMany
    {
        return $this->hasMany(BookDownload::class, 'user_id');
    }

    /**
     * حساب المؤلف المرتبط بهذا المستخدم (إن وُجد).
     */
    public function authorProfile(): HasOne
    {
        return $this->hasOne(Author::class, 'user_id');
    }

    /**
     * المتابَعون (الأشخاص الذين يتابعهم هذا المستخدم).
     */
    public function following(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'table_followers',
            'follower_id',
            'following_id'
        )->withTimestamps();
    }

    /**
     * المتابِعون (الأشخاص الذين يتابعون هذا المستخدم).
     */
    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'table_followers',
            'following_id',
            'follower_id'
        )->withTimestamps();
    }

    // =========================================================================
    // Helper Methods
    // =========================================================================

    /**
     * التحقق مما إذا كان المستخدم يملك صلاحية رفع الكتب.
     */
    public function canUploadBooks(): bool
    {
        return $this->role->canUploadBooks();
    }

    /**
     * التحقق مما إذا كان المستخدم مديراً أو مشرفاً.
     */
    public function isAdmin(): bool
    {
        return $this->role->isAdmin();
    }
 public function books(): HasMany
{
    // البارامتر الثاني: 'uploader_id' هو العمود الفعلي في جدول الكتب لديك
    // البارامتر الثالث: 'id' هو المفتاح الرئيسي لجدول المستخدمين
    return $this->hasMany(Book::class, 'uploader_id', 'id');
}
}
