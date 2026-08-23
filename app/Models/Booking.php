<?php

namespace App\Models;

use App\Enums\BookingStatus;
use App\Enums\LocationType;
use App\Enums\ServiceType;
use Database\Factories\BookingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'customer_id',
    'service_type',
    'pickup_date',
    'pickup_time',
    'pickup_type',
    'pickup_location',
    'pickup_lat',
    'pickup_lng',
    'dropoff_type',
    'dropoff_location',
    'dropoff_lat',
    'dropoff_lng',
    'passengers',
    'distance_meters',
    'duration_seconds',
    'distance_text',
    'duration_text',
    'status',
])]
class Booking extends Model
{
    /** @use HasFactory<BookingFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'service_type' => ServiceType::class,
            'pickup_type' => LocationType::class,
            'dropoff_type' => LocationType::class,
            'status' => BookingStatus::class,
            'pickup_date' => 'date',
            'pickup_lat' => 'decimal:7',
            'pickup_lng' => 'decimal:7',
            'dropoff_lat' => 'decimal:7',
            'dropoff_lng' => 'decimal:7',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function stops(): HasMany
    {
        return $this->hasMany(BookingStop::class)->orderBy('position');
    }
}
