<?php

namespace App\Services;

class Geo
{
    public static function meters(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $a = sin(deg2rad($lat2 - $lat1) / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin(deg2rad($lon2 - $lon1) / 2) ** 2;

        return 6371000 * 2 * atan2(sqrt(min(1, $a)), sqrt(max(0, 1 - $a)));
    }
}
