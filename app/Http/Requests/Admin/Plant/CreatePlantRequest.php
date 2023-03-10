<?php

namespace App\Http\Requests\Admin\Plant;

use App\Http\Requests\BaseApiRequest;

/**
 * @OA\Schema(
 *     title="Create plant request",
 *     type="object",
 *     required={"code", "description"}
 * )
 */
class CreatePlantRequest extends BaseApiRequest
{
    /**
     * @OA\Property()
     * @var string
     */
    public string $code;

    /**
     * @OA\Property()
     * @var string
     */
    public string $description;

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
            'code' => 'required|alpha_num_dash|max:5|unique:plants,code,NULL,code,deleted_at,NULL',
			'description' => 'required|string|max:255',
            'remark' => 'nullable|string|max:255'
        ];
    }
}
