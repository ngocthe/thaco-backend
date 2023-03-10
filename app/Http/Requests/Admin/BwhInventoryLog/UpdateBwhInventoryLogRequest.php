<?php

namespace App\Http\Requests\Admin\BwhInventoryLog;

use App\Http\Requests\BaseApiRequest;

/**
 * @OA\Schema(
 *     title="Update Bwh Inventory Log Request",
 *     type="object"
 * )
 */
class UpdateBwhInventoryLogRequest extends BaseApiRequest
{
    /**
     * @OA\Property()
     * @var string
     */
    public string $devanned_date;

    /**
     * @OA\Property()
     * @var string
     */
    public string $stored_date;

    /**
     * @OA\Property()
     * @var string
     */
    public string $warehouse_location_code;

    /**
     * @OA\Property()
     * @var string
     */
    public string $warehouse_code;

    /**
     * @OA\Property()
     * @var string
     */
    public string $shipped_date;

    /**
     * @OA\Property()
     * @var string
     */
    public string $defect_id;

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
        return [
            'container_received' => 'nullable|date_format:Y-m-d',
            'devanned_date' => 'nullable|date_format:Y-m-d',
            'stored_date' => 'nullable|date_format:Y-m-d',
            'warehouse_location_code' => 'nullable|alpha_num_dash|max:8|reference_check_plant_code:warehouse_locations,code,bwh_inventory_logs,warehouse_code',
            'warehouse_code' => 'required|alpha_num_dash|max:8|exists:warehouses,code,deleted_at,NULL,warehouse_type,1|reference_check_plant_code:warehouses,code,bwh_inventory_logs',
			'shipped_date' => 'nullable|date_format:Y-m-d',
            'defect_id' => 'nullable|string|in:W,D,X,S',
            'remark' => 'nullable|string|max:255'
        ];
    }

    /**
     * @return string[]
     */
    public function messages(): array
    {
        return [
            'warehouse_location_code.reference_check' => 'Warehouse Code, Location Code, Plant Code are not linked together',
            'warehouse_code.reference_check_plant_code' => 'Warehouse Code, Plant Code are not linked together'
        ];
    }
}
