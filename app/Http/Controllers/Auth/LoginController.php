<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function __construct(private AuthService $auth) {}

    public function show(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $user = $this->auth->attemptLogin(
            email: $data['email'],
            password: $data['password'],
            remember: (bool) ($data['remember'] ?? false),
            ip: $request->ip(),
        );

        $request->session()->regenerate();

        return redirect()->intended($this->routeForRole($user->role));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $this->auth->logout($request->user());

        return redirect()->route('login')->with('status', 'تم تسجيل الخروج بنجاح.');
    }

    private function routeForRole(string $role): string
    {
        return match ($role) {
            'super_admin', 'admin' => '/admin/dashboard',
            'account_manager'      => '/manager/dashboard',
            default                => '/agent/dashboard',
        };
    }
}
