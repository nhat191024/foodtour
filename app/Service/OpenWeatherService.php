<?php

namespace App\Service;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

//* my coworker wrote this service AND ITS SUCKS
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

        Log::info("Fetching weather data for location: {$locationName} with coordinates: " . json_encode($coordinates));

        if (!$coordinates) {
            return null;
        }

        if ($startDate === null && $endDate === null) {
            $weather = $this->getWeather($coordinates['latitude'], $coordinates['longitude']);
        } else {
            $startDate = date('Y-m-d', strtotime($startDate));
            $endDate = date('Y-m-d', strtotime($endDate));
            $weather = $this->getWeather($coordinates['latitude'], $coordinates['longitude'], $startDate, $endDate);
        }

        if (isset($weather['daily'])) {
            return $this->formatDailyWeatherData($weather['daily']);
        }

        return null;
    }

    /**
     * Format daily weather data into an array of days.
     *
     * @param array $daily
     * @return array
     */
    private function formatDailyWeatherData(array $daily): array
    {
        $result = [];
        $count = count($daily['time'] ?? []);
        for ($i = 0; $i < $count; $i++) {
            $weatherCode = $daily['weather_code'][$i] ?? null;
            $result[] = [
                'date' => $daily['time'][$i] ?? null,
                'temperature' => $daily['temperature_2m_max'][$i] ?? null,
                'weather' => $weatherCode !== null ? $this->mapWeatherCodeToString($weatherCode) : null,
            ];
        }
        return $result;
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
            'daily' => 'temperature_2m_max,temperature_2m_min,precipitation_sum,precipitation_hours,weather_code',
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
            'q' => "{$locationName} . '+' . {$countryName}",
            'count' => $count,
            'api_key' => env('GEOCODE_API_KEY')
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }

    /**
     * Map weather code to a human-readable string.
     *
     * @param int $code
     * @return string
     */
    private function mapWeatherCodeToString(int $code): string
    {
        $map = [
            0 => 'Trời quang đãng',
            1 => 'Chủ yếu quang đãng',
            2 => 'Có mây rải rác',
            3 => 'Nhiều mây',
            45 => 'Sương mù',
            48 => 'Sương mù đóng băng',
            51 => 'Mưa phùn nhẹ',
            53 => 'Mưa phùn vừa',
            55 => 'Mưa phùn nặng',
            56 => 'Mưa phùn đóng băng nhẹ',
            57 => 'Mưa phùn đóng băng nặng',
            61 => 'Mưa nhẹ',
            63 => 'Mưa vừa',
            65 => 'Mưa nặng',
            66 => 'Mưa đóng băng nhẹ',
            67 => 'Mưa đóng băng nặng',
            71 => 'Tuyết nhẹ',
            73 => 'Tuyết vừa',
            75 => 'Tuyết nặng',
            77 => 'Tuyết hạt',
            80 => 'Mưa rào nhẹ',
            81 => 'Mưa rào vừa',
            82 => 'Mưa rào nặng',
            85 => 'Mưa tuyết nhẹ',
            86 => 'Mưa tuyết nặng',
            95 => 'Dông',
            96 => 'Dông kèm mưa đá nhẹ',
            99 => 'Dông kèm mưa đá nặng',
        ];
        return $map[$code] ?? 'Không xác định';
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
                    'country' => 'Việt Nam'
                ];
            }, $data);
        }

        return [];
    }
}
