<?php

namespace App\Http\Requests\Admin\Setting;

use App\Http\Requests\BaseApiRequest;

/**
 * @OA\Schema(
 *     title="Update Setting request",
 *     type="object",
 *     required={"key", "value"}
 * )
 */
class SettingRequest extends BaseApiRequest
{
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
     * @OA\Property()
     * @var string
     */
    public string $key;

    /**
     * @OA\Property()
     * @var string
     */
    public string $value;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $key = $this->get('key');
        if ($key === 'lock_table_master') {
            return [
                'key' => 'required|in:lock_table_master',
                'value' => 'required|array',
                'value.start_time' => 'required|date_format:H:i',
                'value.end_time' => 'required|date_format:H:i',
            ];
        } else {
            return [
                'key' => 'required|in:max_product',
                'value' => 'required|integer|min:1|max:9999',
            ];
        }
    }
}
