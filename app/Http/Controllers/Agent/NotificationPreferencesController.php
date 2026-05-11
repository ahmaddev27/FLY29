<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\UserNotificationPreference;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationPreferencesController extends Controller
{
    /**
     * Notification types the agent can toggle.
     * Keep in sync with NotificationService dispatcher (Sprint 2.3).
     */
    public const TYPES = [
        'tier_upgraded'        => 'ترقية التصنيف',
        'tier_downgraded'      => 'تخفيض التصنيف',
        'tier_warning'         => 'تحذير قبل تخفيض التصنيف',
        'points_earned'        => 'كسب نقاط جديدة',
        'redemption_approved'  => 'موافقة على طلب تحويل',
        'redemption_rejected'  => 'رفض طلب تحويل',
        'free_package_ready'   => 'الوصول لعتبة باكج مجاني',
        'manual_points_added'  => 'إضافة نقاط يدوية من الإدارة',
    ];

    public function show(Request $request): View
    {
        $user        = $request->user();
        $existing    = $user->notificationPreferences()->get()->keyBy('notification_type');
        $preferences = [];

        foreach (self::TYPES as $type => $label) {
            $row = $existing->get($type);
            $preferences[$type] = [
                'label'          => $label,
                'email_enabled'  => $row?->email_enabled  ?? true,
                'sms_enabled'    => $row?->sms_enabled    ?? false,
                'in_app_enabled' => $row?->in_app_enabled ?? true,
            ];
        }

        return view('agent.notification-preferences', compact('preferences'));
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'preferences' => ['array'],
        ]);

        foreach (array_keys(self::TYPES) as $type) {
            $email  = (bool) data_get($data, "preferences.{$type}.email_enabled", false);
            $sms    = (bool) data_get($data, "preferences.{$type}.sms_enabled", false);
            $inApp  = (bool) data_get($data, "preferences.{$type}.in_app_enabled", false);

            UserNotificationPreference::updateOrCreate(
                ['user_id' => $user->id, 'notification_type' => $type],
                [
                    'email_enabled'  => $email,
                    'sms_enabled'    => $sms,
                    'in_app_enabled' => $inApp,
                ]
            );
        }

        return back()->with('status', 'تم حفظ تفضيلات الإشعارات.');
    }
}
