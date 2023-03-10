<?php

namespace App\Services;

use App\Models\Bom;
use App\Models\Msc;
use App\Models\ProductionPlan;

class MscService extends BaseService
{
    protected array $fieldRelation = ['msc_code'];

    protected array $classRelationDelete = [
        Bom::class
    ];
    /**
     * @return string
     */
    public function model(): string
    {
        return Msc::class;
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

        if (isset($params['car_line']) && $this->checkParamFilter($params['car_line'])) {
            $this->whereLike('car_line', $params['car_line']);
        }

        if (isset($params['model_grade']) && $this->checkParamFilter($params['model_grade'])) {
            $this->whereLike('model_grade', $params['model_grade']);
        }

        if (isset($params['body']) && $this->checkParamFilter($params['body'])) {
            $this->whereLike('body', $params['body']);
        }

        $this->addFilterPlantCode($params);

    }

}
