<?php

namespace App\Helpers;

class CoordinateConverter
{
    /**
     * Conversion factor from projection units to degrees
     * Based on swisstopo conversion formulas
     */
    private const DEGREES_CONVERSION_FACTOR = 100 / 36;

    /**
     * Convert Swiss LV95 coordinates (E, N) to WGS84 (latitude, longitude)
     * 
     * Based on the approximate formulas from swisstopo
     * E = Easting (Swiss coordinate), N = Northing (Swiss coordinate)
     * 
     * @param float $E Easting coordinate (LV95)
     * @param float $N Northing coordinate (LV95)
     * @return array ['lat' => latitude, 'lng' => longitude]
     */
    public static function lv95ToWgs84(float $E, float $N): array
    {
        // Convert to auxiliary values (shifted and scaled coordinates)
        $y = ($E - 2600000) / 1000000;
        $x = ($N - 1200000) / 1000000;

        // Calculate longitude (lambda) in decimal degrees
        $lambda = 2.6779094
            + 4.728982 * $y
            + 0.791484 * $y * $x
            + 0.1306 * $y * pow($x, 2)
            - 0.0436 * pow($y, 3);

        // Calculate latitude (phi) in decimal degrees
        $phi = 16.9023892
            + 3.238272 * $x
            - 0.270978 * pow($y, 2)
            - 0.002528 * pow($x, 2)
            - 0.0447 * pow($y, 2) * $x
            - 0.0140 * pow($x, 3);

        // Convert to degrees
        $longitude = $lambda * self::DEGREES_CONVERSION_FACTOR;
        $latitude = $phi * self::DEGREES_CONVERSION_FACTOR;

        return [
            'lat' => $latitude,
            'lng' => $longitude
        ];
    }
}
