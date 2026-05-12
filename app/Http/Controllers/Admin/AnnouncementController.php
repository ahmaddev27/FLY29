<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\AnnouncementMail;
use App\Models\Agent;
use App\Models\Announcement;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function __construct(private AuditService $audit) {}

    public function index(): View
    {
        $announcements = Announcement::with('creator')
            ->withCount('reads')
            ->latest()
            ->paginate(25);

        return view('admin.announcements.index', compact('announcements'));
    }

    public function create(): View
    {
        $countries = Agent::query()->distinct()->pluck('country')->filter()->mapWithKeys(fn ($c) => [$c => $c])->toArray();

        return view('admin.announcements.form', [
            'announcement' => new Announcement(),
            'countries'    => $countries,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'          => ['required', 'string', 'max:200'],
            'body'           => ['required', 'string', 'max:5000'],
            'variant'        => ['required', 'in:info,success,warning,danger'],
            'tier_filter'    => ['nullable', 'array'],
            'tier_filter.*'  => ['in:bronze,silver,gold,diamond'],
            'country_filter' => ['nullable', 'array'],
            'send_email'     => ['nullable', 'boolean'],
            'expires_at'     => ['nullable', 'date', 'after:now'],
        ]);

        $announcement = Announcement::create([
            'title'          => $data['title'],
            'body'           => $data['body'],
            'variant'        => $data['variant'],
            'tier_filter'    => $data['tier_filter']    ?? null,
            'country_filter' => $data['country_filter'] ?? null,
            'send_email'     => (bool) ($data['send_email'] ?? false),
            'expires_at'     => $data['expires_at']     ?? null,
            'is_active'      => true,
            'created_by'     => $request->user()->id,
        ]);

        $emailedCount = 0;

        if ($announcement->send_email) {
            $recipients = $announcement->recipients();
            foreach ($recipients as $agent) {
                if (! $agent->user || ! $agent->user->email) {
                    continue;
                }
                try {
                    Mail::to($agent->user->email)->send(new AnnouncementMail($announcement));
                    $emailedCount++;
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        }

        $this->audit->log(
            action: 'announcement_created',
            entityType: Announcement::class,
            entityId: (string) $announcement->id,
            newValues: [
                'title'         => $announcement->title,
                'audience'      => [
                    'tiers'     => $announcement->tier_filter,
                    'countries' => $announcement->country_filter,
                ],
                'send_email'    => $announcement->send_email,
                'emailed_count' => $emailedCount,
            ],
        );

        $msg = "تم نشر الإعلان بنجاح.";
        if ($announcement->send_email) {
            $msg .= " أُرسل إيميل لـ {$emailedCount} وكيل.";
        }

        return redirect()->route('admin.announcements')->with('status', $msg);
    }

    public function toggle(Request $request, Announcement $announcement): RedirectResponse
    {
        $announcement->update(['is_active' => ! $announcement->is_active]);

        $this->audit->log(
            action: 'announcement_toggled',
            entityType: Announcement::class,
            entityId: (string) $announcement->id,
            newValues: ['is_active' => $announcement->is_active],
        );

        $verb = $announcement->is_active ? 'تفعيل' : 'إيقاف';

        return back()->with('status', "تم {$verb} الإعلان «{$announcement->title}».");
    }

    public function destroy(Request $request, Announcement $announcement): RedirectResponse
    {
        $title = $announcement->title;
        $announcement->delete();

        $this->audit->log(
            action: 'announcement_deleted',
            entityType: Announcement::class,
            entityId: (string) $announcement->id,
        );

        return redirect()->route('admin.announcements')->with('status', "تم حذف الإعلان «{$title}».");
    }
}
