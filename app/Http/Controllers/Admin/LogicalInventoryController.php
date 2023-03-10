<?php

namespace App\Http\Controllers\Admin;

use App\Exports\LogicalInventoryExport;
use App\Http\Controllers\Controller;
use App\Services\LogicalInventoryService;
use App\Services\MrpWeekDefinitionService;
use App\Transformers\LogicalInventoryByPartTransformer;
use App\Transformers\LogicalInventoryTransformer;
use Carbon\Carbon;
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

class LogicalInventoryController extends Controller
{
    /**
     * @var LogicalInventoryService
     */
    protected LogicalInventoryService $logicalInventoryService;

    public function __construct(Manager $fractal, LogicalInventoryService $logicalInventoryService)
    {
        $this->logicalInventoryService = $logicalInventoryService;
        parent::__construct($fractal);
    }

    /**
     * @OA\Get (
     *     path="/logical-inventory",
     *     operationId="LogicalInventoryList",
     *     tags={"LogicalInventory"},
     *     summary="List part color",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\Parameter(
     *         name="part_code",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="part_color_code",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="date",
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
        $logicalInventories = $this->logicalInventoryService->getCurrentSummary();
        return $this->responseWithTransformer($logicalInventories,
            new LogicalInventoryByPartTransformer());
    }

    /**
     * @OA\Get (
     *     path="/logical-inventory/forecast",
     *     operationId="LogicalInventoryListForecast",
     *     tags={"LogicalInventory"},
     *     summary="List part color",
     *     security={
     *         {"sanctum": {}}
     *     },
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
     * @return Response
     * @throws ArrayWithMixedKeysException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws InvalidTypeException
     * @throws MissingConfigurationKeyException
     * @throws NotIntegerException
     */
    public function forecastInventory(): Response
    {
        $dates = (new MrpWeekDefinitionService())->getDates(Carbon::now()->toDateString());
        $logicalInventories = $this->logicalInventoryService->getForecastInventory();
        $latestImportFile = $this->logicalInventoryService->latestImportFile();
        return $this->responseWithTransformer($logicalInventories,
            new LogicalInventoryTransformer($dates), null,
            ['original_file_name' => $latestImportFile ? $latestImportFile->original_file_name : null]);
    }

    /**
     * @OA\Get   (
     *     path="/logical-inventory/columns",
     *     operationId="LogicalInventoryColumns",
     *     tags={"LogicalInventory"},
     *     summary="Get column of logical-inventory",
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
        $codes = $this->logicalInventoryService->getColumnValue();
        return $this->respond($codes);
    }

    /**
     * @OA\Get (
     *     path="/logical-inventory/export",
     *     operationId="LogicalInventoryExport",
     *     tags={"LogicalInventory"},
     *     summary="Export part color",
     *     security={
     *         {"sanctum": {}}
     *     },
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
        return $this->logicalInventoryService->export($request, LogicalInventoryExport::class, 'logical-inventory');
    }

}
