<?php

namespace App\Http\Requests\Admin\ShortagePart;

use App\Http\Requests\BaseApiRequest;
/**
 * @OA\Schema(
 *     title="Create Supplier request",
 *     type="object",
 *     required={"plan_date", "part_code", "part_color_code", "plant_code", "remark"}
 * )
 */
class CreateShortagePartRemarkRequest extends BaseApiRequest
{
    /**
     * @OA\Property()
     * @var string
     */
    public string $plan_date;

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
    public int $import_id;

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
            'plan_date' => 'bail|required|date_format:Y-m-d',
            'part_code' => 'bail|required|alpha_num_dash|max:10|reference_check:shortage_parts,part_code,part_color_code,plan_date,import_id,plant_code',
            'part_color_code' => 'bail|required|alpha_num_dash|max:2',
            'plant_code' => 'required|alpha_num_dash|max:5',
            'import_id' => 'required|exists:mrp_production_plan_imports,id,deleted_at,NULL',
            'remark' => 'required|string|max:255'
        ];
    }

    /**
     * @return string[]
     */
    public function messages(): array
    {
        return [
            'part_code.reference_check' => 'Part No, Plant Code are not linked together'
        ];
    }
}
