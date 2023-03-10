<?php

namespace App\Helpers;

use App\Exports\InventoryLogErrorsExport;
use App\Models\BoxType;
use App\Models\Ecn;
use App\Models\Msc;
use App\Models\Part;
use App\Models\PartColor;
use App\Models\PartGroup;
use App\Models\Plant;
use App\Models\Supplier;
use App\Models\VehicleColor;
use App\Models\Warehouse;
use App\Models\WarehouseLocation;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Validators\Failure;

class ImportHelper
{
    protected const FOLDER_DATA_IMPORT_ERRORS = 'data_import_errors';
    protected const MAX_ERRORS = 50;

    /**
     * @param array $headingsClass
     * @param array $headingsFileImport
     * @return bool
     */
    public static function checkHeadingRow(array $headingsClass, array $headingsFileImport): bool
    {
        return count(array_diff($headingsClass, $headingsFileImport)) === 0;
    }

    /**
     * @param $array
     * @return array
     */
    public static function findDuplicateInMultidimensional($array): array
    {
        $unique = array_unique($array, SORT_REGULAR);
        return array_diff_key($array, $unique);
    }

    /**
     * @param $duplicate
     * @param $attributeName
     * @param bool $throwIfErrors
     * @param array $failures
     * @return void
     * @throws ValidationException
     */
    public static function handleDuplicateError($duplicate, $attributeName, array &$failures = [],
                                                bool $throwIfErrors = true)
    {
        foreach ($duplicate as $row => $data) {
            self::processErrors($row, $attributeName, 'Duplicate data', [implode(',', $data)], $failures, $throwIfErrors);
        }
    }

    /**
     * @param array $uniqueData
     * @param string $model
     * @param array $uniqueKeys
     * @param string $errorMessage
     * @param string $uniqueAttributes
     * @param array $failures
     * @param bool $throwIfErrors
     * @return void
     * @throws ValidationException
     */
    public static function checkUniqueData(array  $uniqueData, string $model, array $uniqueKeys, string $errorMessage,
                                           string $uniqueAttributes, array &$failures = [], bool $throwIfErrors = true)
    {
        $uniqueDataValues = array_values($uniqueData);
        if (!count($uniqueDataValues)) {
            $failures[] = new Failure(
                4,
                '',
                ['The import file has missing data.'],
                ['']
            );
        } else {
            $rows = $model::whereInMultiple($uniqueKeys, $uniqueDataValues)
                ->select($uniqueKeys)
                ->get()
                ->toArray();
            if ($rows) {
                $values = array_map(function ($val) {
                    return implode(',', $val);
                }, $rows);

                foreach ($uniqueData as $row => $data) {
                    $v = implode(',', $data);
                    if (in_array($v, $values)) {
                        self::processErrors($row, $uniqueAttributes, $errorMessage, [$v], $failures, $throwIfErrors);
                    }
                }
            }
        }
    }

    /**
     * @param array $uniqueKeys
     * @param array $uniqueData
     * @param string $model
     * @param string $errorMessage
     * @param string $uniqueAttributes
     * @param array $with
     * @param array $rowsIgnore
     * @param array $failures
     * @return array
     * @throws ValidationException
     */
    public static function checkAndGetDataInRefTable(array  $uniqueKeys, array $uniqueData, string $model,
                                                     string $errorMessage, string $uniqueAttributes, array $with = [],
                                                     array  &$rowsIgnore = [], array &$failures = []): array
    {
        $query = $model::whereInMultiple($uniqueKeys, $uniqueData);
        if (count($with)) $query->with($with);
        $rows = $query->get()->toArray();
        $dataGroupByKey = [];

        foreach ($rows as $row) {
            $values = array_map(function ($k) use ($row) {
                return $row[$k];
            }, $uniqueKeys);
            $key = implode('-', $values);
            $dataGroupByKey[$key][] = $row;
        }

        foreach ($uniqueData as $index => $data) {
            if (!in_array($index, $rowsIgnore)) {
                $key = implode('-', $data);

                if (!isset($dataGroupByKey[$key])) {
                    self::processErrors($index, $uniqueAttributes, $errorMessage, [$key], $failures);
                    $rowsIgnore[] = $index;
                }
            }
        }

        return $dataGroupByKey;
    }

    /**
     * @param array $plantCodes
     * @param bool $throwIfErrors
     * @param array $failures
     * @return void
     * @throws ValidationException
     */
    public static function referenceCheckPlantCode(array $plantCodes, array &$failures = [], bool $throwIfErrors = true)
    {
        self::referenceCheckCode(Plant::class, $plantCodes, 'plant_code', 'Plant Code',
            'The Plant Code does not exist.', $failures, $throwIfErrors);
    }

