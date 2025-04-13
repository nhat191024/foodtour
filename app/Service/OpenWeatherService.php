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
    private const DEFAULT_HOURLY = 'temperature_2m,wind_speed_10m';
    private const DEFAULT_COUNT = 10;

    private function callApi(
        string $locationName,
        string $countryName = self::DEFAULT_COUNTRY_NAME,
        int $count = self::DEFAULT_COUNT
    ): ?array {
        $response = Http::get('https://geocode.maps.co/search', [
            'q' => $locationName . '+' . $countryName,
            'count' => $count,
            //! TODO: move to env later!
            'api_key' => '67f941799a44a086133760rqg8c83b5'
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }

    private function getCoordinates(
        string $locationName,
        string $countryName = self::DEFAULT_COUNTRY_NAME
    ): ?array {
        $data = $this->callApi($locationName, $countryName);

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

    public function getAvailableLocations(string $keyword): array
    {
        $data = $this->callApi($keyword);
        // return $data;
        if ($data && is_array($data)) {
            return array_map(function ($location) {
                if (!isset($location['display_name'])) {
                    return null;
                }
                // $nameParts = explode(',', );
                return [
                    'name' => $location['display_name'],
                    'admin1' => ''
                ];
            }, $data);
        }

        return [];
    }

    private function getWeather(
        float $latitude,
        float $longitude,
        ?string $startDate = null,
        ?string $endDate = null
    ): ?array {
        // return ['error' => 'getting weather', 'latitude' => $latitude, 'longitude' => $longitude];
        $startDate = $startDate ?? date('Y-m-d');
        $endDate = $endDate ?? date('Y-m-d', strtotime($startDate . ' + 14 days'));

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

        if ($response->successful()) {
            return $response->json();
        }
        return ['error' => 'Unable to fetch weather data.'];
        return null;
    }

    public function getWeatherInVietnam(string $locationName, ?string $startDate = null, ?string $endDate = null): ?array
    {
        // remove 'Vietnam' from the location name
        $locationName = preg_replace('/\s*,\s*Vietnam$/', '', $locationName);
        $coordinates = $this->getCoordinates($locationName);

        if (!$coordinates) {
            return ['not correct location'];
            return null;
        }

        if (is_null($startDate) && is_null($endDate)) {
            return $this->getWeather($coordinates['latitude'], $coordinates['longitude']);
        } else {
            $startDate = date('Y-m-d', strtotime($startDate));
            $endDate = date('Y-m-d', strtotime($endDate));
            return $this->getWeather($coordinates['latitude'], $coordinates['longitude'], $startDate, $endDate);
        }
    }
}
