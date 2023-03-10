<?php

namespace App\Services;

use App\Models\BoxType;
use App\Models\BwhInventoryLog;
use App\Models\BwhOrderRequest;
use App\Models\InTransitInventoryLog;
use App\Models\UpkwhInventoryLog;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class InTransitInventoryLogService extends BaseService
{
    /**
     * @return string
     */
    public function model(): string
    {
        return InTransitInventoryLog::class;
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

        if (isset($params['invoice_code']) && $this->checkParamFilter($params['invoice_code'])) {
            $this->whereLike('invoice_code', $params['invoice_code']);
        }

        if (isset($params['bill_of_lading_code']) && $this->checkParamFilter($params['bill_of_lading_code'])) {
            $this->whereLike('bill_of_lading_code', $params['bill_of_lading_code']);
        }

        if (isset($params['container_code']) && $this->checkParamFilter($params['container_code'])) {
            $this->whereLike('container_code', $params['container_code']);
        }

        if (isset($params['case_code']) && $this->checkParamFilter($params['case_code'])) {
            $this->whereLike('case_code', $params['case_code']);
        }

        if (isset($params['supplier_code']) && $this->checkParamFilter($params['supplier_code'])) {
            $this->whereLike('supplier_code', $params['supplier_code']);
        }

        if (isset($params['container_shipped']) && $this->checkParamDateFilter($params['container_shipped'])) {
            $this->query->where('container_shipped', $params['container_shipped']);
        }

        $this->addFilterPartAndPartColor($params);
        $this->addFilterPlantCode($params);
        $this->addFilterUpdatedAt($params);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param array $attributes
     * @param bool $hasRemark
     * @return Model|bool
     * @throws Exception
     */
    public function store(array $attributes, bool $hasRemark = true)
    {
        $boxType = BoxType::query()
            ->where([
                'code' => $attributes['box_type_code'],
                'part_code' => $attributes['part_code'],
                'plant_code' => $attributes['plant_code'],
            ])->first();
        $attributes['part_quantity'] = $boxType->quantity;
        $attributes['unit'] = $boxType->unit;
        $parent = $this->query->create($attributes);

        if ($hasRemark) {
            $this->createRemark($parent);
        }

        return $parent->push() ? $parent : false;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Model|int $parent
     * @param array $attributes
     * @param bool $hasRemark
     * @return Model|bool
     *
     * @throws Exception
     */
    public function update($parent, array $attributes, bool $hasRemark = true)
    {
        /**
         * @var InTransitInventoryLog $inTransitLog
         */
        $inTransitLog = $this->query->findOrFail($parent);
        if (!$this->canEditAndDelete($inTransitLog)) {
            return false;
        }
        $inTransitLog->fill($attributes);
        $inTransitLog->save();
        $this->createRemark($inTransitLog);
        return $inTransitLog;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $item
     * @param bool $force
     * @return bool
     *
     */
    public function destroy($item, bool $force = false): bool
    {
        /**
         * @var InTransitInventoryLog $inTransitLog
         */
        $inTransitLog = $this->query->findOrFail($item);
        if (!$this->canEditAndDelete($inTransitLog)) {
            return false;
        }
        $inTransitLog->delete();
        return true;
    }

    /**
     * @param InTransitInventoryLog $inTransitLog
     * @return int
     */
    private function canEditAndDelete(InTransitInventoryLog $inTransitLog): int
    {
        $conditions = [
            'contract_code' => $inTransitLog->contract_code,
            'invoice_code' => $inTransitLog->invoice_code,
            'bill_of_lading_code' => $inTransitLog->bill_of_lading_code,
            'container_code' => $inTransitLog->container_code,
            'case_code' => $inTransitLog->case_code,
            'plant_code' => $inTransitLog->plant_code
        ];
        return !BwhInventoryLog::query()
            ->where($conditions)
            ->count();
    }
}
