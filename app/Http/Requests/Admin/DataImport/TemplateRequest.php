<?php

namespace App\Http\Requests\Admin\DataImport;

use App\Http\Requests\BaseApiRequest;


class TemplateRequest extends BaseApiRequest
{

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
        return [
            'type' => 'required|in:user,part_group,plant,ecn,vehicle_color,msc,part,part_color,bom,supplier,procurement,warehouse,warehouse_location,box_type,in_transit_inventory_log,bwh_inventory_log,upkwh_inventory_log,order_point_control,vietnam_source_log,warehouse_summary_adjustment,warehouse_logical_adjustment_part,warehouse_logical_adjustment_msc,production_plan,part_usage_result,shortage_part,mrp_ordering_calendar'
        ];
    }
}
