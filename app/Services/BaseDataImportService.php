<?php

namespace App\Services;

use App\Exports\TemplateExport;
use App\Helpers\ImportHelper;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\HeadingRowImport;
use Maatwebsite\Excel\Validators\Failure;
use Maatwebsite\Excel\Validators\ValidationException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BaseDataImportService
{
    const HEADING_ROW = 3;

    /**
     * @var array|string[]
     */
    public array $importClass = [];

    /**
     * @var array|string[]
     */
    public array $exportTemplateName = [];

    /**
     * @var array|string[]
     */
    public array $exportTemplateTitle = [];


    /**
     * @param $type
     * @return Response|BinaryFileResponse
     */
    public function exportTemplate($type)
    {
        $importClass = $this->importClass[$type];
        $headingsClass = $importClass::MAP_HEADING_ROW;
        $dateFile = Carbon::now()->format('dmY');
        $fileName = $this->exportTemplateName[$type];
        $exportTitle = $this->exportTemplateTitle[$type];
        return (new TemplateExport($headingsClass, $exportTitle))->download($fileName . '_' . $dateFile . '.xlsx');
    }

    /**
     * @param $type
     * @param $importFile
     * @return array|void
     */
    public function processDataImport($type, $importFile)
    {
        $importClass = $this->importClass[$type];
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
            try {
                Excel::import(new $importClass, $importFile);
            } catch (ValidationException $e) {
                return $this->handleValidationError($e->failures(), $importClass::MAP_HEADING_ROW);
            } catch (\Illuminate\Validation\ValidationException $failures) {
                return $this->handleValidationError($failures->errors()['failures'], $importClass::MAP_HEADING_ROW);
            }
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

    /**
     * @param $failures
     * @param $headingsClass
     * @return array
     */
    protected function handleValidationError($failures, $headingsClass): array
    {
        $headingsClassFlip = array_flip($headingsClass);
        $errors = [];
        foreach ($failures as $failure) {
            if ($failure instanceof Failure) {
                $error = $failure->errors()[0] ?? '';
                $attribute = $failure->attribute();
                $index = null;
                if (str_contains($attribute, 'receiver') || str_contains($attribute, 'bcc') || str_contains($attribute, 'cc')) {
                    $attributeArr = explode('.', $attribute);
                    $attribute = $headingsClass[$attributeArr[0]] ?? $attributeArr[0];
                    $index = $attributeArr[1];
                    $error = str_replace($attributeArr[0] .'.' . $index, $attributeArr[0], $error);
                }
                $field = $headingsClassFlip[$attribute] ?? $attribute;
                $value = $failure->values()[$field] ?? $failure->values()[array_key_first($failure->values())];
                if ($index != null) {
                    $value = [$value[$index]];
                }
                $errors[] = [
                    'line' => $failure->row(),
                    'attribute' => $attribute,
                    'errors' => $error,
                    'value' => $value
                ];
            } else {
                $errors[] = $failure;
            }
        }
        usort($errors, function ($a, $b) {
            if ($a['line'] == $b['line']) return 0;
            return ($a['line'] > $b['line']) ? 1 : -1;
        });
        return [
            'rows' => array_slice($errors,0,50),
            'link_download_error' => ''
        ];
    }
}
