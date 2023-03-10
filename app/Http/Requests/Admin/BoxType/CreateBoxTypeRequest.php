<?php

namespace App\Http\Requests\Admin\BoxType;

use App\Http\Requests\BaseApiRequest;
/**
 * @OA\Schema(
 *     title="Create Box type request",
 *     type="object",
 *     required={"code", "part_code", "description", "weight", "width", "height", "depth", "quantity", "unit", "plant_code"}
 * )
 */
class CreateBoxTypeRequest extends BaseApiRequest
{
    /**
     * @var array
     */
    protected array $unique_fields = ['code', 'part_code', 'plant_code'];

    /**
     * @var string
     */
    protected string $unique_error_message = 'The codes: box type, part and plant have already been taken.';

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
    public string $description;

    /**
     * @OA\Property()
     * @var int
     */
    public int $weight;

    /**
     * @OA\Property()
     * @var int
     */
    public int $width;

    /**
     * @OA\Property()
     * @var int
     */
    public int $height;

    /**
     * @OA\Property()
     * @var int
     */
    public int $depth;

    /**
     * @OA\Property()
     * @var int
     */
    public int $quantity;

    /**
     * @OA\Property()
     * @var string
     */
    public string $unit;

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
            'code' => 'required|alpha_num_dash|max:5|unique:box_types,code,NULL,code,deleted_at,NULL,part_code,'. $this->get('part_code'). ',plant_code,' . $this->get('plant_code'),
			'part_code' => 'required|alpha_num_dash|max:10|reference_check:parts,code,plant_code',
			'description' => 'required|string|max:255',
			'weight' => 'required|integer|min:1|max:9999',
			'width' => 'required|integer|min:1|max:9999',
			'height' => 'required|integer|min:1|max:9999',
			'depth' => 'required|integer|min:1|max:9999',
			'quantity' => 'required|integer|min:1|max:9999',
			'unit' => 'required|unit_of_measure',
			'plant_code' => 'required|alpha_num_dash|max:5|exists:plants,code,deleted_at,NULL',
            'remark' => 'nullable|string|max:255'
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
