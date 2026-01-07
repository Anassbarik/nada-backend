<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Voucher extends Model
{
    protected $fillable = [
        'booking_id',
        'user_id',
        'voucher_number',
        'pdf_path',
        'emailed',
    ];

    protected $casts = [
        'emailed' => 'boolean',
    ];

    /**
     * Get the booking that owns the voucher.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get the user that owns the voucher.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
