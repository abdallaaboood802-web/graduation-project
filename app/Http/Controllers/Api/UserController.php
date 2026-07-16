<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookResource;
use App\Http\Resources\UserResource;
use App\Models\Quote;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * متحكم الملف الشخصي – عرض بيانات المستخدمين العامة وإدارة المتابعين.
 */
class UserController extends Controller
{
    /**
     * GET /api/v1/users/{username}
     *
     * جلب الملف الشخصي العام لمستخدم ما.
     */
    public function show(string $username): JsonResponse
    {
        $user = User::where('username', $username)
            ->with(['authorProfile', 'uploadedBooks' => fn ($q) => $q->approved()])
            ->withCount(['followers', 'following', 'reviews', 'quotes'])
            ->firstOrFail();

        return response()->json([
            'data' => [
                ...(new UserResource($user))->toArray(request()),
                'author_profile'  => $user->authorProfile,
                'followers_count' => $user->followers_count,
                'following_count' => $user->following_count,
                'reviews_count'   => $user->reviews_count,
                'quotes_count'    => $user->quotes_count,
                'is_following'    => request()->user()
                    ? $user->followers()->where('follower_id', request()->user()->id)->exists()
                    : false,
            ],
        ]);
    }

    /**
     * GET /api/v1/users/{username}/books
     *
     * جلب الكتب المرفوعة من مستخدم معين (المعتمدة فقط).
     */
    public function books(string $username): AnonymousResourceCollection
    {
        $user = User::where('username', $username)->firstOrFail();

        $books = $user->uploadedBooks()
            ->approved()
            ->with(['author', 'category'])
            ->withCount('reviews')
            ->latest()
            ->paginate(15);

        return BookResource::collection($books);
    }

    /**
     * POST /api/v1/users/{username}/follow
     *
     * متابعة / إلغاء متابعة مستخدم (Toggle).
     */
    public function toggleFollow(string $username, Request $request): JsonResponse
    {
        $targetUser = User::where('username', $username)->firstOrFail();
        $authUser   = $request->user();

        if ($targetUser->id === $authUser->id) {
            return response()->json([
                'message' => 'لا يمكنك متابعة نفسك.',
            ], 422);
        }

        $isFollowing = $authUser->following()->where('following_id', $targetUser->id)->exists();

        if ($isFollowing) {
            $authUser->following()->detach($targetUser->id);
            $message = "تم إلغاء متابعة {$targetUser->username}.";
            $action  = 'unfollowed';
        } else {
            $authUser->following()->attach($targetUser->id);
            $message = "أنت الآن تتابع {$targetUser->username}.";
            $action  = 'followed';
        }

        return response()->json([
            'message'         => $message,
            'action'          => $action,
            'followers_count' => $targetUser->followers()->count(),
        ]);
    }

    /**
     * GET /api/v1/users/{username}/quotes
     *
     * جلب الاقتباسات العامة لمستخدم ما.
     */
    public function quotes(string $username): JsonResponse
    {
        $user = User::where('username', $username)->firstOrFail();

        $quotes = $user->quotes()
            ->with('book:id,title,slug,cover_image')
            ->latest()
            ->paginate(20);

        return response()->json(['data' => $quotes]);
    }
}
