<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Services\AuditService;
use App\Services\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SettingsController extends Controller
{
    /**
     * Human labels per category, in display order.
     */
    private const CATEGORY_LABELS = [
        'points'     => 'حساب النقاط',
        'redemption' => 'الاستبدال',
        'tier'       => 'التصنيفات',
        'webhook'    => 'الـ Webhook',
        'agents'     => 'الوكلاء',
        'auth'       => 'المصادقة والأمان',
        'security'   => 'الأمان المتقدم',
        'general'    => 'إعدادات عامة',
    ];

    public function __construct(
        private SettingsService $settings,
        private AuditService $audit,
    ) {}

    public function index(): View
    {
        $rows = SystemSetting::orderBy('category')->orderBy('key')->get();

        $grouped = $rows->groupBy('category')
            ->sortBy(fn ($_, $cat) => array_search($cat, array_keys(self::CATEGORY_LABELS), true) === false
                ? PHP_INT_MAX
                : array_search($cat, array_keys(self::CATEGORY_LABELS), true)
            );

        return view('admin.settings.index', [
            'grouped'        => $grouped,
            'categoryLabels' => self::CATEGORY_LABELS,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        // Build validation rules dynamically from the settings catalogue.
        $rules   = [];
        $known   = SystemSetting::pluck('value_type', 'key')->toArray();
        $payload = $request->input('settings', []);

        foreach ($payload as $key => $value) {
            if (! isset($known[$key])) {
                continue;
            }
            $rules["settings.{$key}"] = $this->rulesFor($known[$key]);
        }

        $request->validate($rules);

        $changes = [];

        DB::transaction(function () use ($payload, $known, $request, &$changes) {
            foreach ($payload as $key => $value) {
                if (! isset($known[$key])) {
                    continue;
                }

                $setting = SystemSetting::find($key);
                $oldTyped = $setting->typedValue();

                // Bool checkboxes arrive as "1"/null — normalize.
                if ($known[$key] === 'bool') {
                    $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                }

                $this->settings->set($key, $value, $request->user()->id);

                $newTyped = $setting->fresh()->typedValue();
                if ($oldTyped !== $newTyped) {
                    $changes[$key] = ['old' => $oldTyped, 'new' => $newTyped];
                }
            }
        });

        if (! empty($changes)) {
            $this->audit->log(
                action: 'settings_updated',
                entityType: SystemSetting::class,
                entityId: null,
                oldValues: array_map(fn ($c) => $c['old'], $changes),
                newValues: array_map(fn ($c) => $c['new'], $changes),
            );
        }

        return back()->with('status', count($changes)
            ? 'تم حفظ ' . count($changes) . ' إعداد بنجاح.'
            : 'لم يتم تغيير أي إعداد.');
    }

    /**
     * Per value-type validation rules.
     */
    private function rulesFor(string $type): array
    {
        return match ($type) {
            'int'   => ['required', 'integer'],
            'float' => ['required', 'numeric'],
            'bool'  => ['nullable', 'in:0,1,true,false'],
            'json'  => ['required', 'json'],
            default => ['required', 'string', 'max:1000'],
        };
    }
}
