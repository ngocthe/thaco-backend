<?php

namespace App\Helpers;

use App\Models\MrpWeekDefinition;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class DateTimeHelper
{

    /**
     * @param $date
     * @return Model
     */
    public static function getWeekDefinitionFromDate($date): Model
    {
        $dateObj = Carbon::parse($date);
        return MrpWeekDefinition::query()->whereDate('date', '=', $dateObj)->firstOrFail();
    }

    /**
     * @param $weekDefinition
     * @return array|null
     */
    public static function getMonthYearFromWeekDefinition($weekDefinition): ?array
    {
        if ($weekDefinition && preg_match('/^W{1,1}\d{1,1}-\d{2,2}\/\d{4,4}$/', $weekDefinition)) {
            $weekDefinitionSplit = explode('-', $weekDefinition);
            $monthYearSplit = explode('/', $weekDefinitionSplit[1]);
            return ["month" => $monthYearSplit[0], "year" => $monthYearSplit[1]];
        } else {
            return null;
        }
    }

}
