<?php

namespace App\Http\Controllers\Admin;

use App\Exports\SupplierExport;
use App\Exports\WarehouseLocationExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\WarehouseLocation\CreateWarehouseLocationRequest;
use App\Http\Requests\Admin\WarehouseLocation\UpdateWarehouseLocationRequest;
use App\Services\WarehouseLocationService;
use App\Transformers\WarehouseLocationTransformer;
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

class WarehouseLocationController extends Controller
{
    /**
     * @var WarehouseLocationService
     */
    protected WarehouseLocationService $warehouseLocationService;
    /**
     * @var WarehouseLocationTransformer
     */
    protected WarehouseLocationTransformer $transformer;

    public function __construct(Manager $fractal, WarehouseLocationService $warehouseLocationService, WarehouseLocationTransformer $warehouseLocationTransformer)
    {
        $this->warehouseLocationService = $warehouseLocationService;
        $this->transformer = $warehouseLocationTransformer;
        parent::__construct($fractal);
    }

    /**
     * @OA\Get (
     *     path="/warehouse-locations",
     *     operationId="WarehouseLocationList",
     *     tags={"WarehouseLocation"},
     *     summary="List warehouse location",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\Parameter(
     *         name="code",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="warehouse_code",
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
        $warehouseLocations = $this->warehouseLocationService->paginate();
        return $this->responseWithTransformer($warehouseLocations, $this->transformer);
    }

    /**
     * @OA\Post  (
     *     path="/warehouse-locations",
     *     operationId="WarehouseLocationCreate",
     *     tags={"WarehouseLocation"},
     *     summary="Creeat a warehouse location",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CreateWarehouseLocationRequest")
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
     * @param CreateWarehouseLocationRequest $request
     * @return Response
     * @throws ArrayWithMixedKeysException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws InvalidTypeException
     * @throws MissingConfigurationKeyException
     * @throws NotIntegerException
     * @throws Exception
     */
    public function store(CreateWarehouseLocationRequest $request): Response
    {
        $attributes = $request->only([
            'code',
			'warehouse_code',
			'description',
			'plant_code'
        ]);
        $warehouseLocation = $this->warehouseLocationService->store($attributes);
        return $this->responseWithTransformer($warehouseLocation, $this->transformer);
    }

    /**
     * @OA\Get   (
     *     path="/warehouse-locations/codes",
     *     operationId="WarehouseLocationListCode",
     *     tags={"WarehouseLocation"},
     *     summary="Get list warehouse location codes",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\Parameter(
     *         name="code",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="warehouse_code",
     *         in="query"
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
        $codes = $this->warehouseLocationService->searchCode();
        return $this->respond($codes);
    }

    /**
     * @OA\Get (
     *     path="/warehouse-locations/export",
     *     operationId="WarehouseLocationExport",
     *     tags={"WarehouseLocation"},
     *     summary="Export Warehouse Location",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\Parameter(
     *         name="code",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="warehouse_code",
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
        return $this->warehouseLocationService->export($request, WarehouseLocationExport::class, 'warehouse-location-master');
    }

    /**
     * @OA\Get   (
     *     path="/warehouse-locations/{id}",
     *     operationId="WarehouseLocationShow",
     *     tags={"WarehouseLocation"},
     *     summary="Get a warehouse location detail",
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
        $warehouseLocation = $this->warehouseLocationService->show($id);
        return $this->responseWithTransformer($warehouseLocation, $this->transformer);
    }

    /**
     * @OA\Put (
     *     path="/warehouse-locations/{id}",
     *     operationId="WarehouseLocationUpdate",
     *     tags={"WarehouseLocation"},
     *     summary="Update a warehouse location",
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
     *         @OA\JsonContent(ref="#/components/schemas/UpdateWarehouseLocationRequest")
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
     * @param UpdateWarehouseLocationRequest $request
     * @return Response
     * @throws ArrayWithMixedKeysException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws InvalidTypeException
     * @throws MissingConfigurationKeyException
     * @throws NotIntegerException
     * @throws Exception
     */
    public function update(int $id, UpdateWarehouseLocationRequest $request): Response
    {
        $attributes = $request->only([
			'description'
        ]);
        $warehouseLocation = $this->warehouseLocationService->update($id, $attributes);
        return $this->responseWithTransformer($warehouseLocation, $this->transformer);
    }

    /**
     * @OA\Delete (
     *     path="/warehouse-locations/{id}",
     *     operationId="WarehouseLocationDelete",
     *     tags={"WarehouseLocation"},
     *     summary="Delete a warehouse location",
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
        $result = $this->warehouseLocationService->destroy($id);
        if ($result) return $this->respond(); else return $this->respondWithError( 200, 400, 'Item used! Can not delete!');
    }

    /**
     * @OA\Get   (
     *     path="/warehouse-locations/detail",
     *     operationId="WarehouseLocationDetail",
     *     tags={"Warehouse"},
     *     summary="Get a warehouse location detail by warehouse code and plant code",
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
        $warehouseLocation = $this->warehouseLocationService->findBy([
            'warehouse_code' => $request->warehouse_code,
            'plant_code' => $request->plant_code,
        ]);

        if (!$warehouseLocation) {
            return $this->respondWithError(200, 404, 'Object not found');
        }

        return $this->responseWithTransformer($warehouseLocation, $this->transformer);
    }
}
