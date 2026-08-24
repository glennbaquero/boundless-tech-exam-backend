<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Booking */
class BookingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'service_type' => $this->service_type,
            'passengers' => $this->passengers,
            'pickup' => [
                'date' => $this->pickup_date->toDateString(),
                'time' => $this->pickup_time,
                'type' => $this->pickup_type,
                'location' => $this->pickup_location,
                'lat' => $this->pickup_lat,
                'lng' => $this->pickup_lng,
                'stops' => $this->stops->map(fn ($stop) => [
                    'location' => $stop->location,
                    'lat' => $stop->lat,
                    'lng' => $stop->lng,
                ]),
            ],
            'dropoff' => [
                'type' => $this->dropoff_type,
                'location' => $this->dropoff_location,
                'lat' => $this->dropoff_lat,
                'lng' => $this->dropoff_lng,
            ],
            'distance' => [
                'meters' => $this->distance_meters,
                'text' => $this->distance_text,
                'duration_seconds' => $this->duration_seconds,
                'duration_text' => $this->duration_text,
            ],
            'customer' => [
                'first_name' => $this->customer->first_name,
                'last_name' => $this->customer->last_name,
                'phone' => $this->customer->phone,
            ],
            'created_at' => $this->created_at,
        ];
    }
}
