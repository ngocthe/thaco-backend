<?php

namespace App\Http\Requests\Admin\Admin;

use App\Http\Requests\BaseApiRequest;
use App\Rules\NoSpaces;

/**
 * @OA\Schema(
 *     title="Login request",
 *     type="object",
 *     required={"username", "password"}
 * )
 */
class LoginRequest extends BaseApiRequest
{
    /**
     * @OA\Property(
     *     title="username",
     *     minLength=3,
     *     example="admin"
     * )
     *
     * @var string
     */
    public string $username;

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
            'username' => ['required', 'min:3', new NoSpaces],
            'password' => 'required',
        ];
    }
}
