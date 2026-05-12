<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensures the authenticated user is an account_manager.
 */
class EnsureAccountManager
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! $user->isAccountManager()) {
            abort(403, 'هذه الصفحة متاحة لمدراء الحسابات فقط.');
        }

        return $next($request);
    }
}
