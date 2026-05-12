<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\AnnouncementRead;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    /**
     * Mark an announcement as dismissed (read) for the current agent.
     */
    public function dismiss(Request $request, Announcement $announcement): RedirectResponse
    {
        $agent = $request->user()->agent;

        if (! $agent) {
            abort(403);
        }

        AnnouncementRead::firstOrCreate([
            'announcement_id' => $announcement->id,
            'agent_id'        => $agent->id,
        ], [
            'read_at' => now(),
        ]);

        return back();
    }
}
