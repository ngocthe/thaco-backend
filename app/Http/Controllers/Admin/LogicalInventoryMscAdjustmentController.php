<?php

namespace App\Http\Controllers\Admin;

use App\Exports\LogicalInventoryMscAdjustmentExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LogicalInventoryMscAdjustment\CreateLogicalInventoryMscAdjustmentRequest;
use App\Services\LogicalInventoryMscAdjustmentService;
use App\Transformers\LogicalInventoryMscAdjustmentTransformer;
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

class LogicalInventoryMscAdjustmentController extends Controller
{
    /**
     * @var LogicalInventoryMscAdjustmentService
     */
    protected LogicalInventoryMscAdjustmentService $logicalInventoryMscAdjustmentService;
    /**
     * @var LogicalInventoryMscAdjustmentTransformer
     */
    protected LogicalInventoryMscAdjustmentTransformer $transformer;

    public function __construct(Manager $fractal, LogicalInventoryMscAdjustmentService $logicalInventoryMscAdjustmentService, LogicalInventoryMscAdjustmentTransformer $logicalInventoryMscAdjustmentTransformer)
    {
        $this->logicalInventoryMscAdjustmentService = $logicalInventoryMscAdjustmentService;
        $this->transformer = $logicalInventoryMscAdjustmentTransformer;
        parent::__construct($fractal);
    }

    /**
     * @OA\Get (
     *     path="/logical-inventory-msc-adjustments",
     *     operationId="LogicalInventoryMscAdjustmentsList",
     *     tags={"LogicalInventoryMscAdjustment"},
     *     summary="List warehouse adjustment",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\Parameter(
     *         name="msc_code",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="vehicle_color_code",
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
        $logicalInventoryMscAdjustments = $this->logicalInventoryMscAdjustmentService->paginate();
        return $this->responseWithTransformer($logicalInventoryMscAdjustments, $this->transformer);
    }

    /**
     * @OA\Post  (
     *     path="/logical-inventory-msc-adjustments",
     *     operationId="LogicalInventoryMscAdjustmentsCreate",
     *     tags={"LogicalInventoryMscAdjustment"},
     *     summary="Creeat a warehouse adjustment",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CreateLogicalInventoryMscAdjustmentRequest")
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
     * @param CreateLogicalInventoryMscAdjustmentRequest $request
     * @return Response
     * @throws ArrayWithMixedKeysException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws InvalidTypeException
     * @throws MissingConfigurationKeyException
     * @throws NotIntegerException
     * @throws Exception
     */
    public function store(CreateLogicalInventoryMscAdjustmentRequest $request): Response
    {
        $attributes = $request->only([
            'msc_code',
			'adjustment_quantity',
			'vehicle_color_code',
			'production_date',
			'plant_code'
        ]);
        list($logicalInventoryMscAdjustment, $message) = $this->logicalInventoryMscAdjustmentService->store($attributes);
        if ($logicalInventoryMscAdjustment) {
            return $this->responseWithTransformer($logicalInventoryMscAdjustment, $this->transformer);
        } else {
            return $this->respondWithError(200, 400, $message, [
                'code' => '',
                'message' => $message
            ]);
        }
    }

    /**
     * @OA\Get   (
     *     path="/logical-inventory-msc-adjustments/columns",
     *     operationId="LogicalInventoryMscAdjustmentColumns",
     *     tags={"LogicalInventoryMscAdjustment"},
     *     summary="Get column of logical-inventory-msc-adjustments",
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
        $codes = $this->logicalInventoryMscAdjustmentService->getColumnValue();
        return $this->respond($codes);
    }

    /**
     * @OA\Get (
     *     path="/logical-inventory-msc-adjustments/export",
     *     operationId="LogicalInventoryMscAdjustmentExport",
     *     tags={"LogicalInventoryMscAdjustment"},
     *     summary="Export part color",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\Parameter(
     *         name="msc_code",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="vehicle_color_code",
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
        return $this->logicalInventoryMscAdjustmentService->export($request,
            LogicalInventoryMscAdjustmentExport::class,
            'warehouse-logical-adjustment_msc');
    }

    /**
     * @OA\Get   (
     *     path="/logical-inventory-msc-adjustments/{id}",
     *     operationId="LogicalInventoryMscAdjustmentsShow",
     *     tags={"LogicalInventoryMscAdjustment"},
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
        $logicalInventoryMscAdjustment = $this->logicalInventoryMscAdjustmentService->show($id);
        return $this->responseWithTransformer($logicalInventoryMscAdjustment, $this->transformer);
    }

}
