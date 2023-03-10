<?php

namespace App\Services;

use App\Models\Part;
use App\Models\PartUsageResult;
use App\Models\WarehouseInventorySummary;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PartUsageResultService extends BaseService
{
    /**
     * @return string
     */
    public function model(): string
    {
        return PartUsageResult::class;
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
        if (isset($params['used_date']) && $this->checkParamDateFilter($params['used_date'])) {
            $this->query->whereDate('used_date', '=', $params['used_date']);
        }

        $this->addFilterPartAndPartColor($params);
        $this->addFilterPlantCode($params);

    }

    /**
     * @param array $attributes
     * @param bool $hasRemark
     * @return array
     */
    public function store(array $attributes, bool $hasRemark = true): array
    {
        if ($this->checkUsedDateIsValid($attributes)) {
            $whSummary = WarehouseInventorySummary::query()
                ->where([
                    'part_code' => $attributes['part_code'],
                    'part_color_code' => $attributes['part_color_code'],
                    'plant_code' => $attributes['plant_code'],
                    'warehouse_type' => WarehouseInventorySummary::TYPE_PLANT_WH
                ])->first();

            if (!$whSummary || $whSummary->quantity < $attributes['quantity']) {
                return [false, 'Number must not be greater than current summary'];
            } else {
                $whSummary->quantity -= $attributes['quantity'];
                $whSummary->save();
            }

            $attributes['created_by'] = $attributes['updated_by'] = auth()->id();
            $attributes['deleted_at'] = null;

            DB::beginTransaction();
            try {
                $this->query->upsert(
                    $attributes,
                    ['used_date', 'part_code', 'part_color_code', 'plant_code'],
                    ['quantity', 'deleted_at']
                );
                $partUsage = PartUsageResult::query()->where($attributes)->first();
                if ($hasRemark) {
                    $this->createRemark($partUsage);
                }
                DB::commit();
                return [$partUsage, null];
            } catch (\Exception $exception) {
                DB::rollBack();
                Log::error($exception);
                return [false, $exception->getMessage()];
            }
        } else {
            throw new HttpResponseException(response()->json(
                [
                    'status' => false,
                    'message' => 'The Parts Used Date not between ECN date in-out',
                    'data' => [
                        'used_date' => [
                            'code' => '-1',
                            'message' => 'The Parts Used Date not between ECN date in-out'
                        ]
                    ]
                ], 400));
        }

    }

    /**
     * @param $attributes
     * @return bool
     */
    private function checkUsedDateIsValid($attributes): bool
    {
        $dates = Part::query()
            ->select('ecn_in.actual_line_off_date as ecn_in_date', 'ecn_out.actual_line_off_date as ecn_out_date')
            ->join('ecns as ecn_in', function ($join) {
                $join->on('ecn_in.code', '=', 'parts.ecn_in')
                    ->on('ecn_in.plant_code', '=', 'parts.plant_code');
            })
            ->leftJoin('ecns as ecn_out', function ($join) {
                $join->on('ecn_out.code', '=', 'parts.ecn_out')
                    ->on('ecn_out.plant_code', '=', 'parts.plant_code');
            })
            ->where([
                'parts.code' => $attributes['part_code'],
                'parts.plant_code' => $attributes['plant_code']
            ])
            ->get()
            ->toArray();
        $usedDate = $attributes['used_date'];
        $usedDateIsValid = false;
        foreach ($dates as $date) {
            if ((!$date['ecn_in_date'] || $date['ecn_in_date'] <= $usedDate) && (!$date['ecn_out_date'] || $date['ecn_out_date'] >= $usedDate)) {
                $usedDateIsValid = true;
                break;
            }
        }
        return $usedDateIsValid;
    }

    /**
     * @param Model|int $item
     * @param bool $force
     * @return bool
     *
     */
    public function destroy($item, bool $force = false): bool
    {
        /**
         * @var PartUsageResult $partUsageResult
         */
        $partUsageResult = $this->query->findOrFail($item);
        $partUsageResult->quantity = 0;
        $partUsageResult->save();
        return $partUsageResult->delete();
    }
}
