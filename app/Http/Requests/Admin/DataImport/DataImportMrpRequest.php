<?php

namespace App\Http\Requests\Admin\DataImport;

use App\Http\Requests\BaseApiRequest;


class DataImportMrpRequest extends BaseApiRequest
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
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'type' => 'required|in:production_plan,part_usage_result,shortage_part,mrp_ordering_calendar',
            'import_file' => 'required|max:51200|mimes:xlsx,xls'
        ];
    }

    /**
     * @return array
     */
    public function messages(): array
    {
        return [
            'import_file.max' => 'File is too big to import. Max file size: 50MB',
            'import_file.mimes' => 'File format is invalid. Require excel file',
        ];
    }
}
