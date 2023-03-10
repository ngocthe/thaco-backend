<?php

namespace App\Http\Requests\Admin\Warehouse;

use App\Http\Requests\BaseApiRequest;
/**
 * @OA\Schema(
 *     title="Create warehouse request",
 *     type="object",
 *     required={"code", "description", "plant_code"}
 * )
 */
class CreateWarehouseRequest extends BaseApiRequest
{
    /**
     * @var array
     */
    protected array $unique_fields = ['code', 'plant_code'];

    /**
     * @var string
     */
    protected string $unique_error_message = 'The code and plant code have already been taken.';

    /**
     * @OA\Property()
     * @var string
     */
    public string $code;

    /**
     * @OA\Property()
     * @var string
     */
    public string $description;

    /**
     * @OA\Property()
     * @var int
     */
    public int $warehouse_type;

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
            'code' => 'bail|required|alpha_num_dash|max:8|unique:warehouses,code,NULL,code,deleted_at,NULL,plant_code,' . $this->get('plant_code'),
			'description' => 'bail|required|string|max:30',
			'plant_code' => 'bail|required|alpha_num_dash|max:5|exists:plants,code,deleted_at,NULL',
            'warehouse_type' => 'bail|required|integer|in:1,2,3',
            'remark' => 'nullable|string|max:255'
        ];
    }
}
