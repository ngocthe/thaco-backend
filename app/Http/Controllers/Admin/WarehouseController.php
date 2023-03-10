<?php

namespace App\Http\Controllers\Admin;

use App\Exports\SupplierExport;
use App\Exports\WarehouseExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Warehouse\CreateWarehouseRequest;
use App\Http\Requests\Admin\Warehouse\UpdateWarehouseRequest;
use App\Services\WarehouseService;
use App\Transformers\WarehouseTransformer;
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

class WarehouseController extends Controller
{
    /**
     * @var WarehouseService
     */
    protected WarehouseService $warehouseService;
    /**
     * @var WarehouseTransformer
     */
    protected WarehouseTransformer $transformer;

    public function __construct(Manager $fractal, WarehouseService $warehouseService, WarehouseTransformer $warehouseTransformer)
    {
        $this->warehouseService = $warehouseService;
        $this->transformer = $warehouseTransformer;
        parent::__construct($fractal);
    }

    /**
     * @OA\Get (
     *     path="/warehouses",
     *     operationId="WarehouseList",
     *     tags={"Warehouse"},
     *     summary="List warehouse",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\Parameter(
     *         name="code",
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
        $warehouses = $this->warehouseService->paginate();
        return $this->responseWithTransformer($warehouses, $this->transformer);
    }

    /**
     * @OA\Post  (
     *     path="/warehouses",
     *     operationId="WarehouseCreate",
     *     tags={"Warehouse"},
     *     summary="Creeat a warehouse",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CreateWarehouseRequest")
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
     * @param CreateWarehouseRequest $request
     * @return Response
     * @throws ArrayWithMixedKeysException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws InvalidTypeException
     * @throws MissingConfigurationKeyException
     * @throws NotIntegerException
     * @throws Exception
     */
    public function store(CreateWarehouseRequest $request): Response
    {
        $attributes = $request->only([
            'code',
			'description',
            'warehouse_type',
			'plant_code'
        ]);
        list($warehouse, $msg) = $this->warehouseService->store($attributes);
        if ($warehouse) {
            return $this->responseWithTransformer($warehouse, $this->transformer);
        } else {
            return $this->respondWithError( 200, 400, $msg);
        }
    }

    /**
     * @OA\Get   (
     *     path="/warehouses/codes",
     *     operationId="WarehouseListCode",
     *     tags={"Warehouse"},
     *     summary="Get list warehouse codes",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\Parameter(
     *         name="code",
     *         in="query",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="plant_code",
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
    public function searchCodes(): Response
    {
        $codes = $this->warehouseService->searchCode();
        return $this->respond($codes);
    }

    /**
     * @OA\Get (
     *     path="/warehouses/export",
     *     operationId="WarehouseExport",
     *     tags={"Warehouse"},
     *     summary="Export Warehouse",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\Parameter(
     *         name="code",
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
        return $this->warehouseService->export($request, WarehouseExport::class, 'warehouse-master');
    }

    /**
     * @OA\Get   (
     *     path="/warehouses/{id}",
     *     operationId="WarehouseShow",
     *     tags={"Warehouse"},
     *     summary="Get a warehouse detail",
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
        $warehouse = $this->warehouseService->show($id);
        return $this->responseWithTransformer($warehouse, $this->transformer);
    }

    /**
     * @OA\Put (
     *     path="/warehouses/{id}",
     *     operationId="WarehouseUpdate",
     *     tags={"Warehouse"},
     *     summary="Update a warehouse",
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
     *         @OA\JsonContent(ref="#/components/schemas/UpdateWarehouseRequest")
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
     * @param UpdateWarehouseRequest $request
     * @return Response
     * @throws ArrayWithMixedKeysException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws InvalidTypeException
     * @throws MissingConfigurationKeyException
     * @throws NotIntegerException
     * @throws Exception
     */
    public function update(int $id, UpdateWarehouseRequest $request): Response
    {
        $attributes = $request->only([
			'description'
        ]);
        $warehouse = $this->warehouseService->update($id, $attributes);
        return $this->responseWithTransformer($warehouse, $this->transformer);
    }

    /**
     * @OA\Delete (
     *     path="/warehouses/{id}",
     *     operationId="WarehouseDelete",
     *     tags={"Warehouse"},
     *     summary="Delete a warehouse",
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
        $result = $this->warehouseService->destroy($id);
        if ($result) return $this->respond(); else return $this->respondWithError( 200, 400, 'Item used! Can not delete!');
    }

    /**
     * @OA\Get   (
     *     path="/warehouses/detail",
     *     operationId="WarehouseDetail",
     *     tags={"Warehouse"},
     *     summary="Get a warehouse detail by code and plant code",
     *     security={
     *         {"sanctum": {}}
     *     },
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
     * @return Response
     * @throws ArrayWithMixedKeysException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws InvalidTypeException
     * @throws MissingConfigurationKeyException
     * @throws NotIntegerException
     */
    public function detail(Request $request): Response
    {
        $warehouse = $this->warehouseService->findBy([
            'code' => $request->warehouse_code,
            'plant_code' => $request->plant_code,
        ]);

        if (!$warehouse) {
            return $this->respondWithError(200, 404, 'Object not found');
        }

        return $this->responseWithTransformer($warehouse, $this->transformer);
    }
}
