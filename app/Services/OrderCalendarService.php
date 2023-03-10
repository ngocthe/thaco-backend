<?php

namespace App\Services;

use App\Models\OrderCalendar;

class OrderCalendarService extends BaseService
{
    /**
     * @return string
     */
    public function model(): string
    {
        return OrderCalendar::class;
    }

    /**
     * @var array|string[]
     */
    protected array $defaultRelations = [
        'updatedBy', 'remarkable.updatedBy'
    ];

    /**
     * @param $params
     */
    public function addFilter($params = null)
    {

        if (isset($params['contract_code']) && $this->checkParamFilter($params['contract_code'])) {
            $this->whereLike('contract_code', $params['contract_code']);
        }

        if (isset($params['part_group']) && $this->checkParamFilter($params['part_group'])) {
            $this->whereLike('part_group', $params['part_group']);
        }

    }
}
