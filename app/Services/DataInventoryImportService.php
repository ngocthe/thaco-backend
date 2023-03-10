<?php

namespace App\Services;

use App\Exports\BwhInventoryLogExport;
use App\Exports\InTransitInventoryLogExport;
use App\Exports\LogicalInventoryMscAdjustmentExport;
use App\Exports\LogicalInventoryPartAdjustmentExport;
use App\Exports\OrderPointControlExport;
use App\Exports\VietnamSourceRequestExport;
use App\Exports\WarehouseSummaryAdjustmentExport;
use App\Helpers\ImportHelper;
use App\Imports\BwhInventoryLogImport;
use App\Imports\InTransitInventoryLogImport;
use App\Imports\LogicalInventoryMscAdjustmentImport;
use App\Imports\LogicalInventoryPartAdjustmentImport;
use App\Imports\OrderPointControlImport;
use App\Imports\VietnamSourceLogLogImport;
use App\Imports\WarehouseSummaryAdjustmentImport;
use Maatwebsite\Excel\HeadingRowImport;

class DataInventoryImportService extends BaseDataImportService
{
    /**
     * @var array|string[]
     */
    public array $importClass = [
        'in_transit_inventory_log' => InTransitInventoryLogImport::class,
        'bwh_inventory_log' => BwhInventoryLogImport::class,
        'order_point_control' => OrderPointControlImport::class,
        'vietnam_source_log' => VietnamSourceLogLogImport::class,
        'warehouse_summary_adjustment' => WarehouseSummaryAdjustmentImport::class,
        'warehouse_logical_adjustment_part' => LogicalInventoryPartAdjustmentImport::class,
        'warehouse_logical_adjustment_msc' => LogicalInventoryMscAdjustmentImport::class
    ];

    /**
     * @var array|string[]
     */
    public array $exportTemplateName = [
        'in_transit_inventory_log' => 'packing_list',
        'bwh_inventory_log' => 'bonded-warehouse-scanning-data',
        'order_point_control' => 'unpack-case-ordering-point-control-data',
        'vietnam_source_log' => 'vietnam-source_log',
        'warehouse_summary_adjustment' => 'warehouse-summary-adjustment',
        'warehouse_logical_adjustment_part' => 'warehouse-logical-adjustment_part',
        'warehouse_logical_adjustment_msc' => 'warehouse-logical-adjustment_msc',
    ];

    /**
     * @var array|string[]
     */
    public array $exportTemplateTitle = [
        'in_transit_inventory_log' => InTransitInventoryLogExport::TITLE,
        'bwh_inventory_log' => BwhInventoryLogExport::TITLE,
//        'upkwh_inventory_log' => UpkwhInventoryLogExport::TITLE,
        'order_point_control' => OrderPointControlExport::TITLE,
        'vietnam_source_log' => VietnamSourceRequestExport::TITLE,
        'warehouse_summary_adjustment' => WarehouseSummaryAdjustmentExport::TITLE,
        'warehouse_logical_adjustment_part' => LogicalInventoryPartAdjustmentExport::TITLE,
        'warehouse_logical_adjustment_msc' => LogicalInventoryMscAdjustmentExport::TITLE
    ];

    /**
     * @param $type
     * @param $importFile
     * @return array|null
     */
    public function processDataImport($type, $importFile): ?array
    {
        $importClass = $this->importClass[$type];
        $headings = (new HeadingRowImport(3))->toArray($importFile);
        if (!isset($headings[0][0])) {
            return [
                'rows' => [[
                    'line' => self::HEADING_ROW,
                    'attribute' => '',
                    'errors' => 'The heading row invalid',
                    'value' => ''
                ]],
                'link_download_error' => ''
            ];
        }
        $headingsFileImport = array_filter($headings[0][0]);
        $headingsClass = $importClass::HEADING_ROW;
        if (ImportHelper::checkHeadingRow($headingsClass, $headingsFileImport)) {
            $import = new $importClass;
            $import->import($importFile);
            if (count($import->failures())) {
                return $this->handleFailures($import->failures(), $importClass::MAP_HEADING_ROW, $type, $import->dataByIndex);
            } elseif (!count($import->uniqueData))  {
                return [
                    'rows' => [
                        [
                            'line' => 4,
                            'attribute' => '',
                            'errors' => 'The import file has missing data.',
                            'value' => ''
                        ]
                    ],
                    'link_download_error' => ''
                ];
            } else {
                return null;
            }
        } else {
            return [
                'rows' => [
                    [
                        'line' => self::HEADING_ROW,
                        'attribute' => '',
                        'errors' => 'The import file has missing table column',
                        'value' => ''
                    ]
                ],
                'link_download_error' => ''
            ];
        }
    }

    /**
     * @param $failures
     * @param $headingsClass
     * @param $exportFileName
     * @param $dataByIndex
     * @return array
     */
    private function handleFailures($failures, $headingsClass, $exportFileName, $dataByIndex): array
    {
        $errors = [];
        $rowsError = [];
        $headings = array_flip($headingsClass);
        foreach ($failures as $failure) {
            $row = $failure->row();

            $attribute = $failure->attribute();
            $field = $headings[$attribute] ?? $attribute;
            $values = $failure->values();
            $value = $values[$field] ?? $values['value_error'] ?? $values[0] ?? $values;
            $error = $failure->errors()[0] ?? '';
            $errors[] = [
                'line' => $failure->row(),
                'attribute' => $attribute,
                'errors' => $error,
                'value' => $value
            ];
            if (isset($dataByIndex[$row])) {
                $value_import = $dataByIndex[$row];
                if (!isset($rowsError[$row])) {
                    $value_import['attribute'] = [$attribute];
                    $value_import['value'] = [$value];
                    $value_import['error_message'] = [$error];
                    $rowsError[$row] = $value_import;
                } else {
                    $rowsError[$row]['attribute'][] = $attribute;
                    $rowsError[$row]['value'][] = $value;
                    $rowsError[$row]['error_message'][] = $error;
                }
            }
        }
        usort($errors, function ($a, $b) {
            if ($a['line'] == $b['line']) return 0;
            return ($a['line'] > $b['line']) ? 1 : -1;
        });

        if (count($rowsError)) {
            $url = ImportHelper::exportErrorsToFile($rowsError, $headingsClass, $exportFileName);
        } else {
            $url = '';
        }

        return [
            'rows' => $errors,
            'link_download_error' => $url
        ];
    }
}
