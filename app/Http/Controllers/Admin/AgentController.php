<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\User;
use App\Services\AuditService;
use App\Services\MainSiteApiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AgentController extends Controller
{
    public function __construct(private AuditService $audit) {}

    /*
    |--------------------------------------------------------------------------
    | Index — list + filters
    |--------------------------------------------------------------------------
    */
    public function index(Request $request): View
    {
        $query = Agent::with('user')->latest();

        if ($q = $request->query('q')) {
            $query->where(function ($qb) use ($q) {
                $qb->where('business_name', 'like', "%{$q}%")
                   ->orWhere('external_agent_id', 'like', "%{$q}%")
                   ->orWhere('license_number', 'like', "%{$q}%")
                   ->orWhereHas('user', fn ($u) => $u
                        ->where('email', 'like', "%{$q}%")
                        ->orWhere('full_name', 'like', "%{$q}%"));
            });
        }

        if ($tier = $request->query('tier')) {
            $query->where('current_tier', $tier);
        }

        if ($country = $request->query('country')) {
            $query->where('country', $country);
        }

        if ($status = $request->query('status')) {
            $query->whereHas('user', fn ($u) => $u->where('status', $status));
        }

        $perPage = (int) $request->query('per_page', 25);
        $perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 25;

        $agents = $query->paginate($perPage);

        // Filter options
        $countries = Agent::query()->distinct()->pluck('country')->filter()->mapWithKeys(fn ($c) => [$c => $c])->toArray();

        return view('admin.agents.index', compact('agents', 'countries'));
    }

    /*
    |--------------------------------------------------------------------------
    | Show — agent profile (tabs)
    |--------------------------------------------------------------------------
    */
    public function show(Agent $agent): View
    {
        $agent->load(['user', 'accountManager', 'cashWallet', 'packageWallet', 'tierLevel']);

        $recentTxns = $agent->transactions()->latest('transaction_date')->limit(25)->get();
        $recentRedemptions = $agent->redemptions()->with('package')->latest('requested_at')->limit(20)->get();
        $tierHistory = $agent->tierHistory()->orderByDesc('created_at')->limit(10)->get();

        return view('admin.agents.show', compact('agent', 'recentTxns', 'recentRedemptions', 'tierHistory'));
    }

    /*
    |--------------------------------------------------------------------------
    | Create + Store
    |--------------------------------------------------------------------------
    */
    public function create(): View
    {
        return view('admin.agents.form', ['agent' => new Agent(), 'user' => new User()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'full_name'         => ['required', 'string', 'max:255'],
            'email'             => ['required', 'email', Rule::unique('users', 'email')],
            'phone'             => ['nullable', 'string', 'max:30'],
            'external_agent_id' => ['required', 'string', 'max:50', Rule::unique('agents', 'external_agent_id')],
            'business_name'     => ['required', 'string', 'max:255'],
            'license_number'    => ['required', 'string', 'max:100', Rule::unique('agents', 'license_number')],
            'country'           => ['required', 'string', 'max:100'],
            'city'              => ['nullable', 'string', 'max:100'],
            'current_tier'      => ['nullable', 'in:bronze,silver,gold,diamond'],
        ]);

        $agent = DB::transaction(function () use ($data) {
            // Create user with a random password — the welcome email asks
            // them to set their own via the password-reset flow.
            $user = User::create([
                'role'      => 'agent',
                'email'     => $data['email'],
                'password'  => Hash::make(Str::random(40)),
                'full_name' => $data['full_name'],
                'phone'     => $data['phone'] ?? null,
                'status'    => 'active',
            ]);

            $agent = Agent::create([
                'user_id'           => $user->id,
                'external_agent_id' => $data['external_agent_id'],
                'business_name'     => $data['business_name'],
                'license_number'    => $data['license_number'],
                'country'           => $data['country'],
                'city'              => $data['city'] ?? null,
                'current_tier'      => $data['current_tier'] ?? 'bronze',
                'tier_valid_until'  => now()->addDays(30),
            ]);

            \App\Models\CashWalletPoints::create(['agent_id' => $agent->id]);
            \App\Models\PackageWalletPoints::create(['agent_id' => $agent->id]);

            return $agent;
        });

        // Fire the password-setup email (Laravel's built-in reset link)
        Password::sendResetLink(['email' => $agent->user->email]);

        $this->audit->log(
            action: 'agent_created',
            entityType: Agent::class,
            entityId: (string) $agent->id,
            newValues: $agent->fresh()->getAttributes(),
        );

        return redirect()
            ->route('admin.agents.show', $agent)
            ->with('status', "تم إنشاء حساب الوكيل «{$agent->business_name}» وإرسال رابط تعيين كلمة المرور لـ {$agent->user->email}.");
    }

    /*
    |--------------------------------------------------------------------------
    | Lifecycle actions
    |--------------------------------------------------------------------------
    */
    public function suspend(Request $request, Agent $agent): RedirectResponse
    {
        $data = $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        $oldStatus = $agent->user->status;
        $agent->user->update(['status' => 'suspended']);

        $this->audit->log(
            action: 'agent_suspended',
            entityType: Agent::class,
            entityId: (string) $agent->id,
            oldValues: ['status' => $oldStatus],
            newValues: ['status' => 'suspended', 'reason' => $data['reason']],
        );

        return back()->with('status', "تم تعليق حساب «{$agent->business_name}».");
    }

    public function unsuspend(Agent $agent): RedirectResponse
    {
        $oldStatus = $agent->user->status;
        $agent->user->update(['status' => 'active']);

        $this->audit->log(
            action: 'agent_unsuspended',
            entityType: Agent::class,
            entityId: (string) $agent->id,
            oldValues: ['status' => $oldStatus],
            newValues: ['status' => 'active'],
        );

        return back()->with('status', "تم تفعيل حساب «{$agent->business_name}» مجدداً.");
    }

    public function destroy(Agent $agent): RedirectResponse
    {
        $name = $agent->business_name;

        $agent->user->update(['status' => 'deleted']);
        $agent->user->delete(); // soft delete via SoftDeletes trait

        $this->audit->log(
            action: 'agent_deleted',
            entityType: Agent::class,
            entityId: (string) $agent->id,
        );

        return redirect()->route('admin.agents')->with('status', "تم حذف الوكيل «{$name}» (محفوظ في الأرشيف لـ 90 يوم).");
    }

    /**
     * Internal notes (admin-only, hidden from agent).
     */
    public function updateNotes(Request $request, Agent $agent): RedirectResponse
    {
        $data = $request->validate([
            'internal_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $agent->update(['internal_notes' => $data['internal_notes']]);

        $this->audit->log(
            action: 'agent_notes_updated',
            entityType: Agent::class,
            entityId: (string) $agent->id,
        );

        return back()->with('status', 'تم حفظ الملاحظات.');
    }
}
