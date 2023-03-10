<?php

namespace App\Http\Requests\Admin\Admin;

use App\Http\Requests\BaseApiRequest;
use App\Rules\NoSpaces;

/**
 * @OA\Schema(
 *     title="Remove Pass Default request",
 *     type="object",
 *     required={"current_password", "current_password"}
 * )
 */
class ChangePassRequest extends BaseApiRequest
{

    /**
     * @OA\Property(
     *     title="password",
     *     example="123456"
     * )
     *
     * @var string
     */
    public string $current_password;

    /**
     * @OA\Property(
     *     title="password",
     *     example="123456"
     * )
     *
     * @var string
     */
    public string $password;
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
            'password' => 'required',
            'current_password'=>'required'
        ];
    }
}