    /**
     * @param array $partGroups
     * @param bool $throwIfErrors
     * @param array $failures
     * @return void
     * @throws ValidationException
     */
    public static function referenceCheckPartGroup(array $partGroups, array &$failures = [], bool $throwIfErrors = true)
    {
        self::referenceCheckCode(PartGroup::class, $partGroups, 'part_group', 'Part Group',
            'The Part Group Code does not exist.', $failures, $throwIfErrors);
    }

    /**
     * @param array $supplierCodes
     * @param bool $throwIfErrors
     * @param array $failures
     * @return void
     * @throws ValidationException
     */
    public static function referenceCheckSupplier(array $supplierCodes, array &$failures = [], bool $throwIfErrors = true)
    {
        self::referenceCheckCode(Supplier::class, $supplierCodes, 'supplier_code', 'Procurement Supplier Code',
            'The Procurement Supplier Code does not exist.', $failures, $throwIfErrors);
    }

    /**
     * @param array $mscCodes
     * @param bool $throwIfErrors
     * @param array $failures
     * @return void
     * @throws ValidationException
     */
    public static function referenceCheckMsc(array $mscCodes, array &$failures = [], bool $throwIfErrors = true)
    {
        self::referenceCheckDataPair($mscCodes, ['code', 'plant_code'], Msc::class,
            'MSC, Plant Code',
            'MSC, Plant Code are not linked together.',
            $failures,
            $throwIfErrors);
    }

    /**
     * @param array $partCodes
     * @param bool $throwIfErrors
     * @param array $failures
     * @return void
     * @throws ValidationException
     */
    public static function referenceCheckPart(array $partCodes, array &$failures = [], bool $throwIfErrors = true)
    {
        self::referenceCheckDataPair($partCodes, ['code', 'plant_code'], Part::class,
            'Part No., Plant Code',
            'Part No., Plant Code are not linked together.',
            $failures,
            $throwIfErrors);
    }

    /**
     * @param array $warehouseCodes
     * @param null $warehouseType
     * @param array $failures
     * @param bool $throwIfErrors
     * @return void
     * @throws ValidationException
     */
    public static function referenceCheckWarehouse(array $warehouseCodes, array &$failures = [], $warehouseType = null, bool $throwIfErrors = true)
    {
        $uniqueKeys = ['code', 'plant_code'];
        $additionConditions = [];
        if ($warehouseType) {
            $additionConditions['warehouse_type'] = $warehouseType;
        }
        self::referenceCheckDataPair($warehouseCodes, $uniqueKeys, Warehouse::class,
            'Warehouse Code, Plant Code',
            'Warehouse Code, Plant Code are not linked together.',
            $failures, $throwIfErrors, $additionConditions);
    }

    /**
     * @param array $ecnNoIn
     * @param array $ecnNoOut
     * @param bool $throwIfErrors
     * @param array $failures
     * @return void
     * @throws ValidationException
     */
    public static function referenceCheckEcnCode(array $ecnNoIn, array $ecnNoOut, array &$failures = [], bool $throwIfErrors = true)
    {
        self::referenceCheckDataPair($ecnNoIn, ['code', 'plant_code'], Ecn::class,
            'ECN No. In, Plant Code',
            'ECN No. In, Plant Code are not linked together.',
            $failures,
            $throwIfErrors);
        self::referenceCheckDataPair($ecnNoOut, ['code', 'plant_code'], Ecn::class,
            'ECN No. Out, Plant Code',
            'ECN No. Out, Plant Code are not linked together.',
            $failures,
            $throwIfErrors);
    }

    /**
     * @param array $partColors
     * @param bool $throwIfErrors
     * @param array $failures
     * @return void
     * @throws ValidationException
     */
    public static function referenceCheckPartAndPartColor(array $partColors, array &$failures = [], bool $throwIfErrors = true)
    {
        self::referenceCheckDataPair($partColors, ['part_code', 'code', 'plant_code'], PartColor::class,
            'Part No., Part Color Code, Plant Code',
            'Part No, Part Color Code, Plant Code are not linked together.',
            $failures,
            $throwIfErrors);
    }

