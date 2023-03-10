<?php

namespace App\Http\Requests\Admin\BwhOrderRequest;

use App\Http\Requests\BaseApiRequest;

/**
 * @OA\Schema(
 *     title="Confirm Bwh order request",
 *     type="object",
 *     required={"received_date", "shelf_location_code", "warehouse_code", "defect_id"}
 * )
 */
class ConfirmBwhOrderRequestRequest extends BaseApiRequest
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
			'received_date' => 'bail|required|date_format:Y-m-d',
            'shelf_location_code' => 'bail|nullable|alpha_num_dash|max:8',
            'warehouse_code' => 'bail|required|alpha_num_dash|max:8|exists:warehouses,code,deleted_at,NULL,warehouse_type,2',
            'defect_id' => 'nullable|string|in:W,D,X,S',
            'remark' => 'nullable|string|max:255'
        ];
    }
}
