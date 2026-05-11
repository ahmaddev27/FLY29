<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    public function __construct(private AuthService $auth) {}

    public function showLinkRequest(): View
    {
        return view('auth.forgot-password');
    }

    public function sendLink(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);
        $this->auth->sendPasswordResetLink($request->input('email'));

        return back()->with('status', 'إذا كان البريد مسجلاً، سيصلك رابط إعادة التعيين خلال دقائق.');
    }

    public function showReset(string $token, Request $request): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'token'                 => ['required'],
            'email'                 => ['required', 'email'],
            'password'              => ['required', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['required'],
        ]);

        $ok = $this->auth->resetPassword($data);

        return $ok
            ? redirect()->route('login')->with('status', 'تم تعيين كلمة المرور. سجّل الدخول الآن.')
            : back()->withErrors(['email' => 'الرمز غير صحيح أو انتهت صلاحيته.']);
    }
}
