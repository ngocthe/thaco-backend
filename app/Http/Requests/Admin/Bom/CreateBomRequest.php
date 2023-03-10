<?php

namespace App\Http\Requests\Admin\Bom;

use App\Http\Requests\BaseApiRequest;
use App\Rules\BomPartColorCode;

/**
 * @OA\Schema(
 *     title="Create Bom request",
 *     type="object",
 *     required={"msc_code", "shop_code", "part_code", "part_color_code", "ecn_in", "plant_code"}
 * )
 */
class CreateBomRequest extends BaseApiRequest
{
    /**
     * @var array
     */
    protected array $unique_fields = ['msc_code', 'shop_code', 'part_code', 'part_color_code', 'ecn_in', 'plant_code'];

    /**
     * @var string
     */
    protected string $unique_error_message = 'The codes: msc, shop, part, part color and plant have already been taken.';

    /**
     * @OA\Property()
     * @var string
     */
    public string $msc_code;

    /**
     * @OA\Property()
     * @var string
     */
    public string $shop_code;

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
    public int $quantity;

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
            'msc_code' => 'bail|required|alpha_num_dash|max:7|reference_check:mscs,code,plant_code',
			'shop_code' => 'bail|required|alpha_num_dash|max:3|unique:boms,shop_code,NULL,shop_code,deleted_at,NULL,msc_code,'. $this->get('msc_code'). ',part_code,' . $this->get('part_code') . ',part_color_code,' . $this->get('part_color_code'). ',ecn_in,' . $this->get('ecn_in') . ',plant_code,' . $this->get('plant_code'),
			'part_code' => 'bail|required|alpha_num_dash|max:10|reference_check:parts,code,plant_code',
			'part_color_code' => ['bail', 'required', 'alpha_num_dash', 'max:2', new BomPartColorCode],
			'quantity' => 'bail|required|integer|min:1|max:99999',
			'ecn_in' => 'bail|required|alpha_num_dash|max:10|reference_check:ecns,code,plant_code',
			'ecn_out' => 'bail|nullable|alpha_num_dash|max:10|reference_check:ecns,code,plant_code',
			'plant_code' => 'bail|required|alpha_num_dash|max:5|exists:plants,code,deleted_at,NULL',
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
            'msc_code.reference_check' => 'MSC, Plant Code are not linked together',
            'part_code.reference_check' => 'Part No, Plant Code are not linked together',
            'ecn_in.reference_check' => 'ECN No. In, Plant Code are not linked together',
            'ecn_out.reference_check' => 'ECN No. Out, Plant Code are not linked together'
        ];
    }
}
