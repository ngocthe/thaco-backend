<?php

namespace App\Http\Requests\Admin\PartColor;

use App\Http\Requests\BaseApiRequest;
/**
 * @OA\Schema(
 *     title="Update Part Color request",
 *     type="object",
 *     required={"code", "part_code", "name", "plant_code"}
 * )
 */
class UpdatePartColorRequest extends BaseApiRequest
{
    /**
     * @OA\Property()
     * @var string
     */
    public string $name;

    /**
     * @OA\Property()
     * @var string
     */
    public string $interior_code;

    /**
     * @OA\Property()
     * @var string
     */
    public string $vehicle_color_code;

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
            'name' => 'bail|required|string|max:255',
            'interior_code' => 'bail|nullable|alpha_num_dash|max:10|reference_check_plant_code:vehicle_colors,code,part_colors',
            'vehicle_color_code' => 'bail|nullable|alpha_num_dash|max:4|reference_check_plant_code:vehicle_colors,code,part_colors',
            'ecn_out' => 'bail|nullable|alpha_num_dash|max:10|reference_check_plant_code:ecns,code,part_colors',
            'remark' => 'nullable|string|max:255'
        ];
    }

    /**
     * @return string[]
     */
    public function messages(): array
    {
        return [
            'interior_code.reference_check_plant_code' => 'Interior Color Condition, Plant Code are not linked together',
            'vehicle_color_code.reference_check_plant_code' => 'Exterior Color Condition, Plant Code are not linked together',
            'ecn_out.reference_check_plant_code' => 'ECN No. Out, Plant Code are not linked together'
        ];
    }
}
