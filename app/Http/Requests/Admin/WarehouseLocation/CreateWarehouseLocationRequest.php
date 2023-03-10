<?php

namespace App\Http\Requests\Admin\WarehouseLocation;

use App\Http\Requests\BaseApiRequest;

/**
 * @OA\Schema(
 *     title="Create warehouse location request",
 *     type="object",
 *     required={"code", "warehouse_code", "description", "plant_code"}
 * )
 */
class CreateWarehouseLocationRequest extends BaseApiRequest
{
    /**
     * @var array
     */
    protected array $unique_fields = ['code', 'warehouse_code', 'plant_code'];

    /**
     * @var string
     */
    protected string $unique_error_message = 'The code, warehouse code and plant code have already been taken.';

    /**
     * @OA\Property()
     * @var string
     */
    public string $code;

    /**
     * @OA\Property()
     * @var string
     */
    public string $warehouse_code;

    /**
     * @OA\Property()
     * @var string
     */
    public string $description;

    /**
     * @OA\Property()
     * @var string
     */
    public string $plant_code;

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
            'code' => 'bail|required|alpha_num_dash|max:8|unique:warehouse_locations,code,NULL,code,deleted_at,NULL,warehouse_code,' . $this->get('warehouse_code') . ',plant_code,' . $this->get('plant_code'),
			'warehouse_code' => 'bail|required|alpha_num_dash|max:8|reference_check:warehouses,code,plant_code',
			'description' => 'bail|required|string|max:30',
			'plant_code' => 'bail|required|alpha_num_dash|max:5|exists:plants,code,deleted_at,NULL',
            'remark' => 'nullable|string|max:255'
        ];
    }

    /**
     * @return string[]
     */
    public function messages(): array
    {
        return [
            'warehouse_code.reference_check' => 'Warehouse Code, Plant Code are not linked together'
        ];
    }
}
