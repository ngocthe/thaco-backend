<?php

namespace App\Http\Requests\Admin\UpkwhInventoryLog;

use App\Http\Requests\BaseApiRequest;

/**
 * @OA\Schema(
 *     title="Update Upkwh Inventory Log Request",
 *     type="object"
 * )
 */
class UpdateUpkwhInventoryLogRequest extends BaseApiRequest
{
    /**
     * @OA\Property()
     * @var string
     */
    public string $received_date;

    /**
     * @OA\Property()
     * @var string
     */
    public string $shelf_location_code;

    /**
     * @OA\Property()
     * @var string
     */
    public string $warehouse_code;

    /**
     * @OA\Property()
     * @var int
     */
    public int $shipped_box_quantity;

    /**
     * @OA\Property()
     * @var string
     */
    public string $shipped_date;

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
			'received_date' => 'nullable|date_format:Y-m-d',
            'shelf_location_code' => 'nullable|alpha_num_dash|max:8',
            'warehouse_code' => 'required|alpha_num_dash|max:8|exists:warehouses,code,deleted_at,NULL,warehouse_type,2|reference_check_plant_code:warehouses,code,upkwh_inventory_logs',
            'shipped_box_quantity' => 'nullable|integer|min:0|max:9999',
            'shipped_date' => 'nullable|date_format:Y-m-d',
            'remark' => 'nullable|string|max:255'
        ];
    }

    /**
     * @return string[]
     */
    public function messages(): array
    {
        return [
            'warehouse_code.reference_check_plant_code' => 'Warehouse Code, Plant Code are not linked together'
        ];
    }
}
