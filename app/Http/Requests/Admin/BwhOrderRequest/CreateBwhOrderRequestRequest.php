<?php

namespace App\Http\Requests\Admin\BwhOrderRequest;

use App\Http\Requests\BaseApiRequest;

/**
 * @OA\Schema(
 *     title="Create Bwh order request",
 *     type="object",
 *     required={"part_code", "part_color_code", "box_quantity", "plant_code"}
 * )
 */
class CreateBwhOrderRequestRequest extends BaseApiRequest
{
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
     * @var int
     */
    public int $box_quantity;

    /**
     * @OA\Property()
     * @var string
     */
    public string $plant_code;

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
            'box_quantity' => 'bail|required|integer|min:1|max:999',
            'plant_code' => 'bail|required|alpha_num_dash|max:5|exists:plants,code,deleted_at,NULL',
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
