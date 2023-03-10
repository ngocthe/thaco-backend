<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait WhereInMultiple
{
    /**
     * @param array $columns
     * @param $values
     * @param array $preConditions
     * @param bool $notIn
     * @return Builder
     */
    public static function whereInMultiple(array $columns, $values, array $preConditions = [], $notIn = false): Builder
    {
        $values = array_map(function (array $value) {
            $char = '\\';
            $value = str_replace(
                [$char, '%', '_', '\''],
                [$char . $char, $char . '%', $char . '_', $char . '\''],
                $value
            );
            return "('" . implode("', '", $value) . "')";
        }, $values);

        $query = static::query();
        if (count($preConditions)) {
            $query->where($preConditions);
        }

        if ($notIn) {
            return $query->whereRaw(
                '(' . implode(', ', $columns) . ') not in (' . implode(', ', $values) . ')'
            );
        } else {
            return $query->whereRaw(
                '(' . implode(', ', $columns) . ') in (' . implode(', ', $values) . ')'
            );
        }
    }
}
