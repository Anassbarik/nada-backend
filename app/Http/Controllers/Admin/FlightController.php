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

class FlightController extends Controller
{
    /**
     * Display a listing of flights for an accommodation.
     */
    public function index(Accommodation $accommodation)
    {
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
        return view('admin.flights.create', compact('accommodation'));
    }

    /**
     * Store a newly created flight.
     */
    public function store(Request $request, Accommodation $accommodation)
    {
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
            'beneficiary_type' => 'required|in:organizer,client',
            'client_email' => 'nullable|required_if:beneficiary_type,client|email|max:255|unique:users,email',
            'status' => 'required|in:pending,paid',
            'payment_method' => 'nullable|in:wallet,bank,both',
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
                    // Reload flight to get latest data including credentials_pdf_path
                    $flight->refresh();
                    Mail::to($user->email)->send(new \App\Mail\FlightCredentialsMail($flight, $user, $password));
                    Log::info('Flight credentials email sent successfully', [
                        'flight_id' => $flight->id,
                        'user_email' => $user->email,
                    ]);
                } catch (\Throwable $e) {
                    Log::error('Failed to send flight credentials email', [
                        'flight_id' => $flight->id,
                        'user_email' => $user->email,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                    // Don't fail the request if email fails
                }
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
        $flight->load(['user', 'organizer', 'creator', 'bookings']);
        return view('admin.flights.show', compact('accommodation', 'flight'));
    }

    /**
     * Show the form for editing the specified flight.
     */
    public function edit(Accommodation $accommodation, Flight $flight)
    {
        return view('admin.flights.edit', compact('accommodation', 'flight'));
    }

    /**
     * Update the specified flight.
     */
    public function update(Request $request, Accommodation $accommodation, Flight $flight)
    {
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
            'status' => 'required|in:pending,paid',
            'payment_method' => 'nullable|in:wallet,bank,both',
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
     * Remove the specified flight.
     */
    public function destroy(Accommodation $accommodation, Flight $flight)
    {
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
