<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\AgentWelcomeMail;
use App\Models\Agent;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AccountManagerController extends Controller
{
    public function __construct(private AuditService $audit) {}

    public function index(): View
    {
        $managers = User::where('role', 'account_manager')
            ->withCount('managedAgents')
            ->orderBy('full_name')
            ->paginate(25);

        return view('admin.account-managers.index', compact('managers'));
    }

    public function create(): View
    {
        return view('admin.account-managers.form', ['manager' => new User()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', Rule::unique('users', 'email')],
            'phone'     => ['nullable', 'string', 'max:30'],
        ]);

        $manager = User::create([
            'role'      => 'account_manager',
            'email'     => $data['email'],
            'password'  => Hash::make(Str::random(40)),
            'full_name' => $data['full_name'],
            'phone'     => $data['phone'] ?? null,
            'status'    => 'active',
        ]);

        // Re-use the agent welcome flow (same setup — password setup link)
        $token = Password::broker()->createToken($manager);
        $setupUrl = route('password.reset', [
            'token' => $token,
            'email' => $manager->email,
        ]);

        try {
            Mail::to($manager->email)->raw(
                "أهلاً {$manager->full_name}،\n\nتم إنشاء حسابك كمدير حسابات في 29FLY. عيّن كلمة المرور من الرابط:\n{$setupUrl}\n\nالرابط صالح لمدة 60 دقيقة.",
                fn ($m) => $m->to($manager->email)->subject('مرحباً بك في فريق 29FLY'),
            );
        } catch (\Throwable $e) {
            report($e);
        }

        $this->audit->log(
            action: 'account_manager_created',
            entityType: User::class,
            entityId: (string) $manager->id,
            newValues: $manager->getAttributes(),
        );

        return redirect()
            ->route('admin.account-managers')
            ->with('status', "تم إنشاء حساب مدير الحسابات «{$manager->full_name}» وأُرسل رابط تعيين كلمة المرور.");
    }

    public function show(User $manager): View
    {
        $this->ensureIsManager($manager);

        $assigned = $manager->managedAgents()
            ->with(['user', 'cashWallet', 'packageWallet'])
            ->latest()
            ->paginate(25);

        // Agents without a manager — available for assignment.
        $unassigned = Agent::whereNull('account_manager_id')
            ->whereHas('user', fn ($q) => $q->where('status', 'active'))
            ->orderBy('business_name')
            ->limit(50)
            ->get();

        return view('admin.account-managers.show', compact('manager', 'assigned', 'unassigned'));
    }

    public function assign(Request $request, User $manager): RedirectResponse
    {
        $this->ensureIsManager($manager);

        $data = $request->validate([
            'agent_ids'   => ['required', 'array', 'min:1'],
            'agent_ids.*' => ['integer', 'exists:agents,id'],
        ]);

        $count = Agent::whereIn('id', $data['agent_ids'])
            ->update(['account_manager_id' => $manager->id]);

        $this->audit->log(
            action: 'account_manager_assigned_agents',
            entityType: User::class,
            entityId: (string) $manager->id,
            newValues: ['agent_ids' => $data['agent_ids']],
        );

        return back()->with('status', "تم تعيين {$count} وكيل لـ «{$manager->full_name}».");
    }

    public function unassign(Request $request, User $manager, Agent $agent): RedirectResponse
    {
        $this->ensureIsManager($manager);

        if ($agent->account_manager_id !== $manager->id) {
            abort(404);
        }

        $agent->update(['account_manager_id' => null]);

        $this->audit->log(
            action: 'account_manager_unassigned_agent',
            entityType: User::class,
            entityId: (string) $manager->id,
            newValues: ['agent_id' => $agent->id],
        );

        return back()->with('status', "تم إزالة الوكيل «{$agent->business_name}» من قائمة «{$manager->full_name}».");
    }

    public function suspend(Request $request, User $manager): RedirectResponse
    {
        $this->ensureIsManager($manager);

        $data = $request->validate(['reason' => ['required', 'string', 'min:5', 'max:500']]);

        $manager->update(['status' => 'suspended']);

        $this->audit->log(
            action: 'account_manager_suspended',
            entityType: User::class,
            entityId: (string) $manager->id,
            newValues: ['reason' => $data['reason']],
        );

        return back()->with('status', "تم تعليق «{$manager->full_name}».");
    }

    public function unsuspend(User $manager): RedirectResponse
    {
        $this->ensureIsManager($manager);

        $manager->update(['status' => 'active']);

        $this->audit->log(
            action: 'account_manager_unsuspended',
            entityType: User::class,
            entityId: (string) $manager->id,
        );

        return back()->with('status', "تم تفعيل «{$manager->full_name}» مجدداً.");
    }

    public function destroy(User $manager): RedirectResponse
    {
        $this->ensureIsManager($manager);

        // Release any assigned agents before deletion to avoid orphaned FK refs.
        DB::transaction(function () use ($manager) {
            Agent::where('account_manager_id', $manager->id)
                ->update(['account_manager_id' => null]);

            $manager->update(['status' => 'deleted']);
            $manager->delete();
        });

        $this->audit->log(
            action: 'account_manager_deleted',
            entityType: User::class,
            entityId: (string) $manager->id,
        );

        return redirect()
            ->route('admin.account-managers')
            ->with('status', "تم حذف مدير الحسابات «{$manager->full_name}».");
    }

    private function ensureIsManager(User $user): void
    {
        if ($user->role !== 'account_manager') {
            abort(404);
        }
    }
}
