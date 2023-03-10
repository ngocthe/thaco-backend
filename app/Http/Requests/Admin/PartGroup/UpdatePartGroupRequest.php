<?php

namespace App\Http\Requests\Admin\PartGroup;

use App\Http\Requests\BaseApiRequest;

/**
 * @OA\Schema(
 *     title="Update part group request",
 *     type="object",
 * )
 */
class UpdatePartGroupRequest extends BaseApiRequest
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
    public int $lead_time;

    /**
     * @OA\Property()
     * @var string
     */
    public string $ordering_cycle;

    /**
     * @OA\Property()
     * @var int
     */
    public int $delivery_lead_time;

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
			'lead_time' => 'nullable|integer|min:1|max:999',
			'ordering_cycle' => 'required|string|min:1|max:1',
            'delivery_lead_time' => 'nullable|integer|min:1|max:99',
            'remark' => 'nullable|string|max:255'
        ];
    }
}
