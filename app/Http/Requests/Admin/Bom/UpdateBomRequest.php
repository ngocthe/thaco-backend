<?php

namespace App\Http\Requests\Admin\Bom;

use App\Http\Requests\BaseApiRequest;
/**
 * @OA\Schema(
 *     title="Update Bom request",
 *     type="object",
 *     required={"msc_code", "shop_code", "part_code", "part_color_code", "ecn_in", "plant_code"}
 * )
 */
class UpdateBomRequest extends BaseApiRequest
{
    /**
     * @OA\Property()
     * @var int
     */
    public int $quantity;

    /**
     * @OA\Property()
     * @var string
     */
    public string $ecn_out;

    /**
     * @OA\Property()
     * @var string
     */
    public string $remark;

    /**
     * @OA\Property()
     * @var string
     */
    public string $part_remarks;
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
			'quantity' => 'bail|required|integer|min:1|max:99999',
            'ecn_out' => 'bail|nullable|alpha_num_dash|max:10|reference_check_plant_code:ecns,code,boms',
            'remark' => 'nullable|string|max:255',
            'part_remarks'=> 'nullable|string|max:50'
        ];
    }

    /**
     * @return string[]
     */
    public function messages(): array
    {
        return [
            'ecn_out.reference_check_plant_code' => 'ECN No. Out, Plant Code are not linked together'
        ];
    }
}
