<?php

namespace App\Services;

use App\Models\DefectInventory;

class DefectInventoryService extends BaseService
{
    /**
     * @return string
     */
    public function model(): string
    {
        return DefectInventory::class;
    }

    /**
     * @var array|string[]
     */
    protected array $defaultRelations = [
        'updatedBy'
    ];

    /**
     * @param $params
     */
    public function addFilter($params = null)
    {

    }
}
