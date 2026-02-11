<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VehicleType;
use Illuminate\Http\Request;

class VehicleTypeController extends Controller
{
    public function index()
    {
        if (!auth()->user()->hasPermission('transfers', 'view')) {
            abort(403);
        }

        $vehicleTypes = VehicleType::with(['creator', 'vehicleClass'])->latest()->paginate(15);
        return view('admin.vehicle-types.index', compact('vehicleTypes'));
    }

    public function create()
    {
        if (!auth()->user()->hasPermission('transfers', 'create')) {
            abort(403);
        }

        $vehicleClasses = \App\Models\VehicleClass::orderBy('name')->get();
        return view('admin.vehicle-types.create', compact('vehicleClasses'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasPermission('transfers', 'create')) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:vehicle_types,name',
            'vehicle_class_id' => 'required|exists:vehicle_classes,id',
            'max_passengers' => 'required|integer|min:1',
            'max_luggages' => 'required|integer|min:0',
        ]);

        VehicleType::create([
            'name' => $validated['name'],
            'vehicle_class_id' => $validated['vehicle_class_id'],
            'max_passengers' => $validated['max_passengers'],
            'max_luggages' => $validated['max_luggages'],
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('admin.vehicle-types.index')
            ->with('success', 'Vehicle type created successfully.');
    }

    public function edit(VehicleType $vehicleType)
    {
        if (!auth()->user()->hasPermission('transfers', 'edit')) {
            abort(403);
        }

        $vehicleClasses = \App\Models\VehicleClass::orderBy('name')->get();
        return view('admin.vehicle-types.edit', compact('vehicleType', 'vehicleClasses'));
    }

    public function update(Request $request, VehicleType $vehicleType)
    {
        if (!auth()->user()->hasPermission('transfers', 'edit')) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:vehicle_types,name,' . $vehicleType->id,
            'vehicle_class_id' => 'required|exists:vehicle_classes,id',
            'max_passengers' => 'required|integer|min:1',
            'max_luggages' => 'required|integer|min:0',
        ]);

        $vehicleType->update($validated);

        return redirect()->route('admin.vehicle-types.index')
            ->with('success', 'Vehicle type updated successfully.');
    }

    public function destroy(VehicleType $vehicleType)
    {
        if (!auth()->user()->hasPermission('transfers', 'delete')) {
            abort(403);
        }

        if ($vehicleType->transfers()->exists()) {
            return back()->with('error', 'Cannot delete vehicle type because it is being used by transfers.');
        }

        $vehicleType->delete();

        return redirect()->route('admin.vehicle-types.index')
            ->with('success', 'Vehicle type deleted successfully.');
    }
}
