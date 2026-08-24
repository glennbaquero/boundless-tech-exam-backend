<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Resolves distance + travel time using the free OpenStreetMap stack —
 * no API key, no billing:
 *
 *  1. Nominatim geocodes any address that arrives without lat/lng.
 *  2. The public OSRM router computes real road distance + duration.
 *  3. A straight-line Haversine estimate is the last resort if OSRM
 *     (a public demo service) is unreachable.
 */
class OpenStreetMapService
{
    /**
     * @param  array{address?: string, lat?: float|null, lng?: float|null}  $origin
     * @param  array{address?: string, lat?: float|null, lng?: float|null}  $destination
     * @return array{meters: int, distance_text: string, seconds: int, duration_text: string, source: string}|null
     */
    public function distance(array $origin, array $destination): ?array
    {
        $origin = $this->ensureCoordinates($origin);
        $destination = $this->ensureCoordinates($destination);

        if (! $origin || ! $destination) {
            return null;
        }

        return $this->viaOsrm($origin, $destination)
            ?? $this->viaHaversine($origin, $destination);
    }

    /**
     * Geocode an address via Nominatim, the free OpenStreetMap search API.
     *
     * @return array{lat: float, lng: float}|null
     */
    public function geocode(string $address): ?array
    {
        try {
            $response = Http::timeout(5)
                // Nominatim's usage policy requires an identifying User-Agent.
                ->withHeaders(['User-Agent' => config('app.name').' Booking Form'])
                ->get('https://nominatim.openstreetmap.org/search', [
                    'q' => $address,
                    'format' => 'json',
                    'limit' => 1,
                ]);

            $result = $response->json('0');

            if ($response->failed() || ! $result) {
                return null;
            }

            return [
                'lat' => (float) $result['lat'],
                'lng' => (float) $result['lon'],
            ];
        } catch (Throwable $e) {
            Log::warning('Nominatim geocoding request failed.', ['error' => $e->getMessage()]);

            return null;
        }
    }

    private function ensureCoordinates(array $point): ?array
    {
        if ($this->hasCoordinates($point)) {
            return $point;
        }

        if (empty($point['address'])) {
            return null;
        }

        $coordinates = $this->geocode($point['address']);

        return $coordinates ? [...$point, ...$coordinates] : null;
    }

    private function viaOsrm(array $origin, array $destination): ?array
    {
        try {
            $url = sprintf(
                'https://router.project-osrm.org/route/v1/driving/%s,%s;%s,%s',
                $origin['lng'],
                $origin['lat'],
                $destination['lng'],
                $destination['lat'],
            );

            $response = Http::timeout(5)->get($url, ['overview' => 'false']);
            $route = $response->json('routes.0');

            if ($response->failed() || ! $route) {
                return null;
            }

            $meters = (int) round($route['distance']);
            $seconds = (int) round($route['duration']);

            return [
                'meters' => $meters,
                'distance_text' => $this->formatDistance($meters),
                'seconds' => $seconds,
                'duration_text' => $this->formatDuration($seconds),
                'source' => 'osrm',
            ];
        } catch (Throwable $e) {
            Log::warning('OSRM routing request failed, falling back.', ['error' => $e->getMessage()]);

            return null;
        }
    }

    private function viaHaversine(array $origin, array $destination): array
    {
        $earthRadiusMeters = 6_371_000;
        $latDelta = deg2rad($destination['lat'] - $origin['lat']);
        $lngDelta = deg2rad($destination['lng'] - $origin['lng']);

        $a = sin($latDelta / 2) ** 2
            + cos(deg2rad($origin['lat'])) * cos(deg2rad($destination['lat'])) * sin($lngDelta / 2) ** 2;

        $meters = (int) round($earthRadiusMeters * 2 * atan2(sqrt($a), sqrt(1 - $a)));

        // Straight-line distance only, so pad the average speed assumption
        // (40 km/h) to keep the ETA in the right ballpark for real roads.
        $seconds = (int) round($meters / (40 * 1000 / 3600));

        return [
            'meters' => $meters,
            'distance_text' => $this->formatDistance($meters),
            'seconds' => $seconds,
            'duration_text' => $this->formatDuration($seconds),
            'source' => 'estimated',
        ];
    }

    private function hasCoordinates(array $point): bool
    {
        return isset($point['lat'], $point['lng']);
    }

    private function formatDistance(int $meters): string
    {
        return round($meters / 1609.34, 1).' mi';
    }

    private function formatDuration(int $seconds): string
    {
        $minutes = max(1, (int) round($seconds / 60));

        if ($minutes < 60) {
            return "{$minutes} mins";
        }

        return sprintf('%d hr %d min', intdiv($minutes, 60), $minutes % 60);
    }
}
