<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensures the authenticated user is an agent (role=agent + has Agent record).
 * Redirects other roles to their own dashboards.
 */
class EnsureAgent
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->isAdmin()) {
            return redirect('/admin/dashboard');
        }

        if ($user->isAccountManager()) {
            return redirect('/manager/dashboard');
        }

        if (! $user->isAgent() || ! $user->agent) {
            abort(403, 'هذه الصفحة متاحة لحسابات الوكلاء فقط.');
        }

        return $next($request);
    }
}
