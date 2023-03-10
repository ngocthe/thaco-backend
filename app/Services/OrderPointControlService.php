<?php

namespace App\Services;

use App\Models\OrderPointControl;
use Exception;
use Illuminate\Database\Eloquent\Model;

class OrderPointControlService extends BaseService
{
    /**
     * @return string
     */
    public function model(): string
    {
        return OrderPointControl::class;
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
        $this->addFilterPartAndPartColor($params);
        $this->addFilterPlantCode($params);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Model|int $parent
     * @param array $attributes
     * @param bool $hasRemark
     * @return array
     *
     * @throws Exception
     */
    public function update($parent, array $attributes, bool $hasRemark = true): array
    {
        /**
         * @var OrderPointControl $orderPoint
         */
        $orderPoint = $this->query->findOrFail($parent);
        $orderPoint->fill($attributes);
        $exists = OrderPointControl::query()
            ->where([
                'part_code' => $orderPoint->part_code,
                'part_color_code' => $orderPoint->part_color_code,
                'box_type_code' => $orderPoint->box_type_code,
                'plant_code' => $orderPoint->plant_code
            ])
            ->first();
        if ($exists && $exists->id != $orderPoint->id) {
            return [false, 'The codes: Part No., Part Color Code, Box Type Code and Plant Code have already been taken.'];
        }
        $orderPoint->save();
        $this->createRemark($orderPoint);
        return [$orderPoint, null];
    }
}
