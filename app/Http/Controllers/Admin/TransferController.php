<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Accommodation;
use App\Models\Booking;
use App\Models\Transfer;
use App\Models\User;
use App\Services\DualStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Exports\TransfersExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class TransferController extends Controller
{
    public function index(Accommodation $accommodation)
    {
        if (!$accommodation->canManageTransfersBy(auth()->user())) {
            abort(403);
        }

        $transfers = $accommodation->transfers()
            ->with(['booking', 'organizer'])
            ->latest()
            ->paginate(10);

        return view('admin.transfers.index', compact('accommodation', 'transfers'));
    }

    public function globalIndex()
    {
        // For sidebar link "Transfers"
        if (!auth()->user()->isAdmin() && !auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        // Filter based on permissions
        $query = Transfer::query()->with(['accommodation', 'booking']);

        if (!auth()->user()->isSuperAdmin()) {
            // Regular admins only see what they can manage
            // This can be complex with the permission rules, simplistically:
            // 1. Created by them
            // 2. Or they have resource permission on the accommodation
            $userId = auth()->id();
            $query->where(function ($q) use ($userId) {
                $q->where('created_by', $userId)
                    ->orWhereHas('accommodation', function ($aq) use ($userId) {
                        $aq->where('created_by', $userId) // They created the accommodation
                            ->orWhereHas('resourcePermissions', function ($rpq) use ($userId) {
                                $rpq->where('user_id', $userId)
                                    ->where('resource_type', 'transfer'); // Assuming transfer resource type
                            });
                    });
            });
        }

        $transfers = $query->latest()->paginate(15);

        return view('admin.transfers.global-index', compact('transfers'));
    }

    /**
     * Export all transfers across all accommodations to Excel.
     */
    public function exportAll()
    {
        $user = auth()->user();

        if (!$user->isSuperAdmin() && !$user->isAdmin()) {
            abort(403, 'You do not have permission to export transfers.');
        }

        $filename = 'transfers-all-' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(new TransfersExport(), $filename);
    }

    /**
     * Export all transfers for a specific accommodation (event) to Excel.
     */
    public function exportForAccommodation(Accommodation $accommodation)
    {
        if (!$accommodation->canManageTransfersBy(auth()->user())) {
            abort(403, 'You do not have permission to export transfers for this accommodation.');
        }

        $filename = 'transfers-' . ($accommodation->slug ?? $accommodation->id) . '-' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(new TransfersExport($accommodation->id), $filename);
    }

    /**
     * Export a single transfer to Excel.
     */
    public function exportSingle(Accommodation $accommodation, Transfer $transfer)
    {
        if (!$accommodation->canManageTransfersBy(auth()->user())) {
            abort(403, 'You do not have permission to export transfers for this accommodation.');
        }

        // Ensure the transfer belongs to this accommodation
        if ((int) $transfer->accommodation_id !== (int) $accommodation->id) {
            abort(404);
        }

        $filename = 'transfer-' . $transfer->id . '.xlsx';

        return Excel::download(new TransfersExport(null, $transfer->id), $filename);
    }

    public function create(Accommodation $accommodation)
    {
        if (!$accommodation->canManageTransfersBy(auth()->user())) {
            abort(403);
        }

        // Get regular admins for transfers sub-permissions assignment (only for super-admin)
        $admins = auth()->user()->isSuperAdmin()
            ? User::where('role', 'admin')->orderBy('name')->get()
            : collect();

        // Get current transfers sub-permissions for this accommodation
        $transfersSubPermissions = \App\Models\ResourcePermission::where('resource_type', 'transfer')
            ->where('resource_id', $accommodation->id)
            ->pluck('user_id')
            ->toArray();

        $users = User::all(); // Potentially filter for clients/organizers?
        $vehicleTypes = \App\Models\VehicleType::orderBy('name')->get();
        return view('admin.transfers.create', compact('accommodation', 'users', 'admins', 'transfersSubPermissions', 'vehicleTypes'));
    }

    public function store(Request $request, Accommodation $accommodation)
    {
        if (!$accommodation->canManageTransfersBy(auth()->user())) {
            abort(403);
        }

        $validated = $request->validate([
            'client_name' => 'required_if:beneficiary_type,client|nullable|string|max:255',
            'client_phone' => 'required_if:beneficiary_type,client|nullable|string|max:20',
            'client_email' => 'nullable|email|max:255',
            'transfer_type' => 'required|in:airport_hotel,hotel_airport,hotel_event,event_hotel,city_transfer',
            'trip_type' => 'required|in:one_way,round_trip',
            'transfer_date' => 'required|date',
            'pickup_time' => 'required',
            'pickup_location' => 'required|string|max:255',
            'dropoff_location' => 'required|string|max:255',
            'flight_number' => 'nullable|string|max:50',
            'flight_time' => 'nullable|date_format:H:i',
            'vehicle_type_id' => 'required|exists:vehicle_types,id',
            'passengers' => 'required|integer|min:1',
            'luggages' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0',
            'return_date' => 'nullable|required_if:trip_type,round_trip|date|after_or_equal:transfer_date',
            'return_time' => 'nullable|required_if:trip_type,round_trip',
            'status' => 'required|in:pending,paid,confirmed,completed,cancelled',
            'payment_method' => 'nullable|in:wallet,bank,both',
            'beneficiary_type' => 'required|in:organizer,client',
            'eticket' => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:10240',
            'show_transfer_prices_public' => 'nullable|boolean',
            'show_transfer_prices_client_dashboard' => 'nullable|boolean',
            'show_transfer_prices_organizer_dashboard' => 'nullable|boolean',
            'transfers_sub_permissions' => 'nullable|array',
            'transfers_sub_permissions.*' => 'exists:users,id',
            'driver_name' => 'nullable|string|max:255',
            'driver_phone' => 'nullable|string|max:255',
            'additional_passengers' => 'nullable|array',
            'additional_passengers.*' => 'nullable|string|max:255',
        ]);

        // Strict Passenger and Luggage Validation against Vehicle Type
        $vehicleType = \App\Models\VehicleType::findOrFail($validated['vehicle_type_id']);
        if ($validated['passengers'] > $vehicleType->max_passengers) {
            return back()->withInput()->withErrors(['passengers' => "The selected vehicle type ({$vehicleType->name}) allows a maximum of {$vehicleType->max_passengers} passengers."]);
        }
        if ($validated['luggages'] > $vehicleType->max_luggages) {
            \Illuminate\Support\Facades\Log::info('Luggage check failed', ['luggages' => $validated['luggages'], 'max' => $vehicleType->max_luggages]);
            return back()->withInput()->withErrors(['luggages' => "The selected vehicle type ({$vehicleType->name}) allows a maximum of {$vehicleType->max_luggages} luggages."]);
        }

        DB::beginTransaction();

        try {
            $eticketPath = null;
            if ($request->hasFile('eticket')) {
                $eticketPath = DualStorageService::store($request->file('eticket'), 'transfers/etickets');
            }

            // Handle Beneficiary / User Creation
            $userId = null;
            $organizerId = null;

            if ($validated['beneficiary_type'] === 'organizer') {
                $organizerId = $accommodation->organizer_id;
                // If the organizer is also a user, we might want to link user_id too, 
                // but typically organizer_id is enough for that context.
                // Logic mirrors FlightController: 
                // "If beneficiary is organizer, we attribute it to the accommodation organizer"

                // Set default client info if organizer
                $organizer = $accommodation->organizer ?? User::find($organizerId);
                $validated['client_name'] = $validated['client_name'] ?? ($organizer->name ?? 'Organizer');
                $validated['client_phone'] = $validated['client_phone'] ?? ($organizer->phone ?? '');
                $validated['client_email'] = $validated['client_email'] ?? ($organizer->email ?? '');
            } else {
                // Client beneficiary
                // Check if user exists by email, or create new
                if (!empty($validated['client_email'])) {
                    $user = User::where('email', $validated['client_email'])->first();
                    if (!$user) {
                        // Create new user (client)
                        $password = Str::random(10);
                        $user = User::create([
                            'name' => $validated['client_name'],
                            'email' => $validated['client_email'],
                            'phone' => $validated['client_phone'],
                            'role' => 'user', // Changed from 'client' to 'user' as 'client' is not in database ENUM
                            'password' => Hash::make($password),
                        ]);

                        // Send credentials email
                        // Mail::to($user->email)->send(new \App\Mail\UserCredentials($user, $password));
                    }
                    $userId = $user->id;
                }
            }

            $transfer = Transfer::create([
                'accommodation_id' => $accommodation->id,
                'client_name' => $validated['client_name'],
                'client_phone' => $validated['client_phone'],
                'client_email' => $validated['client_email'],
                'transfer_type' => $validated['transfer_type'],
                'trip_type' => $validated['trip_type'],
                'transfer_date' => $validated['transfer_date'],
                'pickup_time' => $validated['pickup_time'],
                'pickup_location' => $validated['pickup_location'],
                'dropoff_location' => $validated['dropoff_location'],
                'flight_number' => $validated['flight_number'] ?? null,
                'flight_time' => $validated['flight_time'] ?? null,
                'vehicle_type' => $vehicleType->name,
                'vehicle_type_id' => $validated['vehicle_type_id'],
                'passengers' => $validated['passengers'],
                'luggages' => $validated['luggages'],
                'price' => $validated['price'],
                'return_date' => $validated['return_date'] ?? null,
                'return_time' => $validated['return_time'] ?? null,
                'eticket_path' => $eticketPath,
                'status' => $validated['status'],
                'payment_method' => $validated['payment_method'] ?? null,
                'beneficiary_type' => $validated['beneficiary_type'],
                'organizer_id' => $organizerId,
                'user_id' => $userId,
                'created_by' => auth()->id(),
                'driver_name' => $validated['driver_name'] ?? null,
                'driver_phone' => $validated['driver_phone'] ?? null,
                'additional_passengers' => $validated['additional_passengers'] ?? null,
            ]);

            \Illuminate\Support\Facades\Log::info('Transfer created in store', ['id' => $transfer->id]);

            // Create Booking only if NOT organizer beneficiary
            if ($validated['beneficiary_type'] !== 'organizer') {
                $booking = Booking::create([
                    'user_id' => $userId, // Can be null
                    'created_by' => auth()->id(),
                    'accommodation_id' => $accommodation->id,
                    'event_id' => $accommodation->id,
                    'transfer_id' => $transfer->id,
                    'guest_name' => $validated['client_name'],
                    'guest_email' => $validated['client_email'],
                    'guest_phone' => $validated['client_phone'],
                    'full_name' => $validated['client_name'],
                    'phone' => $validated['client_phone'],
                    'email' => $validated['client_email'],

                    'checkin_date' => $validated['transfer_date'], // Use transfer date as checkin
                    'checkout_date' => ($validated['trip_type'] === 'round_trip' && !empty($validated['return_date'])) ? $validated['return_date'] : $validated['transfer_date'],
                    'guests_count' => $validated['passengers'],

                    'flight_number' => $validated['flight_number'] ?? null,
                    'flight_date' => $validated['transfer_date'],
                    'flight_time' => $validated['flight_time'] ?? null,

                    'price' => $validated['price'],
                    'status' => in_array($validated['status'], ['pending', 'confirmed', 'paid', 'cancelled']) ? $validated['status'] : 'pending',
                    'payment_type' => in_array($validated['payment_method'] ?? null, ['wallet', 'bank', 'both']) ? $validated['payment_method'] : 'bank',
                ]);

                // Send confirmation email to client if email is provided
                if (!empty($validated['client_email'])) {
                    try {
                        Mail::to($validated['client_email'])->send(new \App\Mail\BookingConfirmation($booking));
                        Log::info('Booking confirmation email sent to client for transfer', [
                            'booking_id' => $booking->id,
                            'booking_reference' => $booking->booking_reference,
                            'client_email' => $validated['client_email'],
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Failed to send booking confirmation email for transfer', [
                            'booking_id' => $booking->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }

            // Update accommodation's transfer price visibility settings
            if ($request->has('show_transfer_prices_public')) {
                $accommodation->show_transfer_prices_public = (bool) $request->input('show_transfer_prices_public');
            }
            if ($request->has('show_transfer_prices_client_dashboard')) {
                $accommodation->show_transfer_prices_client_dashboard = (bool) $request->input('show_transfer_prices_client_dashboard');
            }
            if ($request->has('show_transfer_prices_organizer_dashboard')) {
                $accommodation->show_transfer_prices_organizer_dashboard = (bool) $request->input('show_transfer_prices_organizer_dashboard');
            }
            $accommodation->save();

            // Handle transfers sub-permissions (only for super-admin)
            if (auth()->user()->isSuperAdmin() && isset($validated['transfers_sub_permissions'])) {
                // Remove all existing transfers sub-permissions for this accommodation
                \App\Models\ResourcePermission::where('resource_type', 'transfer')
                    ->where('resource_id', $accommodation->id)
                    ->delete();

                foreach ($validated['transfers_sub_permissions'] as $adminId) {
                    \App\Models\ResourcePermission::create([
                        'resource_type' => 'transfer',
                        'resource_id' => $accommodation->id,
                        'user_id' => $adminId,
                    ]);
                }
            }

            DB::commit();

            // Check if this is a standalone request
            $isStandalone = $request->has('_standalone') || request()->routeIs('admin.transfers.store-standalone');

            if ($isStandalone) {
                return redirect()->route('admin.transfers.global-index')
                    ->with('success', 'Transfer created successfully.');
            }

            return redirect()->route('admin.transfers.index', $accommodation)
                ->with('success', 'Transfer created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Transfer creation failed: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Failed to create transfer: ' . $e->getMessage());
        }
    }

    public function edit(Accommodation $accommodation, Transfer $transfer)
    {
        if (!$accommodation->canManageTransfersBy(auth()->user())) {
            abort(403);
        }

        // Get regular admins for transfers sub-permissions assignment (only for super-admin)
        $admins = auth()->user()->isSuperAdmin()
            ? User::where('role', 'admin')->orderBy('name')->get()
            : collect();

        // Get current transfers sub-permissions for this accommodation
        $transfersSubPermissions = \App\Models\ResourcePermission::where('resource_type', 'transfer')
            ->where('resource_id', $accommodation->id)
            ->pluck('user_id')
            ->toArray();

        $vehicleTypes = \App\Models\VehicleType::orderBy('name')->get();
        return view('admin.transfers.edit', compact('accommodation', 'transfer', 'admins', 'transfersSubPermissions', 'vehicleTypes'));
    }

    public function update(Request $request, Accommodation $accommodation, Transfer $transfer)
    {
        if (!$accommodation->canManageTransfersBy(auth()->user())) {
            abort(403);
        }

        $validated = $request->validate([
            'client_name' => 'required_if:beneficiary_type,client|nullable|string|max:255',
            'client_phone' => 'required_if:beneficiary_type,client|nullable|string|max:20',
            'client_email' => 'nullable|email|max:255',
            'transfer_type' => 'required|in:airport_hotel,hotel_airport,hotel_event,event_hotel,city_transfer',
            'trip_type' => 'required|in:one_way,round_trip',
            'transfer_date' => 'required|date',
            'pickup_time' => 'required',
            'pickup_location' => 'required|string|max:255',
            'dropoff_location' => 'required|string|max:255',
            'flight_number' => 'nullable|string|max:50',
            'flight_time' => 'nullable|date_format:H:i',
            'vehicle_type_id' => 'required|exists:vehicle_types,id',
            'passengers' => 'required|integer|min:1',
            'luggages' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0',
            'return_date' => 'nullable|required_if:trip_type,round_trip|date|after_or_equal:transfer_date',
            'return_time' => 'nullable|required_if:trip_type,round_trip',
            'status' => 'required|in:pending,paid,confirmed,completed,cancelled',
            'payment_method' => 'nullable|in:wallet,bank,both',
            'beneficiary_type' => 'required|in:organizer,client',
            'eticket' => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:10240',
            'show_transfer_prices_public' => 'nullable|boolean',
            'show_transfer_prices_client_dashboard' => 'nullable|boolean',
            'show_transfer_prices_organizer_dashboard' => 'nullable|boolean',
            'transfers_sub_permissions' => 'nullable|array',
            'transfers_sub_permissions.*' => 'exists:users,id',
            'driver_name' => 'nullable|string|max:255',
            'driver_phone' => 'nullable|string|max:255',
            'additional_passengers' => 'nullable|array',
            'additional_passengers.*' => 'nullable|string|max:255',
        ]);

        // Strict Passenger Validation against Vehicle Type
        $vehicleType = \App\Models\VehicleType::findOrFail($validated['vehicle_type_id']);
        if ($validated['passengers'] > $vehicleType->max_passengers) {
            return back()->withInput()->withErrors(['passengers' => "The selected vehicle type ({$vehicleType->name}) allows a maximum of {$vehicleType->max_passengers} passengers."]);
        }

        DB::beginTransaction();

        try {
            if ($request->hasFile('eticket')) {
                // Delete old if exists
                if ($transfer->eticket_path) {
                    DualStorageService::delete($transfer->eticket_path);
                }
                $transfer->eticket_path = DualStorageService::store($request->file('eticket'), 'transfers/etickets');
            }

            $userId = $transfer->user_id;
            $organizerId = $transfer->organizer_id;

            if ($validated['beneficiary_type'] === 'organizer') {
                $organizerId = $accommodation->organizer_id;
                $userId = null; // Clear user_id if switching to organizer

                // Set default client info if organizer and currently null
                $organizer = $accommodation->organizer ?? User::find($organizerId);
                $validated['client_name'] = $validated['client_name'] ?? ($organizer->name ?? 'Organizer');
                $validated['client_phone'] = $validated['client_phone'] ?? ($organizer->phone ?? '');
                $validated['client_email'] = $validated['client_email'] ?? ($organizer->email ?? '');
            } else {
                // Client beneficiary - handle potential new user creation or lookup
                $organizerId = null; // Clear organizer_id if switching to client
                if (!empty($validated['client_email'])) {
                    $user = User::where('email', $validated['client_email'])->first();
                    if (!$user) {
                        $password = Str::random(10);
                        $user = User::create([
                            'name' => $validated['client_name'],
                            'email' => $validated['client_email'],
                            'phone' => $validated['client_phone'],
                            'role' => 'user',
                            'password' => Hash::make($password),
                        ]);
                    }
                    $userId = $user->id;
                }
            }

            $transfer->update([
                'client_name' => $validated['client_name'],
                'client_phone' => $validated['client_phone'],
                'client_email' => $validated['client_email'],
                'transfer_type' => $validated['transfer_type'],
                'trip_type' => $validated['trip_type'],
                'transfer_date' => $validated['transfer_date'],
                'pickup_time' => $validated['pickup_time'],
                'pickup_location' => $validated['pickup_location'],
                'dropoff_location' => $validated['dropoff_location'],
                'flight_number' => $validated['flight_number'] ?? null,
                'flight_time' => $validated['flight_time'] ?? null,
                'vehicle_type' => $vehicleType->name,
                'vehicle_type_id' => $validated['vehicle_type_id'],
                'passengers' => $validated['passengers'],
                'luggages' => $validated['luggages'],
                'price' => $validated['price'],
                'return_date' => $validated['return_date'] ?? null,
                'return_time' => $validated['return_time'] ?? null,
                'status' => $validated['status'],
                'payment_method' => $validated['payment_method'] ?? null,
                'beneficiary_type' => $validated['beneficiary_type'],
                'organizer_id' => $organizerId,
                'user_id' => $userId,
                'driver_name' => $validated['driver_name'] ?? null,
                'driver_phone' => $validated['driver_phone'] ?? null,
                'additional_passengers' => $validated['additional_passengers'] ?? null,
            ]);

            // Manage associated Booking
            if ($validated['beneficiary_type'] === 'organizer') {
                // Delete booking if it exists (organizer transfers don't have bookings)
                if ($transfer->booking) {
                    $transfer->booking->delete();
                }
            } else {
                // Client beneficiary - update or create booking
                if ($transfer->booking) {
                    $transfer->booking->update([
                        'user_id' => $userId,
                        'guest_name' => $validated['client_name'],
                        'full_name' => $validated['client_name'],
                        'guest_phone' => $validated['client_phone'],
                        'phone' => $validated['client_phone'],
                        'guest_email' => $validated['client_email'],
                        'email' => $validated['client_email'],
                        'checkin_date' => $validated['transfer_date'],
                        'checkout_date' => $validated['trip_type'] === 'round_trip' ? $validated['return_date'] : $validated['transfer_date'],
                        'guests_count' => $validated['passengers'],
                        'price' => $validated['price'],
                        'flight_number' => $validated['flight_number'],
                        'flight_date' => $validated['transfer_date'],
                        'flight_time' => $validated['flight_time'],
                    ]);
                } else {
                    // Create booking if missing
                    Booking::create([
                        'user_id' => $userId,
                        'created_by' => auth()->id(),
                        'accommodation_id' => $accommodation->id,
                        'event_id' => $accommodation->id,
                        'transfer_id' => $transfer->id,
                        'guest_name' => $validated['client_name'],
                        'guest_email' => $validated['client_email'],
                        'guest_phone' => $validated['client_phone'],
                        'full_name' => $validated['client_name'],
                        'phone' => $validated['client_phone'],
                        'email' => $validated['client_email'],
                        'checkin_date' => $validated['transfer_date'],
                        'checkout_date' => ($validated['trip_type'] === 'round_trip' && !empty($validated['return_date'])) ? $validated['return_date'] : $validated['transfer_date'],
                        'guests_count' => $validated['passengers'],
                        'flight_number' => $validated['flight_number'] ?? null,
                        'flight_date' => $validated['transfer_date'],
                        'flight_time' => $validated['flight_time'] ?? null,
                        'price' => $validated['price'],
                        'status' => in_array($validated['status'], ['pending', 'confirmed', 'paid', 'cancelled']) ? $validated['status'] : 'pending',
                        'payment_type' => in_array($validated['payment_method'] ?? null, ['wallet', 'bank', 'both']) ? $validated['payment_method'] : 'bank',
                    ]);
                }
            }

            DB::commit();

            // Update accommodation's transfer price visibility settings
            if ($request->has('show_transfer_prices_public')) {
                $accommodation->show_transfer_prices_public = (bool) $request->input('show_transfer_prices_public');
            }
            if ($request->has('show_transfer_prices_client_dashboard')) {
                $accommodation->show_transfer_prices_client_dashboard = (bool) $request->input('show_transfer_prices_client_dashboard');
            }
            if ($request->has('show_transfer_prices_organizer_dashboard')) {
                $accommodation->show_transfer_prices_organizer_dashboard = (bool) $request->input('show_transfer_prices_organizer_dashboard');
            }
            $accommodation->save();

            // Handle transfers sub-permissions (only for super-admin)
            if (auth()->user()->isSuperAdmin() && isset($validated['transfers_sub_permissions'])) {
                // Remove all existing transfers sub-permissions for this accommodation
                \App\Models\ResourcePermission::where('resource_type', 'transfer')
                    ->where('resource_id', $accommodation->id)
                    ->delete();

                foreach ($validated['transfers_sub_permissions'] as $adminId) {
                    \App\Models\ResourcePermission::create([
                        'resource_type' => 'transfer',
                        'resource_id' => $accommodation->id,
                        'user_id' => $adminId,
                    ]);
                }
            }

            return redirect()->route('admin.transfers.index', $accommodation)
                ->with('success', 'Transfer updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Transfer update failed: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Failed to update transfer.');
        }
    }

    public function destroy(Accommodation $accommodation, Transfer $transfer)
    {
        if (!$accommodation->canManageTransfersBy(auth()->user())) {
            abort(403);
        }

        try {
            if ($transfer->booking) {
                $transfer->booking->delete(); // Or update to null? Usually delete if strictly linked.
            }
            if ($transfer->eticket_path) {
                DualStorageService::delete($transfer->eticket_path);
            }

            $transfer->delete();

            return redirect()->route('admin.transfers.index', $accommodation)
                ->with('success', 'Transfer deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete transfer.');
        }
    }

    /**
     * Show the form for creating a new standalone transfer (with event dropdown).
     */
    public function createStandalone()
    {
        $user = auth()->user();

        if (!$user->isSuperAdmin()) {
            if (!$user->isAdmin()) {
                abort(403, 'You do not have permission to create transfers.');
            }

            // Check if user has main permission OR resource permissions
            $hasMainPermission = $user->hasPermission('transfers', 'create');
            $hasResourcePermissions = \App\Models\ResourcePermission::where('user_id', $user->id)
                ->where('resource_type', 'transfer')
                ->exists();

            if (!$hasMainPermission && !$hasResourcePermissions) {
                abort(403, 'You do not have permission to create transfers.');
            }
        }

        // Get accommodations - filter based on permissions
        if ($user->isSuperAdmin() || $user->hasPermission('transfers', 'create')) {
            // User has main permission, show all accommodations
            $accommodations = Accommodation::orderBy('name')->get();
        } else {
            // User only has resource permissions, show only accommodations they have access to
            $allowedAccommodationIds = \App\Models\ResourcePermission::where('user_id', $user->id)
                ->where('resource_type', 'transfer')
                ->pluck('resource_id')
                ->toArray();

            $accommodations = Accommodation::whereIn('id', $allowedAccommodationIds)
                ->orderBy('name')
                ->get();
        }

        // Get regular admins for transfers sub-permissions assignment (only for super-admin)
        $admins = $user->isSuperAdmin()
            ? User::where('role', 'admin')->orderBy('name')->get()
            : collect();

        // Get all users for potential linking (optional, matching create method)
        $users = User::all();

        $vehicleTypes = \App\Models\VehicleType::orderBy('name')->get();
        return view('admin.transfers.create-standalone', compact('accommodations', 'admins', 'users', 'vehicleTypes'));
    }

    /**
     * Store a newly created standalone transfer.
     */
    public function storeStandalone(Request $request)
    {
        $user = auth()->user();

        if (!$user->isSuperAdmin()) {
            if (!$user->isAdmin()) {
                abort(403, 'You do not have permission to create transfers.');
            }
        }

        // Validation for accommodation_id and other fields will be handled by store(), 
        // but we need to validate accommodation_id here first to get the model
        $request->validate([
            'accommodation_id' => 'required|exists:accommodations,id',
        ]);

        $accommodation = Accommodation::findOrFail($request->accommodation_id);

        // Check permissions
        if (!$accommodation->canManageTransfersBy($user)) {
            abort(403, 'You do not have permission to create transfers for this accommodation.');
        }

        // Mark request as standalone for redirect handling
        $request->merge(['_standalone' => true]);

        // Call the existing store method
        return $this->store($request, $accommodation);
    }

    /**
     * Duplicate a transfer.
     */
    public function duplicate(Accommodation $accommodation, Transfer $transfer)
    {
        if (!$accommodation->canManageTransfersBy(auth()->user())) {
            abort(403);
        }

        try {
            $duplicate = $transfer->replicate();
            $duplicate->client_name = $transfer->client_name . ' (Copy)';
            $duplicate->accommodation_id = $accommodation->id;
            $duplicate->created_by = auth()->id();

            // Reset client/organizer-specific fields
            $duplicate->user_id = null;
            $duplicate->organizer_id = null;
            // Reset status to pending for new transfer
            $duplicate->status = 'pending';

            // Copy eTicket file if it exists
            if ($transfer->eticket_path) {
                $duplicate->eticket_path = DualStorageService::copy($transfer->eticket_path, 'transfers/etickets');
            }

            $duplicate->save();

            return redirect()->route('admin.transfers.index', $accommodation)
                ->with('success', 'Transfer duplicated successfully. You can now modify it.');

        } catch (\Exception $e) {
            Log::error('Failed to duplicate transfer', [
                'transfer_id' => $transfer->id,
                'accommodation_id' => $accommodation->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to duplicate transfer: ' . $e->getMessage());
        }
    }

    /**
     * Duplicate a standalone transfer.
     */
    public function duplicateStandalone(Transfer $transfer)
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        try {
            $duplicate = $transfer->replicate();
            $duplicate->client_name = $transfer->client_name . ' (Copy)';
            $duplicate->created_by = auth()->id();

            // Reset client/organizer-specific fields
            $duplicate->user_id = null;
            $duplicate->organizer_id = null;
            // Reset status to pending for new transfer
            $duplicate->status = 'pending';

            // Copy eTicket file if it exists
            if ($transfer->eticket_path) {
                $duplicate->eticket_path = DualStorageService::copy($transfer->eticket_path, 'transfers/etickets');
            }

            $duplicate->save();

            return redirect()->route('admin.transfers.global-index')
                ->with('success', 'Transfer duplicated successfully. You can now modify it.');

        } catch (\Exception $e) {
            Log::error('Failed to duplicate standalone transfer', [
                'transfer_id' => $transfer->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to duplicate transfer: ' . $e->getMessage());
        }
    }
}
