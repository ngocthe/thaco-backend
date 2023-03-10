<?php

namespace App\Http\Controllers\Admin;

use App\Exports\UpkwhInventoryLogExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpkwhInventoryLog\CreateUpkwhInventoryLogRequest;
use App\Http\Requests\Admin\UpkwhInventoryLog\DefectUpkwhInventoryLogRequest;
use App\Http\Requests\Admin\UpkwhInventoryLog\UpdateUpkwhInventoryLogRequest;
use App\Services\UpkwhInventoryLogService;
use App\Transformers\UpkwhInventoryLogTransformer;
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

class UpkwhInventoryLogController extends Controller
{
    /**
     * @var UpkwhInventoryLogService
     */
    protected UpkwhInventoryLogService $upkwhInventoryLogService;

    /**
     * @var UpkwhInventoryLogTransformer
     */
    protected UpkwhInventoryLogTransformer $transformer;

    public function __construct(
        Manager                      $fractal,
        UpkwhInventoryLogService     $upkwhInventoryLogService,
        UpkwhInventoryLogTransformer $upkwhInventoryLogTransformer
    )
    {
        $this->upkwhInventoryLogService = $upkwhInventoryLogService;
        $this->transformer = $upkwhInventoryLogTransformer;
        parent::__construct($fractal);
    }

