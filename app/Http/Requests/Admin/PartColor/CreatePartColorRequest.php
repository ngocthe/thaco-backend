<?php

namespace App\Http\Requests\Admin\PartColor;

use App\Http\Requests\BaseApiRequest;
use App\Models\PartColor;
use App\Rules\ConstraintCheck;

/**
 * @OA\Schema(
 *     title="Create Part Color request",
 *     type="object",
 *     required={"code", "part_code", "name", "plant_code"}
 * )
 */
class CreatePartColorRequest extends BaseApiRequest
{
    /**
     * @var array
     */
    protected array $unique_fields = ['code', 'part_code', 'ecn_in', 'plant_code'];

    /**
     * @var string
     */
    protected string $unique_error_message = 'The code, part code, ecn in code and plant code have already been taken.';

    /**
     * @OA\Property()
     * @var string
     */
    public string $code;

    /**
     * @OA\Property()
     * @var string
     */
    public string $part_code;

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
    public string $ecn_in;

    /**
     * @OA\Property()
     * @var string
     */
    public string $ecn_out;

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
            'code' => 'bail|required|alpha_num_dash|max:2|not_in:XX,xx,xX,Xx|unique:part_colors,code,NULL,code,deleted_at,NULL,part_code,'. $this->get('part_code'). ',ecn_in,' . $this->get('ecn_in') . ',plant_code,' . $this->get('plant_code'),
			'part_code' => 'bail|required|alpha_num_dash|max:10|reference_check:parts,code,plant_code',
			'name' => 'bail|required|string|max:255',
			'interior_code' => 'bail|nullable|alpha_num_dash|max:10|reference_check:vehicle_colors,code,plant_code',
			'vehicle_color_code' => 'bail|nullable|alpha_num_dash|max:4|reference_check:vehicle_colors,code,plant_code',
			'ecn_in' => 'bail|required|alpha_num_dash|max:10|reference_check:ecns,code,plant_code',
			'ecn_out' => 'bail|nullable|alpha_num_dash|max:10|reference_check:ecns,code,plant_code',
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
            'part_code.reference_check' => 'Part No, Plant Code are not linked together',
            'interior_code.reference_check' => 'Interior Color Condition, Plant Code are not linked together',
            'vehicle_color_code.reference_check' => 'Exterior Color Condition, Plant Code are not linked together',
            'ecn_in.reference_check' => 'ECN No. In, Plant Code are not linked together',
            'ecn_out.reference_check' => 'ECN No. Out, Plant Code are not linked together'
        ];
    }
}
