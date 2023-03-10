<?php

namespace App\Services;

use App\Exports\MRPOrderingCalendarExport;
use App\Exports\PartUsageResultExport;
use App\Helpers\ImportHelper;
use App\Imports\MrpOrderingCalendarImport;
use App\Imports\PartUsageResultImport;
use App\Imports\ProductionPlanImport;
use App\Imports\VehicleColorImport;
use App\Models\MrpProductionPlanImport;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\HeadingRowImport;
use Maatwebsite\Excel\Validators\ValidationException;

class DataMrpImportService extends BaseDataImportService
{
    /**
     * @var array|string[]
     */
    public array $importClass = [
        'production_plan' => ProductionPlanImport::class,
        'part_usage_result' => PartUsageResultImport::class,
        'mrp_ordering_calendar' => MrpOrderingCalendarImport::class
    ];

    /**
     * @var array|string[]
     */
    public array $exportTemplateName = [
        'mrp_ordering_calendar' => 'mrp-ordering-calender'
    ];

    /**
     * @var array|string[]
     */
    public array $exportTemplateTitle = [
        'mrp_ordering_calendar' => MRPOrderingCalendarExport::TITLE
    ];

    /**
     * @param $type
     * @param $importFile
     * @return array|void
     */
    public function processDataImport($type, $importFile)
    {
        $importClass = $this->importClass[$type];
        if ($type === 'production_plan') {
            return $this->processImportProductionPlant($importFile);
        } else {
            try {
                if ($type === 'part_usage_result') {
                    Excel::import(new PartUsageResultImport, $importFile);
                } else {
                    $headings = (new HeadingRowImport(self::HEADING_ROW))->toArray($importFile);
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
                        Excel::import(new $importClass, $importFile);
                    } else {
                        return [
                            'rows' => [[
                                'line' => self::HEADING_ROW,
                                'attribute' => '',
                                'errors' => 'The import file has missing table column',
                                'value' => ''
                            ]],
                            'link_download_error' => ''
                        ];
                    }
                }
            } catch (ValidationException $e) {
                return $this->handleValidationError($e->failures(), $importClass::MAP_HEADING_ROW);
            } catch (\Illuminate\Validation\ValidationException $failures) {
                return $this->handleValidationError($failures->errors()['failures'], $importClass::MAP_HEADING_ROW);
            }
        }
    }

    /**
     * @param UploadedFile $importFile
     * @return array|null
     */
    private function processImportProductionPlant(UploadedFile $importFile): ?array
    {
        $originalFileName = $importFile->getClientOriginalName();
        $exists = MrpProductionPlanImport::query()
            ->where('original_file_name', $originalFileName)
            ->first();
        if ($exists) {
            return [
                'rows' => [[
                    'line' => '',
                    'attribute' => '',
                    'errors' => 'The file import already exists',
                    'value' => ''
                ]],
                'link_download_error' => ''
            ];
        } else {
            DB::beginTransaction();
            try {
                $dateFile = Carbon::now()->format('dmYHi');
                $filePath = 'mrp/production_plans/' . $dateFile . $originalFileName;
                Storage::disk('s3')->put($filePath, $importFile);
                $import = MrpProductionPlanImport::query()
                    ->create([
                        'file_path' => $filePath,
                        'original_file_name' => $originalFileName
                    ]);
                Excel::import(new ProductionPlanImport($import->id), $importFile);
                DB::commit();
                return null;
            }  catch (ValidationException $e) {
                DB::rollBack();
                Log::error($e);
                return $this->handleValidationError($e->failures(), ProductionPlanImport::MAP_HEADING_ROW);
            } catch (\Illuminate\Validation\ValidationException $failures) {
                DB::rollBack();
                Log::error($failures);
                return $this->handleValidationError($failures->errors()['failures'], ProductionPlanImport::MAP_HEADING_ROW);
            }
        }
    }

}
