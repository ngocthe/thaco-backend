<?php

namespace App\Http\Controllers\Admin;

use App\Exports\PlantInventoryLogExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PlantInventoryLog\CreatePlantInventoryLogRequest;
use App\Http\Requests\Admin\PlantInventoryLog\DefectPlantInventoryLogRequest;
use App\Http\Requests\Admin\PlantInventoryLog\UpdatePlantInventoryLogRequest;
use App\Services\PlantInventoryLogService;
use App\Transformers\PlantInventoryLogTransformer;
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

class PlantInventoryLogController extends Controller
{
    /**
     * @var PlantInventoryLogService
     */
    protected PlantInventoryLogService $plantInventoryLogService;
    /**
     * @var PlantInventoryLogTransformer
     */
    protected PlantInventoryLogTransformer $transformer;

    public function __construct(
        Manager $fractal,
        PlantInventoryLogService $plantInventoryLogService,
        PlantInventoryLogTransformer $plantInventoryLogTransformer
    ) {
        $this->plantInventoryLogService = $plantInventoryLogService;
        $this->transformer = $plantInventoryLogTransformer;
        parent::__construct($fractal);
    }

    /**
     * @OA\Get (
     *     path="/plant-inventory-logs",
     *     operationId="PlantInventoryLogList",
     *     tags={"PlantInventoryLog"},
     *     summary="List Plant Inventory Log",
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
     *         name="warehouse_code",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="received_date",
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
        $plantInventoryLogs = $this->plantInventoryLogService->paginate();
        return $this->responseWithTransformer($plantInventoryLogs, $this->transformer);
    }

    /**
     * @OA\Post (
     *     path="/plant-inventory-logs",
     *     operationId="CreatePlantInventoryLog",
     *     tags={"PlantInventoryLog"},
     *     summary="Create Plant Inventory Log",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CreatePlantInventoryLogRequest")
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
     * @param CreatePlantInventoryLogRequest $request
     * @return Response
     * @throws ArrayWithMixedKeysException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws InvalidTypeException
     * @throws MissingConfigurationKeyException
     * @throws NotIntegerException
     * @throws Exception
     */
    public function store(CreatePlantInventoryLogRequest $request): Response
    {
        $attributes = $request->only([
            'part_code',
            'part_color_code',
            'box_type_code',
            'received_date',
            'warehouse_code',
            'defect_id',
            'plant_code'
        ]);

        list($planInventoryLog, $message) = $this->plantInventoryLogService->store($attributes);
        if ($planInventoryLog) {
            return $this->responseWithTransformer($planInventoryLog, $this->transformer);
        } else {
            return $this->respondWithError(200, 400, $message, [
                'code' => '',
                'message' => $message
            ]);
        }
    }

    /**
     * @OA\Get   (
     *     path="/plant-inventory-logs/columns",
     *     operationId="PlantInventoryLogColumns",
     *     tags={"PlantInventoryLog"},
     *     summary="Get column of plant-inventory-logs",
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
        $codes = $this->plantInventoryLogService->getColumnValue();
        return $this->respond($codes);
    }

    /**
     * @OA\Get (
     *     path="/plant-inventory-logs/export",
     *     operationId="PlantInventoryLogExport",
     *     tags={"PlantInventoryLog"},
     *     summary="Export Plant Inventory Log",
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
     *         name="warehouse_code",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="received_date",
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
        return $this->plantInventoryLogService->export($request, PlantInventoryLogExport::class,
            'plant-warehouse-inventory');
    }

    /**
     * @OA\Get   (
     *     path="/plant-inventory-logs/{id}",
     *     operationId="PlantInventoryLogShow",
     *     tags={"PlantInventoryLog"},
     *     summary="Get a Plant Inventory Log detail",
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
        $plantInventoryLog = $this->plantInventoryLogService->show($id);
        return $this->responseWithTransformer($plantInventoryLog, $this->transformer);
    }

    /**
     * @OA\Put (
     *     path="/plant-inventory-logs/{id}",
     *     operationId="PlantInventoryLogUpdate",
     *     tags={"PlantInventoryLog"},
     *     summary="Update a Plant Inventory Log",
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
     *         @OA\JsonContent(ref="#/components/schemas/UpdatePlantInventoryLogRequest")
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
     * @param UpdatePlantInventoryLogRequest $request
     * @return Response
     * @throws ArrayWithMixedKeysException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws InvalidTypeException
     * @throws MissingConfigurationKeyException
     * @throws NotIntegerException
     * @throws Exception
     */
    public function update(int $id, UpdatePlantInventoryLogRequest $request): Response
    {
        $attributes = $request->only([
            'quantity'
        ]);
        $plantInventoryLog = $this->plantInventoryLogService->update($id, $attributes);
        return $this->responseWithTransformer($plantInventoryLog, $this->transformer);
    }

    /**
     * @OA\Delete (
     *     path="/plant-inventory-logs/{id}",
     *     operationId="PlantInventoryLogDelete",
     *     tags={"PlantInventoryLog"},
     *     summary="Delete a Plant Inventory Log",
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
        $this->plantInventoryLogService->destroy($id);
        return $this->respond();
    }

    /**
     * @OA\Put (
     *     path="/plant-inventory-logs/{id}/defects",
     *     operationId="PlantInventoryLogDefect",
     *     tags={"PlantInventoryLog"},
     *     summary="Update defect for a plant inventory log",
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
     *         @OA\JsonContent(ref="#/components/schemas/DefectPlantInventoryLogRequest")
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
     * @param DefectPlantInventoryLogRequest $request
     * @return Response
     * @throws ArrayWithMixedKeysException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws InvalidTypeException
     * @throws MissingConfigurationKeyException
     * @throws NotIntegerException
     * @throws Exception
     */
    public function defects(int $id, DefectPlantInventoryLogRequest $request): Response
    {
        $attributes = $request->only([
            'defect_id',
            'box_list'
        ]);
        list($rsl, $message, $dataErrors) = $this->plantInventoryLogService->defects($id, $attributes);
        if (!$message) {
            return $this->responseWithTransformer($rsl, $this->transformer);
        } else {
            return $this->respondWithError(200, 400, $message, $dataErrors);
        }
    }
}
