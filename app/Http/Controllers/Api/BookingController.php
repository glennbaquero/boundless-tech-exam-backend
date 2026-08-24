<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Models\Customer;
use App\Services\OpenStreetMapService;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    /**
     * Submit a booking. Finds or creates the customer by phone, computes
     * distance + travel time, and persists the booking with its stops.
     */
    public function store(StoreBookingRequest $request, OpenStreetMapService $maps): BookingResource
    {
        $data = $request->validated();

        $customer = Customer::findByPhone($data['phone']) ?? Customer::create([
            'phone' => $data['phone'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'] ?? null,
        ]);

        $distance = $maps->distance(
            origin: [
                'address' => $data['pickup_location'],
                'lat' => $data['pickup_lat'] ?? null,
                'lng' => $data['pickup_lng'] ?? null,
            ],
            destination: [
                'address' => $data['dropoff_location'],
                'lat' => $data['dropoff_lat'] ?? null,
                'lng' => $data['dropoff_lng'] ?? null,
            ],
        );

        $booking = DB::transaction(function () use ($customer, $data, $distance) {
            $booking = $customer->bookings()->create([
                'service_type' => $data['service_type'],
                'pickup_date' => $data['pickup_date'],
                'pickup_time' => $data['pickup_time'],
                'pickup_type' => $data['pickup_type'],
                'pickup_location' => $data['pickup_location'],
                'pickup_lat' => $data['pickup_lat'] ?? null,
                'pickup_lng' => $data['pickup_lng'] ?? null,
                'dropoff_type' => $data['dropoff_type'],
                'dropoff_location' => $data['dropoff_location'],
                'dropoff_lat' => $data['dropoff_lat'] ?? null,
                'dropoff_lng' => $data['dropoff_lng'] ?? null,
                'passengers' => $data['passengers'],
                'distance_meters' => $distance['meters'] ?? null,
                'duration_seconds' => $distance['seconds'] ?? null,
                'distance_text' => $distance['distance_text'] ?? null,
                'duration_text' => $distance['duration_text'] ?? null,
            ]);

            foreach ($data['stops'] ?? [] as $position => $stop) {
                $booking->stops()->create([
                    'position' => $position,
                    'location' => $stop['location'],
                    'lat' => $stop['lat'] ?? null,
                    'lng' => $stop['lng'] ?? null,
                ]);
            }

            return $booking;
        });

        return new BookingResource($booking->fresh(['customer', 'stops']));
    }

    public function show(Booking $booking): BookingResource
    {
        return new BookingResource($booking->load(['customer', 'stops']));
    }
}
