<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FreePackageRequest;
use App\Models\FreePackage;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PackageController extends Controller
{
    public function __construct(private AuditService $audit) {}

    public function index(Request $request): View
    {
        $query = FreePackage::orderBy('display_order')->orderBy('points_required');

        if ($q = $request->query('q')) {
            $query->where(function ($qb) use ($q) {
                $qb->where('name', 'like', "%{$q}%")
                   ->orWhere('destination', 'like', "%{$q}%");
            });
        }

        $status = $request->query('status');
        if ($status === 'active')   { $query->where('is_active', true); }
        if ($status === 'inactive') { $query->where('is_active', false); }

        $perPage = (int) $request->query('per_page', 25);
        $perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 25;

        $packages = $query->paginate($perPage);

        return view('admin.packages.index', compact('packages'));
    }

    public function create(): View
    {
        return view('admin.packages.form', ['package' => new FreePackage()]);
    }

    public function store(FreePackageRequest $request): RedirectResponse
    {
        $data = $this->preparePayload($request);

        $package = FreePackage::create($data);

        $this->audit->log(
            action: 'free_package_created',
            entityType: FreePackage::class,
            entityId: (string) $package->id,
            newValues: $package->getAttributes(),
        );

        return redirect()->route('admin.packages')->with('status', "تم إنشاء الباكج «{$package->name}».");
    }

    public function edit(FreePackage $package): View
    {
        return view('admin.packages.form', compact('package'));
    }

    public function update(FreePackageRequest $request, FreePackage $package): RedirectResponse
    {
        $old  = $package->getAttributes();
        $data = $this->preparePayload($request, $package);

        $package->update($data);

        $this->audit->log(
            action: 'free_package_updated',
            entityType: FreePackage::class,
            entityId: (string) $package->id,
            oldValues: $old,
            newValues: $package->fresh()->getAttributes(),
        );

        return redirect()->route('admin.packages')->with('status', "تم تحديث الباكج «{$package->name}».");
    }

    public function toggle(FreePackage $package): RedirectResponse
    {
        $package->update(['is_active' => ! $package->is_active]);

        $this->audit->log(
            action: $package->is_active ? 'free_package_activated' : 'free_package_deactivated',
            entityType: FreePackage::class,
            entityId: (string) $package->id,
        );

        return back()->with(
            'status',
            $package->is_active
                ? "تم تفعيل الباكج «{$package->name}»."
                : "تم تعطيل الباكج «{$package->name}»."
        );
    }

    public function destroy(FreePackage $package): RedirectResponse
    {
        $name = $package->name;

        if ($package->image_url && str_starts_with($package->image_url, '/storage/')) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $package->image_url));
        }

        $package->delete();

        $this->audit->log(
            action: 'free_package_deleted',
            entityType: FreePackage::class,
            entityId: (string) $package->id,
        );

        return back()->with('status', "تم حذف الباكج «{$name}».");
    }

    /**
     * Normalize request payload (handle image upload, boolean cast, etc.).
     */
    private function preparePayload(FreePackageRequest $request, ?FreePackage $existing = null): array
    {
        $data = $request->validated();
        $data['is_active'] = (bool) $request->boolean('is_active');
        $data['display_order'] = (int) ($data['display_order'] ?? 0);

        if ($request->hasFile('image')) {
            // remove old image first
            if ($existing && $existing->image_url && str_starts_with($existing->image_url, '/storage/')) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $existing->image_url));
            }
            $path = $request->file('image')->store('packages', 'public');
            $data['image_url'] = '/storage/' . $path;
        }

        unset($data['image']);

        return $data;
    }
}
