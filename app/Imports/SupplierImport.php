<?php

namespace App\Imports;

use App\Helpers\ImportHelper;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;

class SupplierImport extends BaseImport implements SkipsOnFailure
{
    use SkipsFailures;

    public const HEADING_ROW = [
        'procurement_supplier_code',
        'procurement_supplier_code_description',
        'address_of_procurement_supplier',
        'phone_no_of_procurement_supplier',
        'number_of_forecast_by_week',
        'number_of_forecast_by_month',
        'receiver',
        'bcc',
        'cc'
    ];

    public const MAP_HEADING_ROW = [
        'code' => 'Procurement Supplier Code',
        'description' => 'Procurement Supplier Code Description',
        'address' => 'Address of Procurement Supplier',
        'phone' => 'Phone No. of Procurement Supplier',
        'forecast_by_week' => 'Number of forecast by week',
        'forecast_by_month' => 'Number of forecast by month',
        'receiver' => 'Receiver',
        'bcc' => 'BCC',
        'cc' => 'CC'
    ];

    /**
     * @var array|string[]
     */
    protected array $uniqueKeys = ['code'];

    /**
     * @var string
     */
    protected string $uniqueAttributes = 'Procurement Supplier Code';

    /**
     * @var string
     */
    protected string $uniqueErrorMessage = 'The Procurement Supplier Code have already been taken.';

    /**
     * @var array
     */
    public array $uniqueData = [];

    /**
     * @param $data
     * @param $index
     * @return array
     */
    public function prepareForValidation($data, $index): array
    {
        $data = array_map('trim', $data);
        $supplier_data = [
            'code' => strtoupper($data['procurement_supplier_code']),
            'description' => $data['procurement_supplier_code_description'],
            'address' => $data['address_of_procurement_supplier'],
            'phone' => $data['phone_no_of_procurement_supplier'],
            'forecast_by_week' => $data['number_of_forecast_by_week'],
            'forecast_by_month' => $data['number_of_forecast_by_month'],
            'receiver' => $data['receiver'] ? array_map('trim', explode(',', $data['receiver'])) : [],
            'bcc' => $data['bcc'] ? array_map('trim', explode(',', $data['bcc'])) : [],
            'cc' => $data['cc'] ? array_map('trim', explode(',', $data['cc'])) : [],
        ];

        $this->uniqueData[$index] = [$supplier_data['code']];

        return $supplier_data;
    }

    /**
     * @return string[]
     */
    public function rules(): array
    {
        return [
            'code' => 'required|alpha_num_dash|max:5',
            'description' => 'required|string|max:20',
            'address' => 'required|string|max:50',
            'phone' => 'required|string|min:9|max:15|regex:/^([0-9\s\-\+\(\)]*)$/',
            'forecast_by_week' => 'required|integer|min:1|max:5',
            'forecast_by_month' => 'required|integer|min:1|max:12',
            'receiver' => 'required|array|max:99',
            'receiver.*' => 'email',
            'bcc' => 'required|array|max:99',
            'bcc.*' => 'email',
            'cc' => 'required|array|max:99',
            'cc.*' => 'email',
        ];
    }

    /**
     * @return array
     */
    public function customValidationAttributes(): array
    {
        return self::MAP_HEADING_ROW;
    }

    /**
     * @param Collection $collection
     * @return void
     * @throws ValidationException
     */
    public function collection(Collection $collection)
    {
        $duplicate = ImportHelper::findDuplicateInMultidimensional($this->uniqueData);
        if (count($duplicate)) {
            ImportHelper::handleDuplicateError($duplicate, $this->uniqueAttributes, $this->failures);
        }
        ImportHelper::checkUniqueData($this->uniqueData, Supplier::class,
            $this->uniqueKeys,
            $this->uniqueErrorMessage,
            $this->uniqueAttributes,
            $this->failures
        );

        if (count($this->failures)) {
            throw ValidationException::withMessages(['failures' => $this->failures]);
        }

        $rows = [];
        $collection = $collection->toArray();
        $loggedId = auth()->id();
        $now = Carbon::now();
        foreach ($collection as $item) {
            $rows[] = array_merge($item, [
                'receiver' => json_encode($item['receiver']),
                'bcc' => json_encode($item['bcc']),
                'cc' => json_encode($item['cc']),
                'created_by' => $loggedId,
                'updated_by' => $loggedId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
        Supplier::query()->insert($rows);
    }
}
