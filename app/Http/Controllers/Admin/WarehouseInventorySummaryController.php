<?php

namespace App\Http\Controllers\Admin;

use App\Exports\WarehouseInventorySummaryExport;
use App\Exports\WarehouseInventorySummaryGroupByPartExport;
use App\Http\Controllers\Controller;
use App\Services\WarehouseInventorySummaryService;
use App\Transformers\WarehouseInventorySummaryByPartTransformer;
use App\Transformers\WarehouseInventorySummaryTransformer;
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

class WarehouseInventorySummaryController extends Controller
{
    /**
     * @var WarehouseInventorySummaryService
     */
    protected WarehouseInventorySummaryService $whInventorySummaryService;
    /**
     * @var WarehouseInventorySummaryTransformer
     */
    protected WarehouseInventorySummaryTransformer $transformer;

    public function __construct(
        Manager $fractal,
        WarehouseInventorySummaryService $whInventorySummaryService,
        WarehouseInventorySummaryTransformer $whInventorySummaryTransformer
    ) {
        $this->whInventorySummaryService = $whInventorySummaryService;
        $this->transformer = $whInventorySummaryTransformer;
        parent::__construct($fractal);
    }

    /**
     * @OA\Get (
     *     path="/warehouse-inventory-summaries",
     *     operationId="WarehouseInventorySummaryList",
     *     tags={"WarehouseInventorySummary"},
     *     summary="List warehouse summary group by warehouse",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\Parameter(
     *         name="warehouse_code",
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
     *         name="updated_at",
     *         in="query",
     *         description="format: yyyy-mm-dd hh:00"
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
        $whInventorySummaries = $this->whInventorySummaryService->paginate();
        return $this->responseWithTransformer($whInventorySummaries, $this->transformer);
    }

    /**
     * @OA\Get (
     *     path="/warehouse-inventory-summaries/parts",
     *     operationId="WarehouseInventorySummaryListPart",
     *     tags={"WarehouseInventorySummary"},
     *     summary="List warehouse summary group by part",
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
    public function parts(): Response
    {
        $warehouseCodes = $this->whInventorySummaryService->getWarehouseCodes();
        $whInventorySummaries = $this->whInventorySummaryService->filterGroupByPart();
        $additionalData = [
            'warehouse_codes' => array_keys($warehouseCodes)
        ];
        return $this->responseWithTransformer($whInventorySummaries,
            new WarehouseInventorySummaryByPartTransformer($warehouseCodes), null, $additionalData);
    }

    /**
     * @OA\Get   (
     *     path="/warehouse-inventory-summaries/columns",
     *     operationId="WarehouseInventorySummaryColumns",
     *     tags={"WarehouseInventorySummary"},
     *     summary="Get column of warehoue summary",
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
        $codes = $this->whInventorySummaryService->getColumnValue();
        return $this->respond($codes);
    }

    /**
     * @OA\Get (
     *     path="/warehouse-inventory-summaries/export",
     *     operationId="WarehouseInventorySummaryExport",
     *     tags={"WarehouseInventorySummary"},
     *     summary="Export warehouse summary",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\Parameter(
     *         name="warehouse_code",
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
     *         name="updated_at",
     *         in="query",
     *         description="format: yyyy-mm-dd hh:00"
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
        return $this->whInventorySummaryService->export($request, WarehouseInventorySummaryExport::class,
            'warehouse-inventory-summary');
    }

    /**
     * @OA\Get (
     *     path="/warehouse-inventory-summaries/parts/export",
     *     operationId="WarehouseInventorySummaryExportByPart",
     *     tags={"WarehouseInventorySummary"},
     *     summary="Export warehouse summary group by part",
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
    public function partExport(Request $request): BinaryFileResponse
    {
        return $this->whInventorySummaryService->export($request, WarehouseInventorySummaryGroupByPartExport::class,
            'warehouse-inventory-summary-part');
    }

    /**
     * @OA\Get   (
     *     path="/warehouse-inventory-summaries/{id}",
     *     operationId="WarehouseInventorySummaryShow",
     *     tags={"WarehouseInventorySummary"},
     *     summary="Get a warehouse summary detail",
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
     * @return Response
     * @throws ArrayWithMixedKeysException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws InvalidTypeException
     * @throws MissingConfigurationKeyException
     * @throws NotIntegerException
     */
    public function show(int $id): Response
    {
        $whInventorySummary = $this->whInventorySummaryService->show($id);
        return $this->responseWithTransformer($whInventorySummary, $this->transformer);
    }

}
