<?php

namespace App\Imports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class BaseImport implements ToCollection, WithHeadingRow, WithValidation, WithStartRow, WithMultipleSheets
{
    /**
     * @return int
     */
    public function headingRow(): int
    {
        return 3;
    }

    /**
     * @return int
     */
    public function startRow(): int
    {
        return 4;
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        return [
            0 => $this,
        ];
    }

    public function collection(Collection $collection)
    {
        // TODO: Implement collection() method.
    }

    public function rules(): array
    {
        // TODO: Implement rules() method.
        return  [];
    }

    /**
     * @param $value
     * @param string $format
     * @return string
     */
    public function excelToDate($value, string $format = 'd/m/Y'): string
    {
        if (is_numeric($value)) {
            return Date::excelToDateTimeObject($value)->format($format);
        }
        return $value;
    }

    /**
     * @param Collection $collection
     * @param array $fieldDateConvertFrom = [fieldName => fromDateFormat]
     * @return array
     */
    public function createRowsInsert(Collection $collection, array $fieldDateConvertFrom = []): array
    {
        $rows = [];
        $collection = $collection->toArray();
        $loggedId = auth()->id();
        $now = Carbon::now()->toDateTimeString();

        foreach ($collection as $item) {

            foreach ($fieldDateConvertFrom as $fieldName => $fromDateFormat) {
                if(!empty($item[$fieldName])) {
                    $item[$fieldName] = Carbon::createFromFormat($fromDateFormat, $item[$fieldName])->format('Y-m-d');
                } else {
                    $item[$fieldName] = null;
                }
            }

            $rows[] = array_merge($item, [
                'created_by' => $loggedId,
                'updated_by' => $loggedId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        return $rows;
    }
}
