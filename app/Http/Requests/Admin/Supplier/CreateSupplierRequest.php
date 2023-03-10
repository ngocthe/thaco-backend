<?php

namespace App\Http\Requests\Admin\Supplier;

use App\Http\Requests\BaseApiRequest;
/**
 * @OA\Schema(
 *     title="Create Supplier request",
 *     type="object",
 *     required={"code", "description", "address", "phone"}
 * )
 */
class CreateSupplierRequest extends BaseApiRequest
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
    public string $address;

    /**
     * @OA\Property()
     * @var string
     */
    public string $phone;

    /**
     * @OA\Property()
     * @var int
     */
    public int $forecast_by_week;

    /**
     * @OA\Property()
     * @var int
     */
    public int $forecast_by_month;

    /**
     * @OA\Property(
     *     @OA\Items(
     *          type="string"
     *      )
     * )
     * @var array
     */
    public array $receiver;

    /**
     * @OA\Property(
     *     @OA\Items(
     *          type="string"
     *      )
     * )
     * @var array
     */
    public array $bcc;

    /**
     * @OA\Property(
     *     @OA\Items(
     *          type="string"
     *      )
     * )
     * @var array
     */
    public array $cc;

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
            'code' => 'bail|required|alpha_num_dash|max:5|unique:suppliers,code,NULL,code,deleted_at,NULL',
			'description' => 'bail|required|string|max:255',
			'address' => 'bail|required|string|max:255',
			'phone' => 'bail|required|max:15|regex:/^([0-9\s\-\+\(\)]*)$/|min:9',
			'forecast_by_week' => 'bail|required|integer|min:1|max:99',
			'forecast_by_month' => 'bail|required|integer|min:1|max:99',
            'receiver' => 'bail|required|array|max:99',
            'receiver.*' => 'email',
            'bcc' => 'bail|required|array|max:99',
            'bcc.*' => 'email',
            'cc' => 'bail|required|array|max:99',
            'cc.*' => 'email',
            'remark' => 'nullable|string|max:255'
        ];
    }
}
