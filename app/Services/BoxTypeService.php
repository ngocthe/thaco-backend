<?php

namespace App\Services;

use App\Models\BoxType;
use App\Models\BwhInventoryLog;
use App\Models\BwhOrderRequest;
use App\Models\InTransitInventoryLog;
use App\Models\OrderPointControl;
use App\Models\PlantInventoryLog;
use App\Models\UpkwhInventoryLog;
use App\Models\VietnamSourceLog;

class BoxTypeService extends BaseService
{
    protected array $fieldRelation = ['box_type_code'];

    protected array $classRelationDelete = [
        OrderPointControl::class,
        InTransitInventoryLog::class,
        VietnamSourceLog::class,
        PlantInventoryLog::class,
        BwhInventoryLog::class,
        UpkwhInventoryLog::class,
        BwhOrderRequest::class
    ];
    /**
     * @return string
     */
    public function model(): string
    {
        return BoxType::class;
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

        if (isset($params['part_code']) && $this->checkParamFilter($params['part_code'])) {
            $this->whereLike('part_code', $params['part_code']);
        }

        $this->addFilterPlantCode($params);

    }

    /**
     * @param $params
     */
    public function addFilterCode($params = null)
    {
        $partCode = $params['part_code'] ?? '';
        $code = $params['part_color_code'] ?? '';
        if ($this->checkParamFilter($partCode) && $this->checkParamFilter($code)) {
            $this->query->where('part_code', $partCode);
        } elseif ($this->checkParamFilter($partCode)) {
            $this->whereLike('part_code', $partCode);
        }

        $this->addFilterPlantCode($params, false);
    }
}
