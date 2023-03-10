<?php

namespace App\Http\Controllers\Admin;

use App\Exports\PartUsageResultTemplateExport;
use App\Exports\ProductionPlanExport;
use App\Exports\ProductionPlanTemplateExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DataImport\DataImportInventoryRequest;
use App\Http\Requests\Admin\DataImport\DataImportMasterRequest;
use App\Http\Requests\Admin\DataImport\DataImportMrpRequest;
use App\Http\Requests\Admin\DataImport\TemplateRequest;
use App\Services\DataInventoryImportService;
use App\Services\DataMasterImportService;
use App\Services\DataMrpImportService;
use App\Services\ProductionPlanService;
use League\Fractal\Manager;
use MarcinOrlowski\ResponseBuilder\Exceptions\ArrayWithMixedKeysException;
use MarcinOrlowski\ResponseBuilder\Exceptions\ConfigurationNotFoundException;
use MarcinOrlowski\ResponseBuilder\Exceptions\IncompatibleTypeException;
use MarcinOrlowski\ResponseBuilder\Exceptions\InvalidTypeException;
use MarcinOrlowski\ResponseBuilder\Exceptions\MissingConfigurationKeyException;
use MarcinOrlowski\ResponseBuilder\Exceptions\NotIntegerException;
use Symfony\Component\HttpFoundation\Response;

class DataImportController extends Controller
{
    /**
     * @var DataMasterImportService
     */
    protected DataMasterImportService $dataImportService;

    public function __construct(Manager $fractal, DataMasterImportService $dataImportService)
    {
        $this->dataImportService = $dataImportService;
        parent::__construct($fractal);
    }

    /**
     * @OA\Get   (
     *     path="/data-imports/templates",
     *     operationId="DataImportTemplate",
     *     tags={"DataImport"},
     *     summary="Data import template",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="part_group,plant,ecn,vehicle_color,msc,part,part_color,bom,supplier,procurement,warehouse,warehouse_location,box_type,in_transit_inventory_log,bwh_inventory_log,order_point_control,warehouse_summary_adjustment,warehouse_logical_adjustment_part,warehouse_logical_adjustment_msc,production_plan,part_usage_result,shortage_part,mrp_ordering_calendar"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK"
     *     ),
     *     @OA\Response(
     *          response=400,
     *          description="Bad request"
     *      ),
     *     @OA\Response(
     *          response=401,
     *          description="Not authorized"
     *      )
     * )
     * @param TemplateRequest $request
     * @return Response
     */
    public function index(TemplateRequest $request): Response
    {
        $inventoryImportTemplates = [
            'in_transit_inventory_log',
            'bwh_inventory_log',
            'order_point_control',
            'vietnam_source_log',
            'warehouse_summary_adjustment',
            'warehouse_logical_adjustment_part',
            'warehouse_logical_adjustment_msc'
        ];
        $mrpImportTemplates = [
            'mrp_ordering_calendar'
        ];
        $type = $request->get('type');
        if (in_array($type, $inventoryImportTemplates)) {
            return (new DataInventoryImportService())->exportTemplate($type);
        } elseif ($type == 'production_plan' || $type == 'shortage_part') {
            return (new ProductionPlanService())->export($request, ProductionPlanTemplateExport::class, 'production-plan');
        } elseif ($type == 'part_usage_result') {
            return (new ProductionPlanService())->export($request, PartUsageResultTemplateExport::class, 'production_result');
        } elseif (in_array($type, $mrpImportTemplates)) {
            return (new DataMrpImportService())->exportTemplate($type);
        } else {
            return $this->dataImportService->exportTemplate($type);
        }
    }

    /**
     * @OA\Post  (
     *     path="/data-imports/master",
     *     operationId="DataImportMaster",
     *     tags={"DataImport"},
     *     summary="Data import",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="type",
     *                      type="string",
     *                      description="part_group,plant,ecn,vehicle_color,msc,part,part_color,bom,supplier,procurement,warehouse,warehouse_location,box_type"
     *                  ),
     *                  @OA\Property(
     *                      property="import_file",
     *                      type="file"
     *                  ),
     *              ),
     *          )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK"
     *     ),
     *     @OA\Response(
     *          response=400,
     *          description="Bad request"
     *      ),
     *     @OA\Response(
     *          response=401,
     *          description="Not authorized"
     *      )
     * )
     * @param DataImportMasterRequest $request
     * @return Response
     * @throws ArrayWithMixedKeysException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws InvalidTypeException
     * @throws MissingConfigurationKeyException
     * @throws NotIntegerException
     */
    public function master(DataImportMasterRequest $request): Response
    {
        $errors = $this->dataImportService->processDataImport($request->get('type'), $request->file('import_file'));
        if ($errors) {
            return $this->respondWithError(205, 400, '', $errors);
        } else {
            return $this->respond();
        }
    }

    /**
     * @OA\Post  (
     *     path="/data-imports/inventory",
     *     operationId="DataImportInventory",
     *     tags={"DataImport"},
     *     summary="Data import",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="type",
     *                      type="string",
     *                      description="in_transit_inventory_log,bwh_inventory_log,order_point_control"
     *                  ),
     *                  @OA\Property(
     *                      property="import_file",
     *                      type="file"
     *                  ),
     *              ),
     *          )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK"
     *     ),
     *     @OA\Response(
     *          response=400,
     *          description="Bad request"
     *      ),
     *     @OA\Response(
     *          response=401,
     *          description="Not authorized"
     *      )
     * )
     * @param DataImportInventoryRequest $request
     * @param DataInventoryImportService $inventoryImportService
     * @return Response
     * @throws ArrayWithMixedKeysException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws InvalidTypeException
     * @throws MissingConfigurationKeyException
     * @throws NotIntegerException
     */
    public function inventory(DataImportInventoryRequest $request, DataInventoryImportService $inventoryImportService): Response
    {
        $errors = $inventoryImportService->processDataImport($request->get('type'), $request->file('import_file'));
        if ($errors) {
            return $this->respondWithError(205, 400, '', $errors);
        } else {
            return $this->respond();
        }
    }

    /**
     * @OA\Post  (
     *     path="/data-imports/mrp",
     *     operationId="DataImportMrp",
     *     tags={"DataImport"},
     *     summary="Data import",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="type",
     *                      type="string",
     *                      description="production_plan,part_usage_result,shortage_part,mrp_ordering_calendar"
     *                  ),
     *                  @OA\Property(
     *                      property="import_file",
     *                      type="file"
     *                  ),
     *              ),
     *          )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK"
     *     ),
     *     @OA\Response(
     *          response=400,
     *          description="Bad request"
     *      ),
     *     @OA\Response(
     *          response=401,
     *          description="Not authorized"
     *      )
     * )
     * @param DataImportMrpRequest $request
     * @param DataMrpImportService $mrpImportService
     * @return Response
     * @throws ArrayWithMixedKeysException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws InvalidTypeException
     * @throws MissingConfigurationKeyException
     * @throws NotIntegerException
     */
    public function mrp(DataImportMrpRequest $request, DataMrpImportService $mrpImportService): Response
    {
        $errors = $mrpImportService->processDataImport($request->get('type'), $request->file('import_file'));
        if ($errors) {
            return $this->respondWithError(205, 400, '', $errors);
        } else {
            return $this->respond();
        }
    }
}
