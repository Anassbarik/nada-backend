<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PartnerController extends Controller
{
    /**
     * Display a listing of partners.
     */
    public function index()
    {
        $partners = Partner::orderBy('sort_order')->orderBy('created_at', 'desc')->paginate(20);
        return view('admin.partners.index', compact('partners'));
    }

    /**
     * Show the form for creating a new partner.
     */
    public function create()
    {
        return view('admin.partners.create');
    }

    /**
     * Store a newly created partner.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'logo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'url' => 'nullable|url|max:500',
            'sort_order' => 'nullable|integer|min:0',
            'active' => 'nullable|boolean',
        ]);

        // Store logo
        $logoPath = $request->file('logo')->store('partners', 'public');

        $partner = Partner::create([
            'name' => $validated['name'],
            'logo_path' => $logoPath,
            'url' => $validated['url'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
            'active' => $validated['active'] ?? true,
        ]);

        return redirect()->route('admin.partners.index')
            ->with('success', __('Partner created successfully.'));
    }

    /**
     * Display the specified partner.
     */
    public function show(Partner $partner)
    {
        return view('admin.partners.show', compact('partner'));
    }

    /**
     * Show the form for editing the specified partner.
     */
    public function edit(Partner $partner)
    {
        return view('admin.partners.edit', compact('partner'));
    }

    /**
     * Update the specified partner.
     */
    public function update(Request $request, Partner $partner)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'url' => 'nullable|url|max:500',
            'sort_order' => 'nullable|integer|min:0',
            'active' => 'nullable|boolean',
        ]);

        // Update logo if new one is uploaded
        if ($request->hasFile('logo')) {
            // Delete old logo
            if ($partner->logo_path) {
                Storage::disk('public')->delete($partner->logo_path);
            }
            $logoPath = $request->file('logo')->store('partners', 'public');
            $partner->logo_path = $logoPath;
        }

        $partner->name = $validated['name'];
        $partner->url = $validated['url'] ?? null;
        $partner->sort_order = $validated['sort_order'] ?? $partner->sort_order;
        $partner->active = $request->has('active') ? (bool) $request->active : $partner->active;
        $partner->save();

        return redirect()->route('admin.partners.index')
            ->with('success', __('Partner updated successfully.'));
    }

    /**
     * Remove the specified partner.
     */
    public function destroy(Partner $partner)
    {
        // Delete logo file
        if ($partner->logo_path) {
            Storage::disk('public')->delete($partner->logo_path);
        }

        $partner->delete();

        return redirect()->route('admin.partners.index')
            ->with('success', __('Partner deleted successfully.'));
    }

    /**
     * Toggle active status.
     */
    public function toggleActive(Partner $partner)
    {
        $partner->active = !$partner->active;
        $partner->save();

        return redirect()->route('admin.partners.index')
            ->with('success', __('Partner status updated.'));
    }

    /**
     * Update sort order.
     */
    public function updateSortOrder(Request $request)
    {
        $request->validate([
            'partners' => 'required|array',
            'partners.*.id' => 'required|exists:partners,id',
            'partners.*.sort_order' => 'required|integer',
        ]);

        foreach ($request->partners as $item) {
            Partner::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json(['success' => true]);
    }
}
