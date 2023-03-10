<?php

namespace App\Http\Controllers\Admin;

use App\Exports\MrpResultByMscExport;
use App\Exports\MrpResultByPartExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MrpResult\MrpResultSystemRunRequest;
use App\Services\MrpProductionPlanImportService;
use App\Services\MrpResultService;
use App\Services\MrpWeekDefinitionService;
use App\Transformers\MrpResultByMscTransformer;
use App\Transformers\MrpResultByPartTransformer;
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

class MrpResultController extends Controller
{
    /**
     * @var MrpResultService
     */
    protected MrpResultService $mrpResultService;

    public function __construct(Manager $fractal, MrpResultService $mrpResultService)
    {
        $this->mrpResultService = $mrpResultService;
        parent::__construct($fractal);
    }

    /**
     * @OA\Get (
     *     path="/mrp-results/part",
     *     operationId="MrpResultPart",
     *     tags={"MrpResult"},
     *     summary="List mrp result by part",
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
     *         name="part_code",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="part_color_code",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="plant_code",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="part_group",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="group_by",
     *         in="query",
     *         description="day | month"
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
     * @param MrpWeekDefinitionService $mrpWeekDefinitionService
     * @param MrpProductionPlanImportService $mrpProductionPlanImportService
     * @return Response
     * @throws ArrayWithMixedKeysException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws InvalidTypeException
     * @throws MissingConfigurationKeyException
     * @throws NotIntegerException
     */
    public function part(MrpWeekDefinitionService $mrpWeekDefinitionService, MrpProductionPlanImportService $mrpProductionPlanImportService): Response
    {
        $groupBy = request()->get('group_by');
        if ($groupBy == 'month') {
            $dates = [];
        } elseif ($groupBy == 'week') {
            $dates = $mrpWeekDefinitionService->getWeeks();
        } else {
            $dates = $mrpWeekDefinitionService->getDates(null, true);
        }
        $mrpResults = $this->mrpResultService->getMrpResultsByPart();
        return $this->responseWithTransformer(
            $mrpResults,
            new MrpResultByPartTransformer($dates, $groupBy),
            null,
            [
                'import_file' => $this->mrpResultService->currentFilterImport,
                'running_file' => $mrpProductionPlanImportService->getFileRunningMrp()
            ]
        );
    }

    /**
     * @OA\Get (
     *     path="/mrp-results/msc",
     *     operationId="MrpResultMsc",
     *     tags={"MrpResult"},
     *     summary="List mrp result by msc",
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
     *         name="part_code",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="part_color_code",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="plant_code",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="part_group",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="group_by",
     *         in="query",
     *         description="day | month"
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
     * @param MrpWeekDefinitionService $mrpWeekDefinitionService
     * @param MrpProductionPlanImportService $mrpProductionPlanImportService
     * @return Response
     * @throws ArrayWithMixedKeysException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws InvalidTypeException
     * @throws MissingConfigurationKeyException
     * @throws NotIntegerException
     */
    public function msc(MrpWeekDefinitionService $mrpWeekDefinitionService, MrpProductionPlanImportService $mrpProductionPlanImportService): Response
    {
        $groupBy = request()->get('group_by') ?: 'day';
        $mrpResults = $this->mrpResultService->getMrpResultsByMSC();
        $mscData = $this->mrpResultService->getProductionPlanVolume($mrpResults, $groupBy);
        if ($groupBy == 'month' || $groupBy == 'week') {
            $dates = array_values($mscData)[0] ?? [];
        } else {
            $dates = $mrpWeekDefinitionService->getDates(null, true);
        }
        return $this->responseWithTransformer(
            $mrpResults,
            new MrpResultByMscTransformer($dates, $groupBy),
            null,
            [
                'msc_volume' => $mscData,
                'import_file' => $this->mrpResultService->currentFilterImport,
                'running_file' => $mrpProductionPlanImportService->getFileRunningMrp()
            ]
        );
    }

    /**
     * @OA\Get   (
     *     path="/mrp-results/columns",
     *     operationId="MrpResultColumns",
     *     tags={"MrpResult"},
     *     summary="Get column of mrp-results",
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
        $codes = $this->mrpResultService->getColumnValue();
        return $this->respond($codes);
    }

    /**
     * @OA\Get (
     *     path="/mrp-results/part/export",
     *     operationId="MrpResultExport",
     *     tags={"MrpResult"},
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
     *         name="part_code",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="part_color_code",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="plant_code",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="part_group",
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
    public function exportByPart(Request $request): BinaryFileResponse
    {
        return $this->mrpResultService->export($request, MrpResultByPartExport::class, 'mrp-result-parts');
    }

    /**
     * @OA\Get (
     *     path="/mrp-results/msc/export",
     *     operationId="MrpResultExportByMsc",
     *     tags={"MrpResult"},
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
     *         name="part_code",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="part_color_code",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="plant_code",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="part_group",
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
    public function exportByMsc(Request $request): BinaryFileResponse
    {
        return $this->mrpResultService->export($request, MrpResultByMscExport::class, 'mrp-result');
    }

    /**
     * @OA\Post  (
     *     path="/mrp-results/system-run",
     *     operationId="MrpResultSystemRun",
     *     tags={"MrpResult"},
     *     summary="System Run",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/ShortagePartSimulationRunRequest")
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
     * @param MrpResultSystemRunRequest $request
     * @return Response
     * @throws ArrayWithMixedKeysException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws InvalidTypeException
     * @throws MissingConfigurationKeyException
     * @throws NotIntegerException
     */
    public function systemRun(MrpResultSystemRunRequest $request): Response
    {
        list($rsl, $msg) = $this->mrpResultService->systemRun($request->get('import_id'), $request->get('mrp_run_date'));
        if ($rsl) {
            return $this->respond();
        } else {
            return $this->respondWithError(200, 400, $msg);
        }
    }
}
