<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(private AuditService $audit) {}

    public function show(Request $request): View
    {
        $agent = $request->user()->agent
            ?? abort(404);

        return view('agent.profile', compact('agent'));
    }

    public function update(Request $request): RedirectResponse
    {
        $user  = $request->user();
        $agent = $user->agent ?? abort(404);

        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'phone'     => ['nullable', 'string', 'max:30'],
            'city'      => ['nullable', 'string', 'max:100'],
            // email و license_number ممنوع تعديلهم (يتطلب موافقة الأدمن)
        ]);

        $oldValues = $user->only(['full_name', 'phone']) + $agent->only(['city']);

        $user->update([
            'full_name' => $data['full_name'],
            'phone'     => $data['phone'] ?? null,
        ]);

        $agent->update(['city' => $data['city'] ?? null]);

        $this->audit->log(
            action: 'profile_updated',
            entityType: 'agent',
            entityId: (string) $agent->id,
            oldValues: $oldValues,
            newValues: $data,
        );

        return back()->with('status', 'تم حفظ التغييرات بنجاح.');
    }

    /**
     * Change password (requires current).
     */
    public function password(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'current_password' => ['required'],
            'password'         => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (! Hash::check($data['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'كلمة المرور الحالية غير صحيحة.',
            ]);
        }

        $user->update(['password' => Hash::make($data['password'])]);

        $this->audit->log(
            action: 'password_changed',
            entityType: 'user',
            entityId: (string) $user->id,
        );

        return back()->with('status', 'تم تغيير كلمة المرور.');
    }
}
