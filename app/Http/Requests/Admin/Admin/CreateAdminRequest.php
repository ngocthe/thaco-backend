<?php

namespace App\Http\Requests\Admin\Admin;

use App\Constants\Role;
use App\Http\Requests\BaseApiRequest;
use App\Rules\NoSpaces;

/**
 * @OA\Schema(
 *     title="Create Admin request",
 *     type="object",
 *     required={"code", "name", "username", "password"}
 * )
 */
class CreateAdminRequest extends BaseApiRequest
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
    public string $name;

    /**
     * @OA\Property()
     * @var string
     */
    public string $password;

    /**
     * @OA\Property()
     * @var string
     */
    public string $role;

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
            'code' => ['bail', 'required', 'unique:admins,username,NULL,username,deleted_at,NULL', 'min:4', 'max:7', new NoSpaces],
            'name' => 'required|string|max:255',
            'company' => 'nullable|string',
            'password' => 'nullable|string|min:6|max:16',
            'role' => 'required|array|max:2|exists:roles,name',
            'remark' => 'nullable|string|max:255'
        ];
    }
}
