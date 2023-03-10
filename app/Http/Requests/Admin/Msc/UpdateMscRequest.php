<?php

namespace App\Http\Requests\Admin\Msc;

use App\Http\Requests\BaseApiRequest;
/**
 * @OA\Schema(
 *     title="Update MSC request",
 *     type="object"
 * )
 */
class UpdateMscRequest extends BaseApiRequest
{
    /**
     * @OA\Property()
     * @var string
     */
    public string $description;

    /**
     * @OA\Property()
     * @var string
     */
    public string $interior_color;

    /**
     * @OA\Property()
     * @var string
     */
    public string $car_line;

    /**
     * @OA\Property()
     * @var string
     */
    public string $model_grade;

    /**
     * @OA\Property()
     * @var string
     */
    public string $body;

    /**
     * @OA\Property()
     * @var string
     */
    public string $engine;

    /**
     * @OA\Property()
     * @var string
     */
    public string $transmission;

    /**
     * @OA\Property()
     * @var string
     */
    public string $effective_date_in;

    /**
     * @OA\Property()
     * @var string
     */
    public string $effective_date_out;

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
			'interior_color' => 'required|string|max:255',
			'car_line' => 'required|string|max:255',
			'model_grade' => 'required|string|max:255',
			'body' => 'required|string|max:255',
			'engine' => 'required|string|max:255',
			'transmission' => 'required|string|max:255',
			'effective_date_in' => 'nullable|date_format:Y-m-d',
			'effective_date_out' => 'nullable|date_format:Y-m-d',
            'remark' => 'nullable|string|max:255'
        ];
    }
}
