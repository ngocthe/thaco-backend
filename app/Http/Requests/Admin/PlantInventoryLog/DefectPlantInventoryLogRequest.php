<?php

namespace App\Http\Requests\Admin\PlantInventoryLog;

use App\Http\Requests\BaseApiRequest;
use App\Rules\BoxListDefect;

/**
 * @OA\Schema(
 *     title="Update Plant Inventory Log Request",
 *     type="object"
 * )
 */
class DefectPlantInventoryLogRequest extends BaseApiRequest
{
    /**
     * @OA\Property()
     * @var string
     */
    public string $defect_id;

    /**
     * @OA\Property(
     *     @OA\Items(
     *          @OA\Property(
     *              property="id",
     *              type="number",
     *              example="1"
     *          ),
     *          @OA\Property(
     *              property="defect_id",
     *              type="string",
     *              example="W,D,X,S"
     *          ),
     *          @OA\Property(
     *              property="part_defect_quantity",
     *              type="number",
     *              example="4"
     *          ),
     *          @OA\Property(
     *              property="remark",
     *              type="string"
     *          ),
     *      )
     * )
     * @var array
     */
    public array $box_list;

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
            'defect_id' => 'nullable|string|in:W,D,X,S',
            'remark' => 'nullable|string|max:255',
            'box_list' => ['required_with:defect_id', 'array', 'max:9999'],
            'box_list.*.id' => 'integer|min:1|max:9999',
            'box_list.*.defect_id' => 'nullable|string|in:W,D,X,S',
            'box_list.*.part_defect_quantity' => 'nullable|integer|min:0|max:9999',
            'box_list.*.remark' => 'nullable|string|max:255',
        ];
    }
}
