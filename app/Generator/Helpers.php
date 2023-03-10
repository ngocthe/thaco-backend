<?php

namespace App\Generator;

use Illuminate\Support\Str;

class Helpers {
    /**
     * Returns an array with the same keys and a default value
     *
     * @param array $ar
     * @param array $fillWith
     * @return array
     */
    public static function justKeys(Array $ar, array $fillWith = []): array
    {
        return array_fill_keys(array_keys($ar), $fillWith);
    }

    /**
     * Makes an array lowercase
     *
     * @param array $ar
     * @return array
     */
    public static function normalize(Array $ar): array
    {
        return array_map('strtolower', $ar);
    }

    /**
     * @param $table
     * @return string
     */
    public static function generateModelName($table): string
    {
        return str_replace('_', '', ucwords(Str::singular($table), '_'));
    }

    /**
     * @param $table
     * @return string
     */
    public static function generateModelNameUpper($table): string
    {
        return strtoupper(Str::singular($table));
    }
}
