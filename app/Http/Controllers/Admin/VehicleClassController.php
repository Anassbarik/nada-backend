<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VehicleClass;
use Illuminate\Http\Request;

class VehicleClassController extends Controller
{
    public function index()
    {
        if (!auth()->user()->hasPermission('transfers', 'view')) {
            abort(403);
        }

        $vehicleClasses = VehicleClass::with('creator')->latest()->paginate(15);
        return view('admin.vehicle-classes.index', compact('vehicleClasses'));
    }

    public function create()
    {
        if (!auth()->user()->hasPermission('transfers', 'create')) {
            abort(403);
        }

        return view('admin.vehicle-classes.create');
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasPermission('transfers', 'create')) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:vehicle_classes,name',
        ]);

        VehicleClass::create([
            'name' => $validated['name'],
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('admin.vehicle-classes.index')
            ->with('success', 'Vehicle class created successfully.');
    }

    public function edit(VehicleClass $vehicleClass)
    {
        if (!auth()->user()->hasPermission('transfers', 'edit')) {
            abort(403);
        }

        return view('admin.vehicle-classes.edit', compact('vehicleClass'));
    }

    public function update(Request $request, VehicleClass $vehicleClass)
    {
        if (!auth()->user()->hasPermission('transfers', 'edit')) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:vehicle_classes,name,' . $vehicleClass->id,
        ]);

        $vehicleClass->update($validated);

        return redirect()->route('admin.vehicle-classes.index')
            ->with('success', 'Vehicle class updated successfully.');
    }

    public function destroy(VehicleClass $vehicleClass)
    {
        if (!auth()->user()->hasPermission('transfers', 'delete')) {
            abort(403);
        }

        if ($vehicleClass->vehicleTypes()->exists()) {
            return back()->with('error', 'Cannot delete vehicle class because it is being used by vehicle types.');
        }

        $vehicleClass->delete();

        return redirect()->route('admin.vehicle-classes.index')
            ->with('success', 'Vehicle class deleted successfully.');
    }
}
