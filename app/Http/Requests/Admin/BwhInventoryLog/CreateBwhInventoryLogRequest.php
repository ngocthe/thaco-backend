<?php

namespace App\Http\Requests\Admin\BwhInventoryLog;

use App\Http\Requests\BaseApiRequest;

/**
 * @OA\Schema(
 *     title="Update BWH Log request",
 *     type="object",
 *     required={"contract_code", "invoice_code", "bill_of_lading_code",
 *          "container_code", "case_code", "part_code", "part_color_code", "box_type_code", "plant_code"
 *      }
 * )
 */
class CreateBwhInventoryLogRequest extends BaseApiRequest
{
    /**
     * @var array
     */
    protected array $unique_fields = [
        'contract_code', 'invoice_code', 'bill_of_lading_code', 'container_code', 'case_code', 'plant_code'
    ];

    /**
     * @var string
     */
    protected string $unique_error_message = 'The data have already been taken.';

    /**
     * @OA\Property()
     * @var string
     */
    public string $contract_code;

    /**
     * @OA\Property()
     * @var string
     */
    public string $invoice_code;

    /**
     * @OA\Property()
     * @var string
     */
    public string $bill_of_lading_code;

    /**
     * @OA\Property()
     * @var string
     */
    public string $container_code;

    /**
     * @OA\Property()
     * @var string
     */
    public string $case_code;

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
    public string $container_received;

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
            'contract_code' => 'required|string|max:9|unique:bwh_inventory_logs,contract_code,NULL,contract_code,deleted_at,NULL,invoice_code,' . $this->get('invoice_code') . ',bill_of_lading_code,' . $this->get('bill_of_lading_code') . ',container_code,' . $this->get('container_code') . ',case_code,' . $this->get('case_code') . ',plant_code,'. $this->get('plant_code'),
            'invoice_code' => 'bail|required|string|max:10',
            'bill_of_lading_code' => 'bail|required|string|max:13',
            'container_code' => 'bail|required|string|max:11',
            'case_code' => 'bail|required|string|max:2',
            'container_received' => 'nullable|date_format:Y-m-d',
            'devanned_date' => 'nullable|date_format:Y-m-d',
            'stored_date' => 'nullable|date_format:Y-m-d',
            'warehouse_location_code' => 'nullable|alpha_num_dash|max:8|reference_check:warehouse_locations,code,warehouse_code,plant_code',
            'warehouse_code' => 'required|alpha_num_dash|max:8|exists:warehouses,code,deleted_at,NULL,warehouse_type,1|reference_check:warehouses,code,plant_code',
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
            'warehouse_location_code.reference_check' => 'Warehouse Code, Location Code, Plant Code are not linked together',
            'warehouse_code.reference_check' => 'Warehouse Code, Plant Code are not linked together'
        ];
    }
}
