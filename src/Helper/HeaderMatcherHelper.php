<?php

namespace App\Helper;
class HeaderMatcherHelper
{
    private const UNDEFINED = 'undefined';
    private static array $teamHeaders = [
        'Name' => 'name',
        'Logo' => 'logo',
        'Founding Year' => 'foundingYear',
        'Venue' => 'venue',
        'ZVR Number' => 'zvrZahl',
        'Trainer' => 'trainer',
        'City' => 'city',
        'Latitude' => 'lat',
        'Longitude' => 'lon',
    ];

    private static array $playerHeaders = [
        'Name' => 'name',
        'Number' => 'number',
        'Age' => 'age',
        'Position' => 'position',
    ];

    public static function getTeamHeaderKey(string $title): string
    {
        return array_key_exists($title, self::$teamHeaders)
            ? self::$teamHeaders[$title]
            : $title.' '. self::UNDEFINED
        ;
    }

    public static function getPlayerHeaderKey(string $title): string
    {
        return array_key_exists($title, self::$playerHeaders)
            ? self::$playerHeaders[$title]
            : $title.' '. self::UNDEFINED
        ;
    }

    public static function isKeyUndefined(string $key): bool
    {
        return str_contains($key, self::UNDEFINED);
    }

    public static function removeMarker(string $wrongTitle): string
    {
        return str_replace(' '.self::UNDEFINED, '', $wrongTitle);
    }
}
