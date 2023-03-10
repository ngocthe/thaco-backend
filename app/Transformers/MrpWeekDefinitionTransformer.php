<?php

namespace App\Transformers;

use App\Models\MrpWeekDefinition;
use App\Traits\IncludeUserTrait;
use League\Fractal\TransformerAbstract;

class MrpWeekDefinitionTransformer extends TransformerAbstract
{

    /**
     * @param MrpWeekDefinition $mrpWeekDefinition
     * @return array
     */
    public function transform(MrpWeekDefinition $mrpWeekDefinition): array
    {
        return [
            'id' => $mrpWeekDefinition->id,
            'date' => $mrpWeekDefinition->date,
			'day_off' => $mrpWeekDefinition->day_off,
			'month_no' => $mrpWeekDefinition->month_no,
			'week_no' => $mrpWeekDefinition->week_no
        ];
    }

}
