<?php

namespace App\Http\Requests\Admin\Msc;

use App\Http\Requests\BaseApiRequest;

/**
 * @OA\Schema(
 *     title="Create MSC request",
 *     type="object",
 *     required={"code", "description", "interior_color", "car_line", "model_grade", "body", "engine", "transmission", "plant_code"}
 * )
 */
class CreateMscRequest extends BaseApiRequest
{
    /**
     * @var array
     */
    protected array $unique_fields = ['code', 'plant_code'];

    /**
     * @var string
     */
    protected string $unique_error_message = 'The code and plant code have already been taken.';

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
            'code' => 'bail|required|alpha_num_dash|max:7|unique:mscs,code,NULL,code,deleted_at,NULL,plant_code,' . $this->get('plant_code'),
			'description' => 'bail|required|string|max:255',
			'interior_color' => 'bail|required|string|max:255',
			'car_line' => 'bail|required|string|max:255',
			'model_grade' => 'bail|required|string|max:255',
			'body' => 'bail|required|string|max:255',
			'engine' => 'bail|required|string|max:255',
			'transmission' => 'bail|required|string|max:255',
            'effective_date_in' => 'bail|nullable|date_format:Y-m-d',
            'effective_date_out' => 'bail|nullable|date_format:Y-m-d|after:effective_date_in',
            'plant_code' => 'bail|required|alpha_num_dash|max:5|exists:plants,code,deleted_at,NULL',
            'remark' => 'nullable|string|max:255'
        ];
    }
}
