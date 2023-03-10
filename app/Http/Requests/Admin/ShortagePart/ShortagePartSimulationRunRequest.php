<?php

namespace App\Http\Requests\Admin\ShortagePart;

use App\Http\Requests\BaseApiRequest;
/**
 * @OA\Schema(
 *     title="Simulation Run",
 *     type="object",
 *     required={"import_id"}
 * )
 */
class ShortagePartSimulationRunRequest extends BaseApiRequest
{
    /**
     * @OA\Property()
     * @var string
     */
    public string $import_id;

    /**
     * @OA\Property()
     * @var string
     */
    public string $mrp_run_date;

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
            'import_id' => 'required|exists:mrp_production_plan_imports,id,deleted_at,NULL',
            'mrp_run_date' => 'required|date_format:n/d/Y'
        ];
    }
}
