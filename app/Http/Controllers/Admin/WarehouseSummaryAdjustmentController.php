<?php

namespace App\Http\Controllers\Admin;

use App\Exports\WarehouseSummaryAdjustmentExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\WarehouseSummaryAdjustment\CreateWarehouseSummaryAdjustmentRequest;
use App\Services\WarehouseSummaryAdjustmentService;
use App\Transformers\WarehouseSummaryAdjustmentTransformer;
use Exception;
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

class WarehouseSummaryAdjustmentController extends Controller
{
    /**
     * @var WarehouseSummaryAdjustmentService
     */
    protected WarehouseSummaryAdjustmentService $warehouseSummaryAdjustmentService;

    /**
     * @var WarehouseSummaryAdjustmentTransformer
     */
    protected WarehouseSummaryAdjustmentTransformer $transformer;

    public function __construct(
        Manager $fractal,
        WarehouseSummaryAdjustmentService $warehouseSummaryAdjustmentService,
        WarehouseSummaryAdjustmentTransformer $warehouseSummaryAdjustmentTransformer
    ) {
        $this->warehouseSummaryAdjustmentService = $warehouseSummaryAdjustmentService;
        $this->transformer = $warehouseSummaryAdjustmentTransformer;
        parent::__construct($fractal);
    }

    /**
     * @OA\Get (
     *     path="/warehouse-summary-adjustments",
     *     operationId="WarehouseSummaryAdjustmentsList",
     *     tags={"WarehouseSummaryAdjustment"},
     *     summary="List warehouse adjustment",
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
     *         name="plant_code",
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
        $warehouseSummaryAdjustments = $this->warehouseSummaryAdjustmentService->paginate();
        return $this->responseWithTransformer($warehouseSummaryAdjustments, $this->transformer);
    }

    /**
     * @OA\Post  (
     *     path="/warehouse-summary-adjustments",
     *     operationId="WarehouseSummaryAdjustmentsCreate",
     *     tags={"WarehouseSummaryAdjustment"},
     *     summary="Creeat a warehouse adjustment",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CreateWarehouseSummaryAdjustmentRequest")
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
     * @param CreateWarehouseSummaryAdjustmentRequest $request
     * @return Response
     * @throws ArrayWithMixedKeysException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws InvalidTypeException
     * @throws MissingConfigurationKeyException
     * @throws NotIntegerException
     * @throws Exception
     */
    public function store(CreateWarehouseSummaryAdjustmentRequest $request): Response
    {
        $attributes = $request->only([
            'warehouse_code',
            'part_code',
            'part_color_code',
            'adjustment_quantity',
            'plant_code'
        ]);
        list($warehouseSummaryAdjustment, $msg) = $this->warehouseSummaryAdjustmentService->store($attributes);
        if ($msg) {
            return $this->respondWithError(200, 400, $msg);
        } else {
            return $this->responseWithTransformer($warehouseSummaryAdjustment, $this->transformer);
        }
    }

    /**
     * @OA\Get   (
     *     path="/warehouse-summary-adjustments/columns",
     *     operationId="WarehouseSummaryAdjustmentColumns",
     *     tags={"WarehouseSummaryAdjustment"},
     *     summary="Get column of warehouse-summary-adjustments",
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
        $codes = $this->warehouseSummaryAdjustmentService->getColumnValue();
        return $this->respond($codes);
    }

    /**
     * @OA\Get (
     *     path="/warehouse-summary-adjustments/export",
     *     operationId="WarehouseSummaryAdjustmentExport",
     *     tags={"WarehouseSummaryAdjustment"},
     *     summary="Export part color",
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
     *         name="plant_code",
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
    public function export(Request $request): BinaryFileResponse
    {
        return $this->warehouseSummaryAdjustmentService->export($request, WarehouseSummaryAdjustmentExport::class,
            'warehouse-summary-adjustment');
    }

    /**
     * @OA\Get   (
     *     path="/warehouse-summary-adjustments/{id}",
     *     operationId="WarehouseSummaryAdjustmentsShow",
     *     tags={"WarehouseSummaryAdjustment"},
     *     summary="Get a warehouse adjustment detail",
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
        $warehouseSummaryAdjustment = $this->warehouseSummaryAdjustmentService->show($id);
        return $this->responseWithTransformer($warehouseSummaryAdjustment, $this->transformer);
    }

}
