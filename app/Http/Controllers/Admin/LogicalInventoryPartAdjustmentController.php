<?php

namespace App\Http\Controllers\Admin;

use App\Exports\LogicalInventoryPartAdjustmentExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LogicalInventoryPartAdjustment\CreateLogicalInventoryPartAdjustmentRequest;
use App\Services\LogicalInventoryPartAdjustmentService;
use App\Transformers\LogicalInventoryPartAdjustmentTransformer;
use Carbon\Carbon;
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

class LogicalInventoryPartAdjustmentController extends Controller
{
    /**
     * @var LogicalInventoryPartAdjustmentService
     */
    protected LogicalInventoryPartAdjustmentService $logicalInventoryPartAdjustmentService;
    /**
     * @var LogicalInventoryPartAdjustmentTransformer
     */
    protected LogicalInventoryPartAdjustmentTransformer $transformer;

    public function __construct(
        Manager $fractal,
        LogicalInventoryPartAdjustmentService $logicalInventoryPartAdjustmentService,
        LogicalInventoryPartAdjustmentTransformer $logicalInventoryPartAdjustmentTransformer
    ) {
        $this->logicalInventoryPartAdjustmentService = $logicalInventoryPartAdjustmentService;
        $this->transformer = $logicalInventoryPartAdjustmentTransformer;
        parent::__construct($fractal);
    }

    /**
     * @OA\Get (
     *     path="/logical-inventory-part-adjustments",
     *     operationId="LogicalInventoryPartAdjustmentsList",
     *     tags={"LogicalInventoryPartAdjustment"},
     *     summary="List warehouse adjustment",
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
        $logicalInventoryPartAdjustments = $this->logicalInventoryPartAdjustmentService->paginate();
        return $this->responseWithTransformer($logicalInventoryPartAdjustments, $this->transformer);
    }

    /**
     * @OA\Post  (
     *     path="/logical-inventory-part-adjustments",
     *     operationId="LogicalInventoryPartAdjustmentsCreate",
     *     tags={"LogicalInventoryPartAdjustment"},
     *     summary="Creeat a warehouse adjustment",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CreateLogicalInventoryPartAdjustmentRequest")
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
     * @param CreateLogicalInventoryPartAdjustmentRequest $request
     * @return Response
     * @throws ArrayWithMixedKeysException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws InvalidTypeException
     * @throws MissingConfigurationKeyException
     * @throws NotIntegerException
     * @throws Exception
     */
    public function store(CreateLogicalInventoryPartAdjustmentRequest $request): Response
    {
        $attributes = $request->only([
            'part_code',
            'part_color_code',
            'adjustment_quantity',
            'plant_code',
            'adjustment_date'
        ]);
        $attributes['adjustment_date'] = Carbon::createFromFormat('n/d/Y', $attributes['adjustment_date'])->toDateString();
        $logicalInventoryPartAdjustment = $this->logicalInventoryPartAdjustmentService->store($attributes);
        return $this->responseWithTransformer($logicalInventoryPartAdjustment, $this->transformer);
    }

    /**
     * @OA\Get   (
     *     path="/logical-inventory-part-adjustments/columns",
     *     operationId="LogicalInventoryPartAdjustmentColumns",
     *     tags={"LogicalInventoryPartAdjustment"},
     *     summary="Get column of logical-inventory-part-adjustments",
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
        $codes = $this->logicalInventoryPartAdjustmentService->getColumnValue();
        return $this->respond($codes);
    }

    /**
     * @OA\Get (
     *     path="/logical-inventory-part-adjustments/export",
     *     operationId="LogicalInventoryPartAdjustmentExport",
     *     tags={"LogicalInventoryPartAdjustment"},
     *     summary="Export part color",
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
        return $this->logicalInventoryPartAdjustmentService->export($request,
            LogicalInventoryPartAdjustmentExport::class,
            'warehouse-logical-adjustment_part');
    }

    /**
     * @OA\Get   (
     *     path="/logical-inventory-part-adjustments/{id}",
     *     operationId="LogicalInventoryPartAdjustmentsShow",
     *     tags={"LogicalInventoryPartAdjustment"},
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
        $logicalInventoryPartAdjustment = $this->logicalInventoryPartAdjustmentService->show($id);
        return $this->responseWithTransformer($logicalInventoryPartAdjustment, $this->transformer);
    }
}
