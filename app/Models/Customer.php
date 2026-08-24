<?php

namespace App\Models;

use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['phone', 'first_name', 'last_name', 'email'])]
class Customer extends Model
{
    /** @use HasFactory<CustomerFactory> */
    use HasFactory;

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Strip everything but digits and a leading "+" so the same number
     * always matches regardless of spacing/formatting/punctuation.
     */
    public static function normalizePhone(string $phone): string
    {
        return preg_replace('/[^\d+]/', '', $phone) ?? $phone;
    }

    public static function findByPhone(string $phone): ?self
    {
        return static::where('phone', static::normalizePhone($phone))->first();
    }
}
