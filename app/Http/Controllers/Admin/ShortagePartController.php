<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ShortagePartExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ShortagePart\CreateShortagePartRemarkRequest;
use App\Http\Requests\Admin\ShortagePart\ShortagePartSimulationRunRequest;
use App\Services\MrpProductionPlanImportService;
use App\Services\MrpWeekDefinitionService;
use App\Services\ShortagePartService;
use App\Transformers\ShortagePartTransformer;
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

class ShortagePartController extends Controller
{
    /**
     * @var ShortagePartService
     */
    protected ShortagePartService $shortagePartService;


    public function __construct(Manager $fractal, ShortagePartService $shortagePartService)
    {
        $this->shortagePartService = $shortagePartService;
        parent::__construct($fractal);
    }

    /**
     * @OA\Get (
     *     path="/shortage-parts",
     *     operationId="ShortagePart",
     *     tags={"ShortagePart"},
     *     summary="List shortage part",
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
     *         name="part_code",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="part_color_code",
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
    public function index(MrpWeekDefinitionService $mrpWeekDefinitionService, MrpProductionPlanImportService $mrpProductionPlanImportService): Response
    {
        $dates = $mrpWeekDefinitionService->getDates(null, true);
        $shortageParts = $this->shortagePartService->filterShortagePart();
        $remarks = $this->shortagePartService->getRemarks($shortageParts);
        return $this->responseWithTransformer(
            $shortageParts,
            new ShortagePartTransformer($dates, $remarks),
            null,
            [
                'import_file' => $this->shortagePartService->currentFilterImport,
                'running_file' => $mrpProductionPlanImportService->getFileRunningShortage()
            ]
        );
    }

    /**
     * @OA\Get   (
     *     path="/shortage-parts/columns",
     *     operationId="ShortagePartColumns",
     *     tags={"ShortagePart"},
     *     summary="Get column of shortage-parts",
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
        $codes = $this->shortagePartService->getColumnValue();
        return $this->respond($codes);
    }

    /**
     * @OA\Get (
     *     path="/shortage-parts/export",
     *     operationId="ShortagePartExport",
     *     tags={"ShortagePart"},
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
     *         name="part_code",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="part_color_code",
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
    public function export(Request $request): BinaryFileResponse
    {
        return $this->shortagePartService->export($request, ShortagePartExport::class, 'shortage-parts');
    }

    /**
     * @OA\Post  (
     *     path="/shortage-parts/remarks",
     *     operationId="ShortagePartRemark",
     *     tags={"ShortagePart"},
     *     summary="Creeat a remark",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CreateShortagePartRemarkRequest")
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
     * @param CreateShortagePartRemarkRequest $request
     * @return Response
     * @throws ArrayWithMixedKeysException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws InvalidTypeException
     * @throws MissingConfigurationKeyException
     * @throws NotIntegerException
     */
    public function remarks(CreateShortagePartRemarkRequest $request): Response
    {
        $data = $request->only([
            'part_code', 'part_color_code', 'plan_date', 'plant_code', 'remark', 'import_id'
        ]);
        $this->shortagePartService->addRemark($data['part_code'], $data['part_color_code'], $data['plan_date'], $data['plant_code'], $data['import_id']);
        return $this->respond();
    }

    /**
     * @OA\Post  (
     *     path="/shortage-parts/simulation-run",
     *     operationId="ShortagePartSimulationRun",
     *     tags={"ShortagePart"},
     *     summary="Simulation Run",
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
     * @param ShortagePartSimulationRunRequest $request
     * @return Response
     * @throws ArrayWithMixedKeysException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws InvalidTypeException
     * @throws MissingConfigurationKeyException
     * @throws NotIntegerException
     */
    public function simulationRun(ShortagePartSimulationRunRequest $request): Response
    {
        list($rsl, $msg) = $this->shortagePartService->simulationRun($request->get('import_id'), $request->get('mrp_run_date'));
        if ($rsl) {
            return $this->respond();
        } else {
            return $this->respondWithError(200, 400, $msg);
        }
    }

}
