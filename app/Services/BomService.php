<?php

namespace App\Services;

use App\Models\Bom;

class BomService extends BaseService
{
    /**
     * @return string
     */
    public function model(): string
    {
        return Bom::class;
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

        if (isset($params['msc_code']) && $this->checkParamFilter($params['msc_code'])) {
            $this->whereLike('msc_code', $params['msc_code']);
        }

        if (isset($params['shop_code']) && $this->checkParamFilter($params['shop_code'])) {
            $this->whereLike('shop_code', $params['shop_code']);
        }

        $this->addFilterPartAndPartColor($params);
        $this->addFilterPlantCode($params);

    }

}
