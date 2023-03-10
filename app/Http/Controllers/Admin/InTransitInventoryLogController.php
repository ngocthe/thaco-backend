<?php

namespace App\Http\Controllers\Admin;

use App\Exports\InTransitInventoryLogExport;
use App\Exports\InTransitInventoryLogForBwhExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\InTransitInventoryLog\CreateInTransitInventoryLogRequest;
use App\Http\Requests\Admin\InTransitInventoryLog\UpdateInTransitInventoryLogRequest;
use App\Services\InTransitInventoryLogService;
use App\Transformers\InTransitInventoryLogTransformer;
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

class InTransitInventoryLogController extends Controller
{
    /**
     * @var InTransitInventoryLogService
     */
    protected InTransitInventoryLogService $inTransitInventoryLogService;
    /**
     * @var InTransitInventoryLogTransformer
     */
    protected InTransitInventoryLogTransformer $transformer;

    public function __construct(Manager $fractal, InTransitInventoryLogService $inTransitInventoryLogService, InTransitInventoryLogTransformer $inTransitInventoryLogTransformer)
    {
        $this->inTransitInventoryLogService = $inTransitInventoryLogService;
        $this->transformer = $inTransitInventoryLogTransformer;
        parent::__construct($fractal);
    }

    /**
     * @OA\Get (
     *     path="/in-transit-inventory-logs",
     *     operationId="InTransitInventoryLogList",
     *     tags={"InTransitInventoryLog"},
     *     summary="List part color",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\Parameter(
     *         name="contract_code",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="invoice_code",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="bill_of_lading_code",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="container_code",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="case_code",
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
     *         name="supplier_code",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="container_shipped",
     *         in="query",
     *         description="format: yyyy-mm-dd"
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
        $inTransitInventoryLogs = $this->inTransitInventoryLogService->paginate();
        return $this->responseWithTransformer($inTransitInventoryLogs, $this->transformer);
    }

    /**
     * @OA\Post  (
     *     path="/in-transit-inventory-logs",
     *     operationId="InTransitInventoryLogCreate",
     *     tags={"InTransitInventoryLog"},
     *     summary="Create a in-transit-inventory-logs",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CreateInTransitInventoryLogRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request"
     *     ),
     *     @OA\Response(
     *          response=401,
     *          description="Not authorized"
     *      )
     * )
     * @param CreateInTransitInventoryLogRequest $request
     * @return Response
     * @throws ArrayWithMixedKeysException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws InvalidTypeException
     * @throws MissingConfigurationKeyException
     * @throws NotIntegerException
     * @throws Exception
     */
    public function store(CreateInTransitInventoryLogRequest $request): Response
    {
        $attributes = $request->only([
            'contract_code',
            'invoice_code',
            'bill_of_lading_code',
            'container_code',
            'case_code',
            'part_code',
            'part_color_code',
            'box_type_code',
            'box_quantity',
            'supplier_code',
            'etd',
            'container_shipped',
            'eta',
            'plant_code'
        ]);
        $inTransitInventoryLog = $this->inTransitInventoryLogService->store($attributes);
        return $this->responseWithTransformer($inTransitInventoryLog, $this->transformer);
    }

    /**
     * @OA\Get   (
     *     path="/in-transit-inventory-logs/columns",
     *     operationId="InTransitInventoryLogColumns",
     *     tags={"InTransitInventoryLog"},
     *     summary="Get column of in-transit-inventory-logs",
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
        $codes = $this->inTransitInventoryLogService->getColumnValue();
        return $this->respond($codes);
    }

    /**
     * @OA\Get (
     *     path="/in-transit-inventory-logs/export",
     *     operationId="InTransitInventoryLogExport",
     *     tags={"InTransitInventoryLog"},
     *     summary="Export part color",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\Parameter(
     *         name="contract_code",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="invoice_code",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="bill_of_lading_code",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="container_code",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="case_code",
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
     *         name="supplier_code",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="container_shipped",
     *         in="query",
     *         description="format: yyyy-mm-dd"
     *     ),
     *     @OA\Parameter(
     *         name="updated_at",
     *         in="query",
     *         description="format: yyyy-mm-dd hh:00"
     *     ),
     *     @OA\Parameter(
     *         name="export_for_bwh",
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
        $export_for_bwh = $request->get('export_for_bwh');
        if ($export_for_bwh == 'true' || $export_for_bwh == 1) {
            return $this->inTransitInventoryLogService->export($request, InTransitInventoryLogForBwhExport::class, 'bonded-warehouse-scanning-data');
        } else {
            return $this->inTransitInventoryLogService->export($request, InTransitInventoryLogExport::class, 'in-transit-inventory');
        }
    }

    /**
     * @OA\Get   (
     *     path="/in-transit-inventory-logs/{id}",
     *     operationId="InTransitInventoryLogShow",
     *     tags={"InTransitInventoryLog"},
     *     summary="Get a part color detail",
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
        $inTransitInventoryLog = $this->inTransitInventoryLogService->show($id);
        return $this->responseWithTransformer($inTransitInventoryLog, $this->transformer);
    }

    /**
     * @OA\Put (
     *     path="/in-transit-inventory-logs/{id}",
     *     operationId="InTransitInventoryLogUpdate",
     *     tags={"InTransitInventoryLog"},
     *     summary="Update a part color",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\Parameter(
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpdateInTransitInventoryLogRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request"
     *     ),
     *     @OA\Response(
     *          response=401,
     *          description="Not authorized"
     *      )
     * )
     * @param int $id
     * @param UpdateInTransitInventoryLogRequest $request
     * @return Response
     * @throws ArrayWithMixedKeysException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws InvalidTypeException
     * @throws MissingConfigurationKeyException
     * @throws NotIntegerException
     * @throws Exception
     */
    public function update(int $id, UpdateInTransitInventoryLogRequest $request): Response
    {
        $attributes = $request->only([
			'box_quantity',
			'part_quantity',
            'unit',
			'etd',
			'container_shipped',
			'eta'
        ]);
        $inTransitInventoryLog = $this->inTransitInventoryLogService->update($id, $attributes);
        if ($inTransitInventoryLog) {
            return $this->responseWithTransformer($inTransitInventoryLog, $this->transformer);
        } else {
            return $this->respondWithError(200, 400, 'Information cannot be updated once the container has been shipped to BWH Inventory log');
        }
    }

    /**
     * @OA\Delete (
     *     path="/in-transit-inventory-logs/{id}",
     *     operationId="InTransitInventoryLogDelete",
     *     tags={"InTransitInventoryLog"},
     *     summary="Delete a part color",
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
    public function destroy(int $id): Response
    {
        $result = $this->inTransitInventoryLogService->destroy($id);
        if ($result) return $this->respond(); else return $this->respondWithError( 200, 400, 'Item used! Can not delete!');
    }
}
