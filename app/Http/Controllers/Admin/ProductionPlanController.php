<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ProductionPlanExport;
use App\Http\Controllers\Controller;
use App\Services\MrpProductionPlanImportService;
use App\Services\MrpWeekDefinitionService;
use App\Services\ProductionPlanService;
use App\Transformers\ProductionPlanTransformer;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use MarcinOrlowski\ResponseBuilder\Exceptions\ArrayWithMixedKeysException;
use MarcinOrlowski\ResponseBuilder\Exceptions\ConfigurationNotFoundException;
use MarcinOrlowski\ResponseBuilder\Exceptions\IncompatibleTypeException;
use MarcinOrlowski\ResponseBuilder\Exceptions\InvalidTypeException;
use MarcinOrlowski\ResponseBuilder\Exceptions\MissingConfigurationKeyException;
use MarcinOrlowski\ResponseBuilder\Exceptions\NotIntegerException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class ProductionPlanController extends Controller
{
    /**
     * @var ProductionPlanService
     */
    protected ProductionPlanService $productionPlanService;
    /**
     * @var ProductionPlanTransformer
     */
    protected ProductionPlanTransformer $transformer;

    public function __construct(Manager $fractal, ProductionPlanService $productionPlanService, ProductionPlanTransformer $productionPlanTransformer)
    {
        $this->productionPlanService = $productionPlanService;
        $this->transformer = $productionPlanTransformer;
        parent::__construct($fractal);
    }

    /**
     * @OA\Get (
     *     path="/production-plans",
     *     operationId="ProductionPlanList",
     *     tags={"ProductionPlan"},
     *     summary="List part color",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\Parameter(
     *         name="import_id",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="year",
     *         in="query",
     *         description="format: yyyy"
     *     ),
     *     @OA\Parameter(
     *         name="month",
     *         in="query",
     *         description="format: mm"
     *     ),
     *     @OA\Parameter(
     *         name="msc_code",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="vehicle_color_code",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK"
     *     ),
     *     @OA\Response(
     *          response=401,
     *          description="Not authorized"
     *      )
     * )
     * @return Response
     * @throws ArrayWithMixedKeysException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws InvalidTypeException
     * @throws MissingConfigurationKeyException
     * @throws NotIntegerException
     */
    public function index(): Response
    {
        $dates = (new MrpWeekDefinitionService())->getDates();
        $productionPlans = $this->productionPlanService->filterProductionPlant();
        return $this->responseWithTransformer($productionPlans, new ProductionPlanTransformer($dates));
    }

    /**
     * @OA\Get   (
     *     path="/production-plans/columns",
     *     operationId="ProductionPlanColumns",
     *     tags={"ProductionPlan"},
     *     summary="Get column of production-plans",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\Parameter(
     *         name="column",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="keyword",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK"
     *     ),
     *     @OA\Response(
     *          response=401,
     *          description="Not authorized"
     *      )
     * )
     * @return Response
     * @throws ArrayWithMixedKeysException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws InvalidTypeException
     * @throws MissingConfigurationKeyException
     * @throws NotIntegerException
     */
    public function columns(): Response
    {
        $codes = $this->productionPlanService->getColumnValue();
        return $this->respond($codes);
    }

    /**
     * @OA\Get (
     *     path="/production-plans/export",
     *     operationId="ProductionPlanExport",
     *     tags={"ProductionPlan"},
     *     summary="Export part color",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\Parameter(
     *         name="import_id",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="year",
     *         in="query",
     *         description="format: yyyy"
     *     ),
     *     @OA\Parameter(
     *         name="month",
     *         in="query",
     *         description="format: mm"
     *     ),
     *     @OA\Parameter(
     *         name="msc_code",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="vehicle_color_code",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="xls | pdf",
     *         @OA\Schema(type="string", default="xls")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK"
     *     ),
     *     @OA\Response(
     *          response=401,
     *          description="Not authorized"
     *      )
     * )
     * @param Request $request
     * @return BinaryFileResponse
     */
    public function export(Request $request): BinaryFileResponse
    {
        return $this->productionPlanService->export($request, ProductionPlanExport::class, 'production-plan');
    }

    /**
     * @OA\Get (
     *     path="/production-plans/import-files",
     *     operationId="ProductionPlanImportFile",
     *     tags={"ProductionPlan"},
     *     summary="List production plan import files",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\Response(
     *         response=200,
     *         description="OK"
     *     ),
     *     @OA\Response(
     *          response=401,
     *          description="Not authorized"
     *      )
     * )
     * @param MrpProductionPlanImportService $mrpProductionPlanImportService
     * @return Response
     * @throws ArrayWithMixedKeysException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws InvalidTypeException
     * @throws MissingConfigurationKeyException
     * @throws NotIntegerException
     */
    public function importFiles(MrpProductionPlanImportService $mrpProductionPlanImportService): Response
    {
        $importFiles = $mrpProductionPlanImportService->getImportFiles();
        return $this->respond($importFiles);
    }

    /**
     * @OA\Get (
     *     path="/production-plans/import-files/{id}",
     *     operationId="ProductionPlanImportFileDetail",
     *     tags={"ProductionPlan"},
     *     summary="Get production plan import file detail",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\Parameter(
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK"
     *     ),
     *     @OA\Response(
     *          response=401,
     *          description="Not authorized"
     *      )
     * )
     * @param int $id
     * @param MrpProductionPlanImportService $mrpProductionPlanImportService
     * @return Response
     * @throws ArrayWithMixedKeysException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws InvalidTypeException
     * @throws MissingConfigurationKeyException
     * @throws NotIntegerException
     */
    public function importFileDetail(int $id, MrpProductionPlanImportService $mrpProductionPlanImportService): Response
    {
        $importFile = $mrpProductionPlanImportService->getImportFile($id);
        return $this->respond($importFile);
    }

}
