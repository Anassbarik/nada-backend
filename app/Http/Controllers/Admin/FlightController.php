<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Flight;
use App\Models\Accommodation;
use App\Models\Booking;
use App\Models\User;
use App\Services\DualStorageService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\BookingConfirmation;
use App\Models\ResourcePermission;

class FlightController extends Controller
{
    /**
     * Display a listing of flights for an accommodation.
     */
    public function index(Accommodation $accommodation)
    {
        // Check permissions
        if (!$accommodation->canManageFlightsBy(auth()->user())) {
            abort(403, 'You do not have permission to view flights for this accommodation.');
        }

        $flights = Flight::where('accommodation_id', $accommodation->id)
            ->with(['user', 'organizer', 'creator'])
            ->latest()
            ->paginate(20);

        return view('admin.flights.index', compact('accommodation', 'flights'));
    }

    /**
     * Show the form for creating a new flight.
     */
    public function create(Accommodation $accommodation)
    {
        // Check permissions
        if (!$accommodation->canManageFlightsBy(auth()->user())) {
            abort(403, 'You do not have permission to create flights for this accommodation.');
        }

        // Check if user has create permission
        if (!auth()->user()->hasPermission('flights', 'create')) {
            abort(403, 'You do not have permission to create flights.');
        }

        // Get regular admins for flights sub-permissions assignment (only for super-admin)
        $admins = auth()->user()->isSuperAdmin() 
            ? User::where('role', 'admin')->orderBy('name')->get()
            : collect();

        // Get current flights sub-permissions for this accommodation
        $flightsSubPermissions = ResourcePermission::where('resource_type', 'flight')
            ->where('resource_id', $accommodation->id)
            ->pluck('user_id')
            ->toArray();

        return view('admin.flights.create', compact('accommodation', 'admins', 'flightsSubPermissions'));
    }

