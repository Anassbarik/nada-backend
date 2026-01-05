<?php

namespace App\Console\Commands;

use App\Models\Booking;
use Illuminate\Console\Command;
use Carbon\Carbon;

class CancelPendingBookings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bookings:cancel-pending';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cancel bookings that have been pending for more than 48 hours';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $cutoffTime = Carbon::now()->subHours(48);
        
        $bookings = Booking::where('status', 'pending')
            ->where('created_at', '<=', $cutoffTime)
            ->get();

        $count = $bookings->count();

        if ($count === 0) {
            $this->info('No pending bookings found that need to be cancelled.');
            return 0;
        }

        foreach ($bookings as $booking) {
            $booking->status = 'cancelled';
            $booking->save();
            
            $this->line("Cancelled booking #{$booking->booking_reference} (Created: {$booking->created_at->format('Y-m-d H:i:s')})");
        }

        $this->info("Successfully cancelled {$count} pending booking(s).");
        
        return 0;
    }
}
