<?php

namespace App\Transformers;

use App\Models\DefectInventory;
use League\Fractal\TransformerAbstract;

class DefectInventoryTransformer extends TransformerAbstract
{

    /**
     * @param DefectInventory $defectInventory
     * @return array
     */
    public function transform(DefectInventory $defectInventory): array
    {
        return [
            'id' => $defectInventory->box_id,
			'defect_id' => $defectInventory->defect_id,
			'part_defect_quantity' => $defectInventory->part_defect_quantity,
            'remark' => $defectInventory->remark,
        ];
    }

}
