<?php

namespace App\Http\Requests\Admin\BoxType;

use App\Http\Requests\BaseApiRequest;
/**
 * @OA\Schema(
 *     title="Update Box type request",
 *     type="object"
 * )
 */
class UpdateBoxTypeRequest extends BaseApiRequest
{
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
			'description' => 'required|string|max:255',
			'weight' => 'required|integer|min:1|max:9999',
			'width' => 'required|integer|min:1|max:9999',
			'height' => 'required|integer|min:1|max:9999',
			'depth' => 'required|integer|min:1|max:9999',
			'quantity' => 'required|integer|min:1|max:9999',
			'unit' => 'required|unit_of_measure',
            'remark' => 'nullable|string|max:255'
        ];
    }
}
