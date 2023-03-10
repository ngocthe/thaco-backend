<?php

namespace App\Services;

use App\Models\Bom;
use App\Models\Ecn;
use App\Models\LogicalInventory;
use App\Models\LogicalInventoryMscAdjustment;
use App\Models\LogicalInventoryPartAdjustment;
use App\Models\Msc;
use App\Models\Part;
use App\Models\PartColor;
use App\Models\Plant;

class PlantService extends BaseService
{
    protected array $fieldRelation = ['plant_code'];

    protected array $classRelationDelete = [
        Part::class,
        PartColor::class,
        Msc::class,
        Ecn::class,
        Bom::class,
        LogicalInventory::class,
        LogicalInventoryPartAdjustment::class,
        LogicalInventoryMscAdjustment::class
    ];
    /**
     * @return string
     */
    public function model(): string
    {
        return Plant::class;
    }

    /**
     * @var array|string[]
     */
    protected array $defaultRelations = [
        'updatedBy', 'remarkable.updatedBy'
    ];

}