    /**
     * @param array $boxTypes
     * @param bool $throwIfErrors
     * @param array $failures
     * @return void
     * @throws ValidationException
     */
    public static function referenceCheckPartAndBoxType(array $boxTypes, array &$failures = [], bool $throwIfErrors = true)
    {
        self::referenceCheckDataPair($boxTypes, ['part_code', 'code', 'plant_code'], BoxType::class,
            'Part No, Box Type Code, Plant Code',
            'Part No, Box Type Code, Plant Code are not linked together.',
            $failures,
            $throwIfErrors);
    }

    /**
     * @param array $locationCodes
     * @param array $failures
     * @return void
     * @throws ValidationException
     */
    public static function referenceCheckLocationCode(array $locationCodes, array &$failures = [])
    {
        self::referenceCheckDataPair($locationCodes, ['code', 'warehouse_code', 'plant_code'], WarehouseLocation::class,
            'Location Code, Warehouse Code, Plant Code',
            'Warehouse Code, Location Code, Plant Code are not linked together.',
            $failures,
            false,
        );
    }

    /**
     * @param array $uniqueDataValues
     * @param array $uniqueKeys
     * @param string $model
     * @param string $attributes
     * @param string $errorMessage
     * @param array $failures
     * @param bool $throwIfErrors
     * @param array $additionConditions
     * @return void
     * @throws ValidationException
     */
    public static function referenceCheckDataPair(array  $uniqueDataValues, array $uniqueKeys, string $model,
                                                  string $attributes, string $errorMessage, array  &$failures = [],
                                                  bool $throwIfErrors = true, array $additionConditions = [])
    {
        if (count($uniqueDataValues)) {
            $query= $model::whereInMultiple($uniqueKeys, $uniqueDataValues)->select($uniqueKeys);
            if (count($additionConditions)) {
                $query->where($additionConditions);
            }
            $rows = $query
                ->get()
                ->toArray();
            $dataInDB = [];
            if ($rows) {
                $dataInDB = array_map(function ($val) {
                    return implode(', ', $val);
                }, $rows);
                $dataInDB = array_unique($dataInDB);
            }

            foreach ($uniqueDataValues as $row => $data) {
                $v = implode(', ', $data);
                if (!in_array($v, $dataInDB)) {
                    self::processErrors($row, $attributes, $errorMessage, [$attributes => $v], $failures, $throwIfErrors);
                }
            }
        }
    }

    /**
     * @param $model
     * @param $codes
     * @param $attribute
     * @param $attributeName
     * @param $errorMessage
     * @param array $failures
     * @param bool $throwIfErrors
     * @param array $preConditions
     * @return void
     * @throws ValidationException
     */
    public static function referenceCheckCode($model, $codes, $attribute, $attributeName, $errorMessage, array &$failures = [],
                                              bool $throwIfErrors = true, array $preConditions = [])
    {
        self::removeRowsInvalid($failures, $attribute, $codes);
        if (!count($codes)) return;
        $codeValues = array_unique($codes);
        $query = $model::query()->select('code');
        if (count($preConditions)) {
            $query->where($preConditions);
        }
        $codesInDb = $query
            ->whereIn('code', $codeValues)
            ->distinct()
            ->pluck('code')
            ->toArray();
        $diff = array_diff($codeValues, $codesInDb);
        if (count($diff)) {
            foreach ($codes as $row => $code) {
                if (in_array($code, $diff)) {
                    self::processErrors($row, $attributeName, $errorMessage, [$attribute => $code], $failures, $throwIfErrors);
                } else {
                    // todo
                }
            }
        }
    }

    /**
     * @param $codes
     * @param $type
     * @param $attributeName
     * @param $attributeCode
     * @param array $failures
     * @param bool $throwIfErrors
     * @return void
     * @throws ValidationException
     */
    public static function referenceCheckVehicleColorCode($codes, $type, $attributeName, $attributeCode, array &$failures = [], bool $throwIfErrors = true)
    {
        self::removeRowsInvalid($failures, $attributeCode, $codes);
        if (!count($codes)) return;

        $rows = VehicleColor::whereInMultiple(['code', 'plant_code'], $codes)
            ->select(['code', 'type', 'plant_code'])
            ->get()
            ->toArray();
        $dataInDB = [];
        if ($rows) {
            foreach ($rows as $row) {
                $dataInDB[$row['code'] . ', ' . $row['plant_code']] = $row['type'];
            }
        }

        foreach ($codes as $row => $data) {
            $v = implode(', ', $data);
            if (!isset($dataInDB[$v])) {
                self::processErrors($row, $attributeName, 'The ' . $attributeName . ', Plant Code are not linked together.', [$attributeCode => $v], $failures, $throwIfErrors);
            } elseif ($dataInDB[$v] != $type) {
                self::processErrors($row, $attributeName, 'The type of ' . $attributeName . ' must is ' . $type, [$attributeCode => $v], $failures, $throwIfErrors);
            } else {
                // todo
            }
        }
    }

