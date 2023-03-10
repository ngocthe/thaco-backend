<?php

namespace App\Http\Controllers\Admin;

use App\Exports\SupplierExport;
use App\Exports\VehicleColorExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\VehicleColor\CreateVehicleColorRequest;
use App\Http\Requests\Admin\VehicleColor\UpdateVehicleColorRequest;
use App\Services\VehicleColorService;
use App\Transformers\VehicleColorTransformer;
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

class VehicleColorController extends Controller
{
    /**
     * @var VehicleColorService
     */
    protected VehicleColorService $vehicleColorService;
    /**
     * @var VehicleColorTransformer
     */
    protected VehicleColorTransformer $transformer;

    public function __construct(Manager $fractal, VehicleColorService $vehicleColorService, VehicleColorTransformer $vehicleColorTransformer)
    {
        $this->vehicleColorService = $vehicleColorService;
        $this->transformer = $vehicleColorTransformer;
        parent::__construct($fractal);
    }

    /**
     * @OA\Get (
     *     path="/vehicle-colors",
     *     operationId="VehicleColorsList",
     *     tags={"VehicleColor"},
     *     summary="List vehicle color",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\Parameter(
     *         name="code",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="vehicle_type",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="name",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="ecn_in",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="ecn_out",
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
        $vehicleColors = $this->vehicleColorService->paginate();
        return $this->responseWithTransformer($vehicleColors, $this->transformer);
    }

    /**
     * @OA\Post  (
     *     path="/vehicle-colors",
     *     operationId="VehicleColorsCreate",
     *     tags={"VehicleColor"},
     *     summary="Creeat a vehicle color",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CreateVehicleColorRequest")
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
     * @param CreateVehicleColorRequest $request
     * @return Response
     * @throws ArrayWithMixedKeysException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws InvalidTypeException
     * @throws MissingConfigurationKeyException
     * @throws NotIntegerException
     * @throws Exception
     */
    public function store(CreateVehicleColorRequest $request): Response
    {
        $attributes = $request->only([
            'code',
			'type',
			'name',
			'ecn_in',
			'ecn_out',
			'plant_code'
        ]);
        $vehicleColor = $this->vehicleColorService->store($attributes);
        return $this->responseWithTransformer($vehicleColor, $this->transformer);
    }

    /**
     * @OA\Get   (
     *     path="/vehicle-colors/codes",
     *     operationId="VehicleColorListCode",
     *     tags={"VehicleColor"},
     *     summary="Get list vehicle color codes",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\Parameter(
     *         name="code",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="ecn_in",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="ecn_out",
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
        $codes = $this->vehicleColorService->searchCode();
        return $this->respond($codes);
    }

    /**
     * @OA\Get   (
     *     path="/vehicle-colors/columns",
     *     operationId="VehicleColorColumns",
     *     tags={"VehicleColor"},
     *     summary="Get column of vehicle color",
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
        $codes = $this->vehicleColorService->getColumnValue();
        return $this->respond($codes);
    }

    /**
     * @OA\Get (
     *     path="/vehicle-colors/export",
     *     operationId="VehicleColorExport",
     *     tags={"VehicleColor"},
     *     summary="Export Vehicle Color",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\Parameter(
     *         name="code",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="vehicle_type",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="name",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="ecn_in",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="ecn_out",
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
        return $this->vehicleColorService->export($request, VehicleColorExport::class, 'vehicle-color-master');
    }

    /**
     * @OA\Get   (
     *     path="/vehicle-colors/{id}",
     *     operationId="VehicleColorsShow",
     *     tags={"VehicleColor"},
     *     summary="Get a vehicle color detail",
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
        $vehicleColor = $this->vehicleColorService->show($id);
        return $this->responseWithTransformer($vehicleColor, $this->transformer);
    }

    /**
     * @OA\Put (
     *     path="/vehicle-colors/{id}",
     *     operationId="VehicleColorsUpdate",
     *     tags={"VehicleColor"},
     *     summary="Update a vehicle color",
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
     *         @OA\JsonContent(ref="#/components/schemas/UpdateVehicleColorRequest")
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
     * @param UpdateVehicleColorRequest $request
     * @return Response
     * @throws ArrayWithMixedKeysException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws InvalidTypeException
     * @throws MissingConfigurationKeyException
     * @throws NotIntegerException
     * @throws Exception
     */
    public function update(int $id, UpdateVehicleColorRequest $request): Response
    {
        $attributes = $request->only([
			'type',
			'name',
			'ecn_out'
        ]);
        $vehicleColor = $this->vehicleColorService->update($id, $attributes);
        return $this->responseWithTransformer($vehicleColor, $this->transformer);
    }

    /**
     * @OA\Delete (
     *     path="/vehicle-colors/{id}",
     *     operationId="VehicleColorsDelete",
     *     tags={"VehicleColor"},
     *     summary="Delete a vehicle color",
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
        $result = $this->vehicleColorService->destroy($id);
        if ($result) return $this->respond(); else return $this->respondWithError( 200, 400, 'Item used! Can not delete!');
    }
}
