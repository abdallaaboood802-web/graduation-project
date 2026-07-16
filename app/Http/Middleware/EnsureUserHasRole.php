<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware للتحقق من دور المستخدم (Role-Based Access Control).
 *
 * الاستخدام في Routes:
 *   ->middleware('role:admin')
 *   ->middleware('role:admin,moderator')
 */
class EnsureUserHasRole
{
    /**
     * معالجة الطلب الوارد.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles  الأدوار المسموح بها (يكفي توافق دور واحد)
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $allowedRoles = array_map(
            fn (string $role) => UserRole::from($role),
            $roles
        );

        if (! in_array($user->role, $allowedRoles, strict: true)) {
            return response()->json([
                'message' => 'Forbidden. You do not have the required role to perform this action.',
                'required_roles' => $roles,
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
