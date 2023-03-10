<?php

namespace App\Http\Requests\Admin\Part;

use App\Http\Requests\BaseApiRequest;
/**
 * @OA\Schema(
 *     title="Create Part request",
 *     type="object",
 *     required={"code", "name", "group", "ecn_in", "plant_code"}
 * )
 */
class CreatePartRequest extends BaseApiRequest
{
    /**
     * @var array
     */
    protected array $unique_fields = ['code', 'ecn_in', 'plant_code'];

    /**
     * @var string
     */
    protected string $unique_error_message = 'The code, ecn in code and plant code have already been taken.';

    /**
     * @OA\Property()
     * @var string
     */
    public string $code;

    /**
     * @OA\Property()
     * @var string
     */
    public string $name;

    /**
     * @OA\Property()
     * @var string
     */
    public string $group;

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
            'code' => 'bail|required|alpha_num_dash|max:10|unique:parts,code,NULL,code,deleted_at,NULL,ecn_in,' . $this->get('ecn_in') . ',plant_code,' . $this->get('plant_code'),
			'name' => 'bail|required|string|max:255',
            'group' => 'bail|required|alpha_num_dash|max:2|exists:part_groups,code,deleted_at,NULL',
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
            'ecn_in.reference_check' => 'ECN No. In, Plant Code are not linked together',
            'ecn_out.reference_check' => 'ECN No. Out, Plant Code are not linked together'
        ];
    }
}
