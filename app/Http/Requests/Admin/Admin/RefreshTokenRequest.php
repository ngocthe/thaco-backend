<?php

namespace App\Http\Requests\Admin\Admin;

use App\Http\Requests\BaseApiRequest;

/**
 * @OA\Schema(
 *     title="Refresh token request",
 *     type="object",
 *     required={"refresh_token"}
 * )
 */
class RefreshTokenRequest extends BaseApiRequest
{
    /**
     * @OA\Property(
     *     title="refresh_token",
     *     example="def502002012ca284b08723bdf8359c55716cf5b50d7e44....."
     * )
     *
     * @var string
     */
    public string $refresh_token;

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
            'refresh_token' => 'required',
        ];
    }
}
