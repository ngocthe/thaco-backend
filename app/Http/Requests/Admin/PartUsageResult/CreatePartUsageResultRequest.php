<?php

namespace App\Http\Requests\Admin\PartUsageResult;

use App\Http\Requests\BaseApiRequest;


/**
 * @OA\Schema(
 *     title="Create part usage result request",
 *     type="object",
 *     required={"used_date", "part_code", "part_color_code", "plant_code", "quantity"}
 * )
 */
class CreatePartUsageResultRequest extends BaseApiRequest
{
    /**
     * @var array
     */
    protected array $unique_fields = [
        'used_date', 'part_code', 'part_color_code', 'plant_code'
    ];

    /**
     * @var string
     */
    protected string $unique_error_message = 'The data have already been taken.';

    /**
     * @OA\Property()
     * @var string
     */
    public string $used_date;

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
    public string $plant_code;

    /**
     * @OA\Property()
     * @var int
     */
    public int $quantity;

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
            'used_date' => 'required|date_format:Y-m-d|before_or_equal:today|unique:part_usage_results,used_date,NULL,used_date,deleted_at,NULL,part_code,' . $this->get('part_code') . ',part_color_code,' . $this->get('part_color_code') . ',plant_code,'. $this->get('plant_code'),
            'part_code' => 'bail|required|alpha_num_dash|max:10|reference_check:parts,code,plant_code',
            'part_color_code' => 'bail|required|alpha_num_dash|max:2|reference_check:part_colors,code,part_code,plant_code',
			'plant_code' => 'required|alpha_num_dash|max:5|exists:plants,code',
			'quantity' => 'required|integer|min:1|max:9999',
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
            'part_color_code.reference_check' => 'Part No, Part Color Code, Plant Code are not linked together'
        ];
    }
}
