<?php

namespace App\Http\Requests\Admin\DataImport;

use App\Http\Requests\BaseApiRequest;


class DataImportInventoryRequest extends BaseApiRequest
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
            'type' => 'required|in:in_transit_inventory_log,bwh_inventory_log,order_point_control,vietnam_source_log,warehouse_summary_adjustment,warehouse_logical_adjustment_part,warehouse_logical_adjustment_msc',
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
