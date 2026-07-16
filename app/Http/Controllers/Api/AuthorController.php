<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AuthorResource;
use App\Models\Author;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * متحكم المؤلفين – عرض قائمة المؤلفين وتفاصيلهم.
 */
class AuthorController extends Controller
{
    /**
     * GET /api/authors
     *
     * جلب قائمة جميع المؤلفين مع عدد كتبهم المعتمدة.
     */
    public function index(): AnonymousResourceCollection
    {
        $authors = Author::query()
            ->withCount(['books as approved_books_count' => function ($query) {
                $query->approved();
            }])
            ->having('approved_books_count', '>', 0)
            ->orderByDesc('approved_books_count')
            ->paginate(20);

        return AuthorResource::collection($authors);
    }

    /**
     * GET /api/authors/{id}
     *
     * جلب تفاصيل مؤلف مع كتبه المعتمدة.
     */
    public function show(Author $author): AuthorResource
    {
        $author->load([
            'books' => fn ($query) => $query->approved()->with('category')->latest(),
            'user',
        ]);

        return new AuthorResource($author);
    }
}