    /**
     * @OA\Get (
     *     path="/upkwh-inventory-logs",
     *     operationId="UPLWHInventoryLogList",
     *     tags={"UPLWHInventoryLog"},
     *     summary="List upkwh inventory log",
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
     *         name="received_date",
     *         in="query",
     *         description="format: yyyy-mm-dd"
     *     ),
     *     @OA\Parameter(
     *         name="shelf_location_code",
     *         in="query",
     *     ),
     *     @OA\Parameter(
     *         name="shipped_date",
     *         in="query",
     *         description="format: yyyy-mm-dd"
     *     ),
     *     @OA\Parameter(
     *         name="defect_id",
     *         in="query",
     *         description="O: OK, W: Wrong, D: Damage, X: Not good, S: Shortage"
     *     ),
     *     @OA\Parameter(
     *         name="updated_at",
     *         in="query",
     *         description="format: yyyy-mm-dd hh:00"
     *     ),
     *     @OA\Parameter(
     *         name="defect_id",
     *         in="query",
     *         description="in:W, D, X, S"
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
        $upkwhInventoryLogs = $this->upkwhInventoryLogService->paginate();
        return $this->responseWithTransformer($upkwhInventoryLogs, $this->transformer);
    }

    /**
     * @OA\Post  (
     *     path="/upkwh-inventory-logs",
     *     operationId="UpkwhInventoryLogCreate",
     *     tags={"UPLWHInventoryLog"},
     *     summary="Create a upkwh-inventory-logs",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CreateUpkwhInventoryLogRequest")
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
     * @param CreateUpkwhInventoryLogRequest $request
     * @return Response
     * @throws ArrayWithMixedKeysException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws InvalidTypeException
     * @throws MissingConfigurationKeyException
     * @throws NotIntegerException
     * @throws Exception
     */
    public function store(CreateUpkwhInventoryLogRequest $request): Response
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
            'received_date',
            'shelf_location_code',
            'warehouse_code',
            'defect_id',
            'plant_code'
        ]);
        list($bwhInventoryLog, $message) = $this->upkwhInventoryLogService->store($attributes);
        if ($bwhInventoryLog) {
            return $this->responseWithTransformer($bwhInventoryLog, $this->transformer);
        } else {
            return $this->respondWithError(200, 400, $message, [
                'code' => '',
                'message' => $message
            ]);
        }
    }

    /**
     * @OA\Get   (
     *     path="/upkwh-inventory-logs/columns",
     *     operationId="UPLWHInventoryLogColumns",
     *     tags={"UPLWHInventoryLog"},
     *     summary="Get column of upkwh-inventory-logs",
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
        $codes = $this->upkwhInventoryLogService->getColumnValue();
        return $this->respond($codes);
    }

    /**
     * @OA\Get (
     *     path="/upkwh-inventory-logs/export",
     *     operationId="UPLWHInventoryLogExport",
     *     tags={"UPLWHInventoryLog"},
     *     summary="Export upkwh inventory log",
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
     *         name="received_date",
     *         in="query",
     *         description="format: yyyy-mm-dd"
     *     ),
     *     @OA\Parameter(
     *         name="shelf_location_code",
     *         in="query",
     *     ),
     *     @OA\Parameter(
     *         name="shipped_date",
     *         in="query",
     *         description="format: yyyy-mm-dd"
     *     ),
     *     @OA\Parameter(
     *         name="updated_at",
     *         in="query",
     *         description="format: yyyy-mm-dd hh:00"
     *     ),
     *     @OA\Parameter(
     *         name="defect_id",
     *         in="query",
     *         description="O: OK, W: Wrong, D: Damage, X: Not good, S: Shortage"
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
        return $this->upkwhInventoryLogService->export($request, UpkwhInventoryLogExport::class,
            'upkwh-warehouse-inventory');
    }

    /**
     * @OA\Get   (
     *     path="/upkwh-inventory-logs/{id}",
     *     operationId="UPLWHInventoryLogShow",
     *     tags={"UPLWHInventoryLog"},
     *     summary="Get a upkwh inventory log detail",
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
        $upkwhInventoryLog = $this->upkwhInventoryLogService->show($id);
        return $this->responseWithTransformer($upkwhInventoryLog, $this->transformer);
    }

    /**
     * @OA\Put (
     *     path="/upkwh-inventory-logs/{id}",
     *     operationId="UPLWHInventoryLogUpdate",
     *     tags={"UPLWHInventoryLog"},
     *     summary="Update a upkwh inventory log",
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
     *         @OA\JsonContent(ref="#/components/schemas/UpdateUpkwhInventoryLogRequest")
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
     * @param UpdateUpkwhInventoryLogRequest $request
     * @return Response
     * @throws ArrayWithMixedKeysException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws InvalidTypeException
     * @throws MissingConfigurationKeyException
     * @throws NotIntegerException
     * @throws Exception
     */
    public function update(int $id, UpdateUpkwhInventoryLogRequest $request): Response
    {
        $attributes = $request->only([
            'received_date',
            'shelf_location_code',
            'warehouse_code',
            'shipped_date',
            'box_quantity',
            'shipped_box_quantity'
        ]);
        list($upkwhInventoryLog, $message) = $this->upkwhInventoryLogService->update($id, $attributes);
        if ($message) {
            return $this->respondWithError(200, 400, $message, [
                'code' => '',
                'message' => $message
            ]);
        } else {
            return $this->responseWithTransformer($upkwhInventoryLog, $this->transformer);
        }
    }

    /**
     * @OA\Delete (
     *     path="/upkwh-inventory-logs/{id}",
     *     operationId="UPLWHInventoryLogDelete",
     *     tags={"UPLWHInventoryLog"},
     *     summary="Delete a upkwh inventory log",
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
        $result = $this->upkwhInventoryLogService->destroy($id);
        if ($result) return $this->respond(); else return $this->respondWithError(200, 400, 'Item used! Can not delete!');
    }

    /**
     * @OA\Put (
     *     path="/upkwh-inventory-logs/{id}/defects",
     *     operationId="UPLWHInventoryLogDefect",
     *     tags={"UPLWHInventoryLog"},
     *     summary="Update defect for a upkwh inventory log",
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
     *         @OA\JsonContent(ref="#/components/schemas/DefectUpkwhInventoryLogRequest")
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
     * @param DefectUpkwhInventoryLogRequest $request
     * @return Response
     * @throws ArrayWithMixedKeysException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws InvalidTypeException
     * @throws MissingConfigurationKeyException
     * @throws NotIntegerException
     * @throws Exception
     */
    public function defects(int $id, DefectUpkwhInventoryLogRequest $request): Response
    {
        $attributes = $request->only([
            'defect_id',
            'box_list'
        ]);
        list($upkwhInventoryLog, $message) = $this->upkwhInventoryLogService->defects($id, $attributes);
        if ($message) {
            return $this->respondWithError(200, 400, $message, [
                'code' => '',
                'message' => $message
            ]);
        } else {
            return $this->responseWithTransformer($upkwhInventoryLog, $this->transformer);
        }
    }
}
