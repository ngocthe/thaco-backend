<?php

namespace App\Http\Requests\Admin\DataImport;

use App\Http\Requests\BaseApiRequest;


class DataImportMasterRequest extends BaseApiRequest
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
            'type' => 'required|in:user,part_group,plant,ecn,vehicle_color,msc,part,part_color,bom,supplier,procurement,warehouse,warehouse_location,box_type',
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
