<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(
        private SettingsService $settings,
        private AuditService $audit,
    ) {}

    /**
     * Attempt to log a user in.
     *
     * @throws ValidationException on auth failure.
     */
    public function attemptLogin(string $email, string $password, bool $remember = false, ?string $ip = null): User
    {
        $user = User::where('email', $email)->first();

        // Don't reveal whether email exists — use generic message.
        if (! $user) {
            $this->audit->logAuth('login_failed', null, ['email' => $email, 'reason' => 'unknown_email']);
            throw ValidationException::withMessages(['email' => __('بيانات تسجيل الدخول غير صحيحة.')]);
        }

        if ($user->isLocked()) {
            $this->audit->logAuth('login_blocked', $user->id, ['reason' => 'locked']);
            throw ValidationException::withMessages([
                'email' => __('حسابك مقفل مؤقتاً، يرجى المحاولة لاحقاً.'),
            ]);
        }

        if (! $user->isActive()) {
            $this->audit->logAuth('login_blocked', $user->id, ['reason' => 'inactive', 'status' => $user->status]);
            throw ValidationException::withMessages([
                'email' => __('حسابك معلق، تواصل مع مدير حسابك.'),
            ]);
        }

        if (! Hash::check($password, $user->password)) {
            $this->handleFailedAttempt($user);
            throw ValidationException::withMessages(['email' => __('بيانات تسجيل الدخول غير صحيحة.')]);
        }

        // Success
        $this->handleSuccessfulLogin($user, $ip);
        Auth::login($user, $remember);

        return $user;
    }

    public function logout(?User $user = null): void
    {
        $user ??= auth()->user();
        if ($user) {
            $this->audit->logAuth('logout', $user->id);
        }
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
    }

    public function sendPasswordResetLink(string $email): bool
    {
        // Always return true to avoid email enumeration AND to keep the
        // page resilient when SMTP is misconfigured (e.g. Resend testing
        // mode rejects unverified domains, network errors, etc.).
        try {
            $status = Password::sendResetLink(['email' => $email]);
        } catch (\Throwable $e) {
            // Log + swallow — we don't want a 500 on the forgot-password page.
            \Illuminate\Support\Facades\Log::error('Password reset mail failed: ' . $e->getMessage(), [
                'email' => $email,
            ]);
            $status = Password::INVALID_USER; // any non-RESET_LINK_SENT value
        }

        $user = User::where('email', $email)->first();
        if ($user) {
            $this->audit->logAuth('password_reset_requested', $user->id);
        }

        return $status === Password::RESET_LINK_SENT;
    }

    public function resetPassword(array $data): bool
    {
        $status = Password::reset(
            $data,
            function (User $user, string $password) {
                $user->forceFill([
                    'password'              => Hash::make($password),
                    'remember_token'        => Str::random(60),
                    'failed_login_attempts' => 0,
                    'locked_until'          => null,
                ])->save();

                $this->audit->logAuth('password_reset_completed', $user->id);
            }
        );

        return $status === Password::PASSWORD_RESET;
    }

    private function handleFailedAttempt(User $user): void
    {
        $user->increment('failed_login_attempts');

        $maxAttempts = (int) $this->settings->get('login_max_attempts', 5);
        $lockoutMin  = (int) $this->settings->get('login_lockout_minutes', 15);

        if ($user->failed_login_attempts >= $maxAttempts) {
            $user->update(['locked_until' => now()->addMinutes($lockoutMin)]);
            $this->audit->logAuth('account_locked', $user->id, [
                'attempts' => $user->failed_login_attempts,
                'until'    => $user->locked_until,
            ]);
        } else {
            $this->audit->logAuth('login_failed', $user->id, [
                'attempts' => $user->failed_login_attempts,
            ]);
        }
    }

    private function handleSuccessfulLogin(User $user, ?string $ip): void
    {
        $user->update([
            'failed_login_attempts' => 0,
            'locked_until'          => null,
            'last_login_at'         => now(),
            'last_login_ip'         => $ip ?? request()->ip(),
        ]);

        $this->audit->logAuth('login_success', $user->id);
    }
}
