<?php

namespace App\Http\Requests\Admin\Remark;

use App\Http\Requests\BaseApiRequest;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(
 *     title="Create Remark request",
 *     type="object",
 *     required={"modelable_type", "modelable_id", "remark"}
 * )
 */
class RemarkRequest extends BaseApiRequest
{
    /**
     * @OA\Property()
     * @var string
     */
    public string $modelable_type;

    /**
     * @OA\Property()
     * @var int
     */
    public int $modelable_id;

    /**
     * @OA\Property()
     * @var string
     */
    public string $remark;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $modelTypes = [
            'order_point_control',
            'bwh_order_request',
            'bwh_inventory_log',
            'plant_wh_inventory_log',
            'upkwh_inventory_log',
            'in_transit_inventory_log',
            'user',
            'part_group',
            'plant',
            'ecn',
            'vehicle_color',
            'msc',
            'part',
            'part_color',
            'bom',
            'supplier',
            'procurement',
            'warehouse',
            'warehouse_location',
            'box_type',
            'vietnam_source_log',
            'wh_adjustment_summary',
            'wh_adjustment_logical_part',
            'wh_adjustment_logical_msc',
            'mrp_ordering_calendar',
            'order_list',
            'part_usage_result'
        ];
        return [
            'modelable_type' => ['required', Rule::in($modelTypes)],
            'modelable_id' => 'required',
            'remark' => 'required'
        ];
    }
}
