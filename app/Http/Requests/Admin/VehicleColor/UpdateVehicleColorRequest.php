<?php

namespace App\Http\Requests\Admin\VehicleColor;

use App\Http\Requests\BaseApiRequest;

/**
 * @OA\Schema(
 *     title="Update vehicle color request",
 *     type="object"
 * )
 */
class UpdateVehicleColorRequest extends BaseApiRequest
{
    /**
     * @OA\Property()
     * @var string
     */
    public string $type;

    /**
     * @OA\Property()
     * @var string
     */
    public string $name;

    /**
     * @OA\Property()
     * @var string
     */
    public string $ecn_out;

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
            'type' => 'bail|required|alpha_num_dash|in:EXT,INT',
            'name' => 'bail|required|string|max:255',
            'ecn_out' => 'bail|nullable|alpha_num_dash|max:10|reference_check_plant_code:ecns,code,vehicle_colors',
            'remark' => 'nullable|string|max:255'
        ];
    }

    /**
     * @return string[]
     */
    public function messages(): array
    {
        return [
            'ecn_out.reference_check_plant_code' => 'ECN No. Out, Plant Code are not linked together'
        ];
    }
}
