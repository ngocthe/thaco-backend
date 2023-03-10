<?php

namespace App\Http\Requests\Admin\MrpWeekDefinition;

use App\Http\Requests\BaseApiRequest;

/**
 * @OA\Schema(
 *     title="Create MrpWeekDefinition request",
 *     type="object",
 *     required={"date"}
 * )
 */
class CreateMrpWeekDefinitionRequest extends BaseApiRequest
{
    /**
     * @OA\Property()
     * @var string
     */
    public string $date;

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
            'date' => 'required|date_format:Y-m-d'
        ];
    }
}
