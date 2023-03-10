<?php

namespace App\Http\Requests\Admin\Ecn;

use App\Http\Requests\BaseApiRequest;

/**
 * @OA\Schema(
 *     title="Create ENC request",
 *     type="object",
 *     required={"code", "page_number", "line_number", "description", "plant_code"}
 * )
 */
class CreateEcnRequest extends BaseApiRequest
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
     * @var int
     */
    public int $page_number;

    /**
     * @OA\Property()
     * @var int
     */
    public int $line_number;

    /**
     * @OA\Property()
     * @var string
     */
    public string $description;

    /**
     * @OA\Property()
     * @var string
     */
    public string $mandatory_level;

    /**
     * @OA\Property()
     * @var string
     */
    public string $production_interchangeability;

    /**
     * @OA\Property()
     * @var string
     */
    public string $service_interchangeability;

    /**
     * @OA\Property()
     * @var string
     */
    public string $released_party;

    /**
     * @OA\Property()
     * @var string
     */
    public string $released_date;

    /**
     * @OA\Property()
     * @var string
     */
    public string $planned_line_off_date;

    /**
     * @OA\Property()
     * @var string
     */
    public string $actual_line_off_date;

    /**
     * @OA\Property()
     * @var string
     */
    public string $planned_packing_date;

    /**
     * @OA\Property()
     * @var string
     */
    public string $actual_packing_date;

    /**
     * @OA\Property()
     * @var string
     */
    public string $vin;

    /**
     * @OA\Property()
     * @var string
     */
    public string $complete_knockdown;

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
            'code' => 'bail|required|alpha_num_dash|max:10|unique:ecns,code,NULL,code,deleted_at,NULL,plant_code,' . $this->get('plant_code'),
            'page_number' => 'bail|required|integer|min:1|max:999',
            'line_number' => 'bail|required|integer|min:1|max:999',
            'description' => 'bail|required|string|max:30',
            'mandatory_level' => 'bail|required|in:M,N',
            'production_interchangeability' => 'bail|nullable|alpha_num|max:1',
            'service_interchangeability' => 'bail|nullable|alpha_num|max:1',
            'released_party' => 'bail|nullable|alpha_num|max:5',
            'released_date' => 'bail|nullable|date_format:Y-m-d',
            'planned_line_off_date' => 'bail|nullable|date_format:Y-m-d',
            'actual_line_off_date' => 'bail|nullable|date_format:Y-m-d|after_or_equal:actual_packing_date',
            'planned_packing_date' => 'bail|nullable|date_format:Y-m-d',
            'actual_packing_date' => 'bail|nullable|date_format:Y-m-d',
            'vin' => 'bail|nullable|alpha_num_dash|max:17',
            'complete_knockdown' => 'bail|nullable|alpha_num_dash|max:13',
            'plant_code' => 'bail|required|alpha_num_dash|max:5|exists:plants,code,deleted_at,NULL',
            'remark' => 'nullable|string|max:255'
        ];
    }
}