    /**
     * Store a newly created flight.
     */
    public function store(Request $request, Accommodation $accommodation)
    {
        // Check permissions
        if (!$accommodation->canManageFlightsBy(auth()->user())) {
            abort(403, 'You do not have permission to create flights for this accommodation.');
        }

        // Check if user has create permission
        if (!auth()->user()->hasPermission('flights', 'create')) {
            abort(403, 'You do not have permission to create flights.');
        }

        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'flight_class' => 'required|in:economy,business,first',
            'flight_category' => 'required|in:one_way,round_trip',
            'departure_date' => 'required|date',
            'departure_time' => 'required',
            'arrival_date' => 'required|date|after_or_equal:departure_date',
            'arrival_time' => 'required',
            'departure_flight_number' => 'required|string|max:50',
            'departure_airport' => 'required|string|max:100',
            'arrival_airport' => 'required|string|max:100',
            'departure_price_ttc' => 'required|numeric|min:0',
            'return_date' => 'nullable|required_if:flight_category,round_trip|date|after_or_equal:arrival_date',
            'return_departure_time' => 'nullable|required_if:flight_category,round_trip|required_with:return_date',
            'return_arrival_date' => 'nullable|required_if:flight_category,round_trip|date|after_or_equal:return_date',
            'return_arrival_time' => 'nullable|required_if:flight_category,round_trip|required_with:return_arrival_date',
            'return_flight_number' => 'nullable|required_if:flight_category,round_trip|string|max:50',
            'return_departure_airport' => 'nullable|required_if:flight_category,round_trip|string|max:100',
            'return_arrival_airport' => 'nullable|required_if:flight_category,round_trip|string|max:100',
            'return_price_ttc' => 'nullable|required_if:flight_category,round_trip|numeric|min:0',
            'eticket' => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:10240',
            'eticket_number' => 'nullable|string|max:255',
            'ticket_reference' => 'nullable|string|max:255',
            'beneficiary_type' => 'required|in:organizer,client',
            'client_email' => 'nullable|required_if:beneficiary_type,client|email|max:255|unique:users,email',
            'status' => 'required|in:pending,paid',
            'payment_method' => 'nullable|in:wallet,bank,both',
            'show_flight_prices_public' => 'nullable|boolean',
            'show_flight_prices_client_dashboard' => 'nullable|boolean',
            'show_flight_prices_organizer_dashboard' => 'nullable|boolean',
            'flights_sub_permissions' => 'nullable|array',
            'flights_sub_permissions.*' => 'exists:users,id',
        ]);

        try {
            DB::beginTransaction();

            // Create flight
            $flight = new Flight();
            $flight->accommodation_id = $accommodation->id;
            $flight->full_name = $validated['full_name'];
            $flight->flight_class = $validated['flight_class'];
            $flight->flight_category = $validated['flight_category'];
            $flight->departure_date = $validated['departure_date'];
            $flight->departure_time = $validated['departure_time'];
            $flight->arrival_date = $validated['arrival_date'];
            $flight->arrival_time = $validated['arrival_time'];
            $flight->departure_flight_number = $validated['departure_flight_number'];
            $flight->departure_airport = $validated['departure_airport'];
            $flight->arrival_airport = $validated['arrival_airport'];
            $flight->departure_price_ttc = $validated['departure_price_ttc'];
            $flight->return_date = $validated['return_date'] ?? null;
            $flight->return_departure_time = $validated['return_departure_time'] ?? null;
            $flight->return_arrival_date = $validated['return_arrival_date'] ?? null;
            $flight->return_arrival_time = $validated['return_arrival_time'] ?? null;
            $flight->return_flight_number = $validated['return_flight_number'] ?? null;
            $flight->return_departure_airport = $validated['return_departure_airport'] ?? null;
            $flight->return_arrival_airport = $validated['return_arrival_airport'] ?? null;
            $flight->return_price_ttc = $validated['return_price_ttc'] ?? null;
            $flight->eticket = $validated['eticket_number'] ?? null;
            $flight->ticket_reference = $validated['ticket_reference'] ?? null;
            $flight->beneficiary_type = $validated['beneficiary_type'];
            $flight->status = $validated['status'];
            $flight->payment_method = $validated['payment_method'] ?? null;
            $flight->created_by = auth()->id();

            // Handle beneficiary
            $user = null;
            $password = null;

            if ($validated['beneficiary_type'] === 'organizer') {
                $flight->organizer_id = $accommodation->organizer_id;
                $user = $accommodation->organizer;
                $flight->user_id = $user->id ?? null; // Can be null if organizer doesn't exist
            } else {
                // Only create client user if client_email is provided
                if (!empty($validated['client_email'])) {
                    $password = Str::random(12);
                    $user = User::create([
                        'name' => $validated['full_name'],
                        'email' => $validated['client_email'],
                        'password' => Hash::make($password),
                        'role' => 'user',
                        'email_verified_at' => now(),
                    ]);
                    $flight->user_id = $user->id;
                    $flight->client_email = $validated['client_email'];
                } else {
                    // No user created, user_id will be null
                    $flight->user_id = null;
                }
            }

            // Handle eTicket upload
            if ($request->hasFile('eticket')) {
                $file = $request->file('eticket');
                $filename = 'flight-' . time() . '-' . Str::random(8) . '.' . $file->getClientOriginalExtension();
                $path = DualStorageService::store($file, 'flights/etickets', 'public');
                
                // Rename to desired format
                $newPath = 'flights/etickets/' . $filename;
                if (\Storage::disk('public')->exists($path)) {
                    \Storage::disk('public')->move($path, $newPath);
                    if (file_exists(public_path('storage/' . $path))) {
                        rename(public_path('storage/' . $path), public_path('storage/' . $newPath));
                    }
                } else {
                    $newPath = $path;
                }
                $flight->eticket_path = $newPath;
            }

            $flight->save();

            // Create booking FIRST (before PDF generation so booking reference is available)
            // If no user_id, fill guest fields with flight client info
            // Calculate total flight price
            $flightPrice = (float) $validated['departure_price_ttc'];
            if ($validated['flight_category'] === 'round_trip' && !empty($validated['return_price_ttc'])) {
                $flightPrice += (float) $validated['return_price_ttc'];
            }
            
            // Combine date and time for flight_time datetime field
            $flightDateTime = \Carbon\Carbon::parse($validated['departure_date'] . ' ' . $validated['departure_time']);

            $bookingData = [
                'user_id' => $user->id ?? null, // Nullable - no user account if beneficiary is not client
                'created_by' => auth()->id(),
                'accommodation_id' => $accommodation->id,
                'flight_id' => $flight->id,
                'hotel_id' => null,
                'package_id' => null,
                'full_name' => $validated['full_name'],
                'flight_number' => $validated['departure_flight_number'],
                'flight_date' => $validated['departure_date'],
                'flight_time' => $flightDateTime,
                'status' => 'confirmed',
                'price' => $flightPrice, // Total flight price (departure + return if round trip)
            ];

            // Calculate commission amount
            $commissionAmount = null;
            if ($accommodation->commission_percentage && $accommodation->commission_percentage > 0) {
                $commissionAmount = round(($flightPrice * $accommodation->commission_percentage) / 100, 2);
            }
            $bookingData['commission_amount'] = $commissionAmount;

            // Fill guest fields if no user_id (for manual email sending)
            if (!$user) {
                $bookingData['guest_name'] = $validated['full_name'];
                $bookingData['guest_email'] = $validated['beneficiary_type'] === 'client' ? ($validated['client_email'] ?? null) : null;
                $bookingData['email'] = $bookingData['guest_email'];
            } else {
                $bookingData['email'] = $user->email;
            }

            try {
                $booking = Booking::create($bookingData);
                Log::info('Flight booking created successfully', [
                    'booking_id' => $booking->id,
                    'booking_reference' => $booking->booking_reference,
                    'flight_id' => $flight->id,
                ]);
            } catch (\Throwable $e) {
                Log::error('Failed to create flight booking', [
                    'flight_id' => $flight->id,
                    'booking_data' => $bookingData,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                throw $e; // Re-throw to trigger rollback
            }

            // Generate credentials PDF if client (after booking creation so booking reference is available)
            if ($validated['beneficiary_type'] === 'client' && $user && $password) {
                try {
                    // Reload flight with booking relationship
                    $flight->load('booking');
                    $pdf = Pdf::loadView('admin.flights.credentials', [
                        'flight' => $flight,
                        'booking' => $booking,
                        'user' => $user,
                        'password' => $password,
                    ]);
                    
                    DualStorageService::makeDirectory('flights/credentials');
                    $relativePath = "flights/credentials/{$flight->id}-credentials.pdf";
                    DualStorageService::put($relativePath, $pdf->output(), 'public');
                    $flight->credentials_pdf_path = $relativePath;
                    $flight->save();
                } catch (\Throwable $e) {
                    Log::error('Failed to generate flight credentials PDF', [
                        'flight_id' => $flight->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            }

            DB::commit();

            // Send email to client if created (after commit to avoid blocking)
            if ($validated['beneficiary_type'] === 'client' && $user && $password) {
                try {
                    // Reload flight and booking to get latest data including credentials_pdf_path
                    $flight->refresh();
                    $booking->refresh();
                    Mail::to($user->email)->send(new \App\Mail\FlightCredentialsMail($flight, $user, $password, $booking));
                    Log::info('Flight credentials email sent successfully', [
                        'flight_id' => $flight->id,
                        'booking_id' => $booking->id,
                        'booking_reference' => $booking->booking_reference,
                        'user_email' => $user->email,
                    ]);
                } catch (\Throwable $e) {
                    Log::error('Failed to send flight credentials email', [
                        'flight_id' => $flight->id,
                        'booking_id' => $booking->id ?? null,
                        'user_email' => $user->email,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                    // Don't fail the request if email fails
                }
            }

            // Update accommodation's flight price visibility settings
            if ($request->has('show_flight_prices_public')) {
                $accommodation->show_flight_prices_public = (bool) $request->input('show_flight_prices_public');
            }
            if ($request->has('show_flight_prices_client_dashboard')) {
                $accommodation->show_flight_prices_client_dashboard = (bool) $request->input('show_flight_prices_client_dashboard');
            }
            if ($request->has('show_flight_prices_organizer_dashboard')) {
                $accommodation->show_flight_prices_organizer_dashboard = (bool) $request->input('show_flight_prices_organizer_dashboard');
            }
            $accommodation->save();

            // Handle flights sub-permissions (only for super-admin)
            if (auth()->user()->isSuperAdmin() && isset($validated['flights_sub_permissions'])) {
                // Remove all existing flights sub-permissions for this accommodation
                ResourcePermission::where('resource_type', 'flight')
                    ->where('resource_id', $accommodation->id)
                    ->delete();

                // Add new flights sub-permissions
                foreach ($validated['flights_sub_permissions'] as $adminId) {
                    ResourcePermission::create([
                        'resource_type' => 'flight',
                        'resource_id' => $accommodation->id,
                        'user_id' => $adminId,
                    ]);
                }
            } elseif (auth()->user()->isSuperAdmin() && !$request->has('flights_sub_permissions')) {
                // If no flights_sub_permissions submitted, remove all existing ones
                ResourcePermission::where('resource_type', 'flight')
                    ->where('resource_id', $accommodation->id)
                    ->delete();
            }

            return redirect()->route('admin.flights.index', $accommodation)
                ->with('success', 'Flight created successfully.')
                ->with('credentials_pdf_url', $flight->credentials_pdf_url ?? null);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Failed to create flight', [
                'accommodation_id' => $accommodation->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create flight: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified flight.
     */
    public function show(Accommodation $accommodation, Flight $flight)
    {
        // Check permissions
        if (!$accommodation->canManageFlightsBy(auth()->user())) {
            abort(403, 'You do not have permission to view flights for this accommodation.');
        }

        $flight->load(['user', 'organizer', 'creator', 'bookings']);
        return view('admin.flights.show', compact('accommodation', 'flight'));
    }

    /**
     * Show the form for editing the specified flight.
     */
    public function edit(Accommodation $accommodation, Flight $flight)
    {
        // Check permissions
        if (!$accommodation->canManageFlightsBy(auth()->user())) {
            abort(403, 'You do not have permission to edit flights for this accommodation.');
        }

        // Check if user has edit permission
        if (!auth()->user()->hasPermission('flights', 'edit')) {
            abort(403, 'You do not have permission to edit flights.');
        }

        // Get regular admins for flights sub-permissions assignment (only for super-admin)
        $admins = auth()->user()->isSuperAdmin() 
            ? User::where('role', 'admin')->orderBy('name')->get()
            : collect();

        // Get current flights sub-permissions for this accommodation
        $flightsSubPermissions = ResourcePermission::where('resource_type', 'flight')
            ->where('resource_id', $accommodation->id)
            ->pluck('user_id')
            ->toArray();

        return view('admin.flights.edit', compact('accommodation', 'flight', 'admins', 'flightsSubPermissions'));
    }

    /**
     * Update the specified flight.
     */
    public function update(Request $request, Accommodation $accommodation, Flight $flight)
    {
        // Check permissions
        if (!$accommodation->canManageFlightsBy(auth()->user())) {
            abort(403, 'You do not have permission to update flights for this accommodation.');
        }

        // Check if user has edit permission
        if (!auth()->user()->hasPermission('flights', 'edit')) {
            abort(403, 'You do not have permission to edit flights.');
        }

        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'flight_class' => 'required|in:economy,business,first',
            'flight_category' => 'required|in:one_way,round_trip',
            'departure_date' => 'required|date',
            'departure_time' => 'required',
            'arrival_date' => 'required|date|after_or_equal:departure_date',
            'arrival_time' => 'required',
            'departure_flight_number' => 'required|string|max:50',
            'departure_airport' => 'required|string|max:100',
            'arrival_airport' => 'required|string|max:100',
            'departure_price_ttc' => 'required|numeric|min:0',
            'return_date' => 'nullable|required_if:flight_category,round_trip|date|after_or_equal:arrival_date',
            'return_departure_time' => 'nullable|required_if:flight_category,round_trip|required_with:return_date',
            'return_arrival_date' => 'nullable|required_if:flight_category,round_trip|date|after_or_equal:return_date',
            'return_arrival_time' => 'nullable|required_if:flight_category,round_trip|required_with:return_arrival_date',
            'return_flight_number' => 'nullable|required_if:flight_category,round_trip|string|max:50',
            'return_departure_airport' => 'nullable|required_if:flight_category,round_trip|string|max:100',
            'return_arrival_airport' => 'nullable|required_if:flight_category,round_trip|string|max:100',
            'return_price_ttc' => 'nullable|required_if:flight_category,round_trip|numeric|min:0',
            'eticket' => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:10240',
            'eticket_number' => 'nullable|string|max:255',
            'ticket_reference' => 'nullable|string|max:255',
            'status' => 'required|in:pending,paid',
            'payment_method' => 'nullable|in:wallet,bank,both',
            'show_flight_prices_public' => 'nullable|boolean',
            'show_flight_prices_client_dashboard' => 'nullable|boolean',
            'show_flight_prices_organizer_dashboard' => 'nullable|boolean',
            'flights_sub_permissions' => 'nullable|array',
            'flights_sub_permissions.*' => 'exists:users,id',
        ]);

        try {
            $flight->full_name = $validated['full_name'];
            $flight->flight_class = $validated['flight_class'];
            $flight->flight_category = $validated['flight_category'];
            $flight->departure_date = $validated['departure_date'];
            $flight->departure_time = $validated['departure_time'];
            $flight->arrival_date = $validated['arrival_date'];
            $flight->arrival_time = $validated['arrival_time'];
            $flight->departure_flight_number = $validated['departure_flight_number'];
            $flight->departure_airport = $validated['departure_airport'];
            $flight->arrival_airport = $validated['arrival_airport'];
            $flight->departure_price_ttc = $validated['departure_price_ttc'];
            $flight->return_date = $validated['return_date'] ?? null;
            $flight->return_departure_time = $validated['return_departure_time'] ?? null;
            $flight->return_arrival_date = $validated['return_arrival_date'] ?? null;
            $flight->return_arrival_time = $validated['return_arrival_time'] ?? null;
            $flight->return_flight_number = $validated['return_flight_number'] ?? null;
            $flight->return_departure_airport = $validated['return_departure_airport'] ?? null;
            $flight->return_arrival_airport = $validated['return_arrival_airport'] ?? null;
            $flight->return_price_ttc = $validated['return_price_ttc'] ?? null;
            $flight->eticket = $validated['eticket_number'] ?? null;
            $flight->ticket_reference = $validated['ticket_reference'] ?? null;
            $flight->status = $validated['status'];
            $flight->payment_method = $validated['payment_method'] ?? null;
            
            // Update booking price if exists
            if ($flight->booking) {
                $flightPrice = (float) $validated['departure_price_ttc'];
                if ($validated['flight_category'] === 'round_trip' && !empty($validated['return_price_ttc'])) {
                    $flightPrice += (float) $validated['return_price_ttc'];
                }
                $flight->booking->price = $flightPrice;
                $flight->booking->save();
            }

            // Handle eTicket upload
            if ($request->hasFile('eticket')) {
                // Delete old eTicket
                if ($flight->eticket_path) {
                    DualStorageService::delete($flight->eticket_path, 'public');
                }

                $file = $request->file('eticket');
                $filename = 'flight-' . time() . '-' . Str::random(8) . '.' . $file->getClientOriginalExtension();
                $path = DualStorageService::store($file, 'flights/etickets', 'public');
                
                $newPath = 'flights/etickets/' . $filename;
                if (\Storage::disk('public')->exists($path)) {
                    \Storage::disk('public')->move($path, $newPath);
                    if (file_exists(public_path('storage/' . $path))) {
                        rename(public_path('storage/' . $path), public_path('storage/' . $newPath));
                    }
                } else {
                    $newPath = $path;
                }
                $flight->eticket_path = $newPath;
            }

            $flight->save();

            // Update accommodation's flight price visibility settings
            if ($request->has('show_flight_prices_public')) {
                $accommodation->show_flight_prices_public = (bool) $request->input('show_flight_prices_public');
            }
            if ($request->has('show_flight_prices_client_dashboard')) {
                $accommodation->show_flight_prices_client_dashboard = (bool) $request->input('show_flight_prices_client_dashboard');
            }
            if ($request->has('show_flight_prices_organizer_dashboard')) {
                $accommodation->show_flight_prices_organizer_dashboard = (bool) $request->input('show_flight_prices_organizer_dashboard');
            }
            $accommodation->save();

            // Handle flights sub-permissions (only for super-admin)
            if (auth()->user()->isSuperAdmin()) {
                // Remove all existing flights sub-permissions for this accommodation
                ResourcePermission::where('resource_type', 'flight')
                    ->where('resource_id', $accommodation->id)
                    ->delete();

                // Add new flights sub-permissions
                if (isset($validated['flights_sub_permissions'])) {
                    foreach ($validated['flights_sub_permissions'] as $adminId) {
                        ResourcePermission::create([
                            'resource_type' => 'flight',
                            'resource_id' => $accommodation->id,
                            'user_id' => $adminId,
                        ]);
                    }
                }
            }

            return redirect()->route('admin.flights.index', $accommodation)
                ->with('success', 'Flight updated successfully.');

        } catch (\Throwable $e) {
            Log::error('Failed to update flight', [
                'flight_id' => $flight->id,
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to update flight: ' . $e->getMessage()]);
        }
    }

    /**
     * Duplicate a flight.
     */
    public function duplicate(Accommodation $accommodation, Flight $flight)
    {
        // Check permissions
        if (!$accommodation->canManageFlightsBy(auth()->user())) {
            abort(403, 'You do not have permission to duplicate flights for this accommodation.');
        }

        // Check if user has create permission
        if (!auth()->user()->hasPermission('flights', 'create')) {
            abort(403, 'You do not have permission to create flights.');
        }

        try {
            $duplicate = $flight->replicate();
            $duplicate->full_name = $flight->full_name . ' (Copy)';
            // reference will be auto-generated in the model's boot method
            $duplicate->reference = null; // Let the model generate a new one
            $duplicate->accommodation_id = $accommodation->id;
            $duplicate->created_by = auth()->id();
            
            // Reset client/organizer-specific fields (will be set when creating new client/organizer if needed)
            $duplicate->user_id = null;
            $duplicate->organizer_id = null;
            $duplicate->client_email = null;
            $duplicate->credentials_pdf_path = null;
            $duplicate->credentials_emailed = false;
            $duplicate->client_password_generated = false;
            // Reset status to pending for new flight
            $duplicate->status = 'pending';
            
            // Copy eTicket file if it exists
            if ($flight->eticket_path) {
                $duplicate->eticket_path = DualStorageService::copy($flight->eticket_path, 'flights/etickets', 'public');
            }
            
            $duplicate->save();

            return redirect()->route('admin.flights.index', $accommodation)
                ->with('success', 'Flight duplicated successfully. You can now modify it.');

        } catch (\Throwable $e) {
            Log::error('Failed to duplicate flight', [
                'flight_id' => $flight->id,
                'accommodation_id' => $accommodation->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()
                ->withErrors(['error' => 'Failed to duplicate flight: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified flight.
     */
    public function destroy(Accommodation $accommodation, Flight $flight)
    {
        // Check permissions
        if (!$accommodation->canManageFlightsBy(auth()->user())) {
            abort(403, 'You do not have permission to delete flights for this accommodation.');
        }

        // Check if user has delete permission
        if (!auth()->user()->hasPermission('flights', 'delete')) {
            abort(403, 'You do not have permission to delete flights.');
        }

        try {
            // Delete associated files
            if ($flight->eticket_path) {
                DualStorageService::delete($flight->eticket_path, 'public');
            }
            if ($flight->credentials_pdf_path) {
                DualStorageService::delete($flight->credentials_pdf_path, 'public');
            }

            $flight->delete();

            return redirect()->route('admin.flights.index', $accommodation)
                ->with('success', 'Flight deleted successfully.');

        } catch (\Throwable $e) {
            Log::error('Failed to delete flight', [
                'flight_id' => $flight->id,
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withErrors(['error' => 'Failed to delete flight: ' . $e->getMessage()]);
        }
    }

    /**
     * Download credentials PDF.
     */
    public function downloadCredentials(Accommodation $accommodation, Flight $flight)
    {
        if (!$flight->credentials_pdf_path) {
            abort(404, 'Credentials PDF not found.');
        }

        $path = storage_path('app/public/' . $flight->credentials_pdf_path);
        
        if (!file_exists($path)) {
            $path = public_path('storage/' . $flight->credentials_pdf_path);
        }

        if (!file_exists($path)) {
            abort(404, 'Credentials PDF file not found.');
        }

        $filename = 'flight-credentials-' . $flight->reference . '.pdf';
        
        return response()->download($path, $filename);
    }
}
