<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * متحكم الأقسام – عرض شجرة الأقسام والفئات.
 */
class CategoryController extends Controller
{
    /**
     * GET /api/categories
     *
     * جلب الأقسام الرئيسية مع أقسامها الفرعية وعدد الكتب في كل قسم.
     */
    public function index(): AnonymousResourceCollection
    {
        $categories = Category::query()
            ->root()
            ->with(['children' => function ($query) {
                $query->withCount(['books as approved_books_count' => fn ($q) => $q->approved()]);
            }])
            ->withCount(['books as approved_books_count' => fn ($q) => $q->approved()])
            ->orderBy('name')
            ->get();

        return CategoryResource::collection($categories);
    }

    /**
     * GET /api/categories/{slug}
     *
     * جلب تفاصيل قسم مع كتبه المعتمدة.
     */
    public function show(string $slug): CategoryResource
    {
        $category = Category::where('slug', $slug)
            ->with(['parent', 'children'])
            ->withCount(['books as approved_books_count' => fn ($q) => $q->approved()])
            ->firstOrFail();

        return new CategoryResource($category);
    }
}
