<?php

namespace App\Services;

use App\Models\PartColor;
use App\Models\Procurement;
use Exception;
use Illuminate\Database\Eloquent\Model;

class ProcurementService extends BaseService
{
    protected array $fieldRelation = ['procurement_code'];

    protected array $classRelationDelete = [];
    /**
     * @return string
     */
    public function model(): string
    {
        return Procurement::class;
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

        if (isset($params['supplier_code']) && $this->checkParamFilter($params['supplier_code'])) {
            $this->whereLike('supplier_code', $params['supplier_code']);
        }

        $this->addFilterPartAndPartColor($params);
        $this->addFilterPlantCode($params);

    }
}
