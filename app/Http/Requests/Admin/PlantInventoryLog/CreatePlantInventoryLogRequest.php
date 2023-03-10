<?php

namespace App\Http\Requests\Admin\PlantInventoryLog;

use App\Http\Requests\BaseApiRequest;

/**
 * @OA\Schema(
 *     title="Update Upkwh Log request",
 *     type="object",
 *     required={"contract_code", "invoice_code", "bill_of_lading_code",
 *          "container_code", "case_code", "part_code", "part_color_code", "box_type_code", "plant_code"
 *      }
 * )
 */
class CreatePlantInventoryLogRequest extends BaseApiRequest
{
    /**
     * @var array
     */
    protected array $unique_fields = ['part_code', 'part_color_code', 'box_type_code', 'received_date', 'warehouse_code', 'plant_code'];

    /**
     * @var string
     */
    protected string $unique_error_message = 'The data have already been taken.';

    /**
     * @OA\Property()
     * @var string
     */
    public string $part_code;

    /**
     * @OA\Property()
     * @var string
     */
    public string $part_color_code;

    /**
     * @OA\Property()
     * @var string
     */
    public string $box_type_code;

    /**
     * @OA\Property()
     * @var string
     */
    public string $received_date;
    /**
     * @OA\Property()
     * @var string
     */
    public string $warehouse_code;

    /**
     * @OA\Property()
     * @var string
     */
    public string $defect_id;

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
            'part_code' => 'bail|required|alpha_num_dash|max:10|reference_check:parts,code,plant_code',
            'part_color_code' => 'bail|required|alpha_num_dash|max:2|reference_check:part_colors,code,part_code,plant_code',
            'box_type_code' => 'bail|required|alpha_num_dash|max:5|reference_check:box_types,code,part_code,plant_code',
            'received_date' => 'required|date_format:Y-m-d',
            'warehouse_code' => 'bail|required|alpha_num_dash|max:8|exists:warehouses,code,deleted_at,NULL,warehouse_type,3|reference_check:warehouses,code,plant_code',
            'defect_id' => 'nullable|string|in:W,D,X,S',
            'plant_code' => 'required|alpha_num_dash|max:5',
            'remark' => 'nullable|string|max:255'
        ];
    }

    /**
     * @return string[]
     */
    public function messages(): array
    {
        return [
            'part_code.reference_check' => 'Part No, Plant Code are not linked together',
            'part_color_code.reference_check' => 'Part No, Part Color Code, Plant Code are not linked together',
            'box_type_code.reference_check' => 'Part No, Box Type Code, Plant Code are not linked together',
            'warehouse_code.reference_check' => 'Warehouse Code, Plant Code are not linked together'
        ];
    }
}
