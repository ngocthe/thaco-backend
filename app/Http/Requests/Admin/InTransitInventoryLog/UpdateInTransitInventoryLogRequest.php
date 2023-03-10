<?php

namespace App\Http\Requests\Admin\InTransitInventoryLog;

use App\Http\Requests\BaseApiRequest;
/**
 * @OA\Schema(
 *     title="Update In Transit Log request",
 *     type="object",
 *     required={"box_quantity", "part_quantity", "unit", "etd", "container_shipped", "eta"}
 * )
 */
class UpdateInTransitInventoryLogRequest extends BaseApiRequest
{
    /**
     * @OA\Property()
     * @var int
     */
    public int $box_quantity;

    /**
     * @OA\Property()
     * @var int
     */
    public int $part_quantity;

    /**
     * @OA\Property()
     * @var string
     */
    public string $unit;

    /**
     * @OA\Property()
     * @var string
     */
    public string $etd;

    /**
     * @OA\Property()
     * @var string
     */
    public string $container_shipped;

    /**
     * @OA\Property()
     * @var string
     */
    public string $eta;

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
            'box_quantity' => 'bail|required|integer|min:1|max:9999',
            'part_quantity' => 'bail|required|integer|min:1|max:9999',
            'unit' => 'required|unit_of_measure',
			'etd' => 'required|date_format:Y-m-d',
			'container_shipped' => 'required|date_format:Y-m-d',
			'eta' => 'required|date_format:Y-m-d',
            'remark' => 'nullable|string|max:255'
        ];
    }
}