    /**
     * @param $failuresArray
     * @param $failuresObject
     * @return array
     */
    public static function getRowsIgnore($failuresArray, $failuresObject): array
    {
        $rowsIgnore = array_map(function ($f) {
            return $f['line'];
        }, $failuresArray);

        $rowsIgnore = array_merge($rowsIgnore, array_map(function ($f) {
            return $f->row();
        }, $failuresObject));

        return array_unique($rowsIgnore);
    }

    /**
     * @param $failuresObject
     * @param $attribute
     * @param $dataReferenceCheck
     * @return void
     */
    public static function removeRowsInvalid($failuresObject, $attribute, &$dataReferenceCheck)
    {
        $rowsIgnore = [];
        foreach ($failuresObject as $f) {
            if ($attribute == $f->attribute() || in_array('Duplicate data', $f->errors())) {
                $rowsIgnore[] = $f->row();
            }
        }
        foreach ($rowsIgnore as $row) {
            unset($dataReferenceCheck[$row]);
        }
    }

    /**
     * @param $rowsError
     * @param $headingsClass
     * @param $exportFileName
     * @return string
     */
    public static function exportErrorsToFile($rowsError, $headingsClass, $exportFileName): string
    {
        usort($rowsError, function ($a, $b) {
            if ($a['row'] == $b['row']) return 0;
            return ($a['row'] > $b['row']) ? 1 : -1;
        });

        $export = new InventoryLogErrorsExport($rowsError, $headingsClass, $exportFileName);
        $dateFile = Carbon::now()->format('dmYHi');
        $fileName = self::FOLDER_DATA_IMPORT_ERRORS . '/' . $exportFileName . '_' . $dateFile . '.xlsx';
        $export->store($fileName, 's3');
        return Storage::disk('s3')->temporaryUrl($fileName, now()->addMinutes(30));
    }

    /**
     * @param $row
     * @param $attribute
     * @param $message
     * @param $value
     * @param array $failures
     * @param bool $throwIfErrors
     * @return void
     * @throws ValidationException
     */
    public static function processErrors($row, $attribute, $message, $value, array &$failures = [],
                                         bool $throwIfErrors = true)
    {
        $failures[] = new Failure(
            $row,
            $attribute,
            [$message],
            $value
        );
        if ($throwIfErrors && count($failures) > self::MAX_ERRORS) {
            throw ValidationException::withMessages(['failures' => $failures]);
        }
    }

    /**
     * @param $fromDate
     * @param $toDate
     * @param string $format
     * @return bool
     */
    public static function __isAfterDate($fromDate, $toDate, string $format = 'd/m/Y'): bool
    {
        if ($fromDate && $toDate) {
            try {

                $fromDateObj = Carbon::createFromFormat($format, $fromDate);
                $toDateObj = Carbon::createFromFormat($format, $toDate);

                return ($fromDateObj && $toDateObj && $toDateObj->isAfter($fromDateObj));

            } catch (\Exception $exception) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param $fromDate
     * @param $toDate
     * @param string $format
     * @return bool
     */
    public static function __isGreaterThanOrEqualDate($fromDate, $toDate, string $format = 'd/m/Y'): bool
    {
        if ($fromDate && $toDate) {
            try {

                $fromDateObj = Carbon::createFromFormat($format, $fromDate);
                $toDateObj = Carbon::createFromFormat($format, $toDate);

                return ($fromDateObj && $toDateObj && $toDateObj->gte($fromDateObj));

            } catch (\Exception $exception) {
                return true;
            }
        }
        return true;
    }

    /**
     * @param array $failures
     * @param $row
     * @param $dates
     * @param $fieldValidate
     * @param $fieldName
     * @param $message
     * @param bool $throwIfErrors
     * @return void
     * @throws ValidationException
     */
    public static function __handleAfterDateError(array &$failures, $row, $dates, $fieldValidate, $fieldName, $message, bool $throwIfErrors = true)
    {
        $dataFieldConverted = [];
        $dataFieldConverted[$fieldValidate] = $dates[$fieldValidate];
        self::processErrors($row,
            $fieldName,
            $message,
            $dataFieldConverted,
            $failures,
            $throwIfErrors
        );
    }
}
