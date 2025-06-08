<?php

namespace App\Service;

use Illuminate\Support\Facades\Http;

class OpenWeatherService
{
    //* API DOCUMENT: https://open-meteo.com/en/docs

    private const DEFAULT_COUNTRY_CODE = 'VN';
    private const DEFAULT_COUNTRY_NAME = 'Vietnam';
    private const DEFAULT_TIMEZONE = 'Asia/Ho_Chi_Minh';

    //* this is the default hourly data to be fetched
    // private const DEFAULT_HOURLY = 'temperature_2m,wind_speed_10m';
    private const DEFAULT_HOURLY = 'temperature_2m,precipitation,precipitation_probability';
    private const DEFAULT_COUNT = 10;

    /**
     * Get weather data for a specific location in Vietnam.
     *
     * @param string $locationName
     * @param string|null $startDate
     * @param string|null $endDate
     * @return array|null
     */
    public function getWeatherInVietnam(string $locationName, ?string $startDate = null, ?string $endDate = null): ?array
    {
        // remove 'Vietnam' from the location name
        $locationName = preg_replace('/\s*,\s*Vietnam$/', '', $locationName);
        $coordinates = $this->getCoordinates($locationName);

        if (!$coordinates) {
            return null;
        }

        if ($startDate === null && $endDate === null) {
            return $this->getWeather($coordinates['latitude'], $coordinates['longitude']);
        } else {
            $startDate = date('Y-m-d', strtotime($startDate));
            $endDate = date('Y-m-d', strtotime($endDate));
            return $this->getWeather($coordinates['latitude'], $coordinates['longitude'], $startDate, $endDate);
        }
    }

    /**
     * Get weather data for a specific latitude and longitude.
     *
     * @param float $latitude
     * @param float $longitude
     * @param string|null $startDate
     * @param string|null $endDate
     * @return array|null
     */
    private function getWeather(float $latitude, float $longitude, ?string $startDate = null, ?string $endDate = null): ?array
    {
        $startDate ??= date('Y-m-d');
        $endDate ??= date('Y-m-d', strtotime(`{$startDate} + 14 days`));

        $response = Http::get('https://api.open-meteo.com/v1/forecast', [
            'latitude' => $latitude,
            'longitude' => $longitude,
            'hourly' => self::DEFAULT_HOURLY,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'timezone' => self::DEFAULT_TIMEZONE,
            'temperature_unit' => 'celsius',
            'wind_speed_unit' => 'kmh',
        ]);

        return $response->json();
    }

    /**
     * Get coordinates of a location by its name and country name.
     *
     * @param string $locationName
     * @param string $countryName
     * @return array|null
     */
    private function getCoordinates(string $locationName, string $countryName = self::DEFAULT_COUNTRY_NAME): ?array
    {
        $data = $this->geocode($locationName, $countryName);

        if ($data) {
            if (!empty($data)) {
                return [
                    'name' => $data[0]['display_name'],
                    'latitude' => (float)$data[0]['lat'],
                    'longitude' => (float)$data[0]['lon']
                ];
            }
        }

        return null;
    }

    /**
     * Get geocoding data for a location.
     *
     * @param string $locationName
     * @param string $countryName
     * @param int $count
     * @return array|null
     */
    private function geocode(string $locationName, string $countryName = self::DEFAULT_COUNTRY_NAME, int $count = self::DEFAULT_COUNT): ?array
    {
        $response = Http::get('https://geocode.maps.co/search', [
            'q' => `{$locationName} . '+' . {$countryName}`,
            'count' => $count,
            'api_key' => env('GEOCODE_API_KEY')
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }

    /**
     * Get available locations based on a keyword.
     *
     * @param string $keyword
     * @return array
     */
    public function getAvailableLocations(string $keyword): array
    {
        $data = $this->geocode($keyword);
        if ($data && is_array($data)) {
            return array_map(function ($location) {
                if (!isset($location['display_name'])) {
                    return null;
                }
                return [
                    'name' => $location['display_name'],
                    'admin1' => ''
                ];
            }, $data);
        }

        return [];
    }
}
