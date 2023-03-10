<?php

namespace App\Http\Controllers\Admin;

use App\Exports\OrderPointControlExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OrderPointControl\CreateOrderPointControlRequest;
use App\Http\Requests\Admin\OrderPointControl\UpdateOrderPointControlRequest;
use App\Services\OrderPointControlService;
use App\Transformers\OrderPointControlTransformer;
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

class OrderPointControlController extends Controller
{
    /**
     * @var OrderPointControlService
     */
    protected OrderPointControlService $orderPointControlService;
    /**
     * @var OrderPointControlTransformer
     */
    protected OrderPointControlTransformer $transformer;

    public function __construct(Manager $fractal, OrderPointControlService $OrderPointControlService, OrderPointControlTransformer $OrderPointControlTransformer)
    {
        $this->orderPointControlService = $OrderPointControlService;
        $this->transformer = $OrderPointControlTransformer;
        parent::__construct($fractal);
    }

    /**
     * @OA\Get (
     *     path="/order-point-controls",
     *     operationId="OrderPointControlList",
     *     tags={"OrderPointControl"},
     *     summary="List order point control",
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
        $OrderPointControls = $this->orderPointControlService->paginate();
        return $this->responseWithTransformer($OrderPointControls, $this->transformer);
    }

    /**
     * @OA\Post  (
     *     path="/order-point-controls",
     *     operationId="OrderPointControlCreate",
     *     tags={"OrderPointControl"},
     *     summary="Creeat a order point control",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CreateOrderPointControlRequest")
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
     * @param CreateOrderPointControlRequest $request
     * @return Response
     * @throws ArrayWithMixedKeysException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws InvalidTypeException
     * @throws MissingConfigurationKeyException
     * @throws NotIntegerException
     * @throws Exception
     */
    public function store(CreateOrderPointControlRequest $request): Response
    {
        $attributes = $request->only([
            'part_code',
			'part_color_code',
            'box_type_code',
			'standard_stock',
            'ordering_lot',
			'plant_code'
        ]);
        $OrderPointControl = $this->orderPointControlService->store($attributes);
        return $this->responseWithTransformer($OrderPointControl, $this->transformer);
    }

    /**
     * @OA\Get   (
     *     path="/order-point-controls/export",
     *     operationId="OrderPointControlExport",
     *     tags={"OrderPointControl"},
     *     summary="Export order point control",
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
        return $this->orderPointControlService->export($request, OrderPointControlExport::class, 'unpack-warehouse-order-control');
    }

    /**
     * @OA\Get   (
     *     path="/order-point-controls/{id}",
     *     operationId="OrderPointControlShow",
     *     tags={"OrderPointControl"},
     *     summary="Get a OrderPointControl detail",
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
        $OrderPointControl = $this->orderPointControlService->show($id);
        return $this->responseWithTransformer($OrderPointControl, $this->transformer);
    }

    /**
     * @OA\Put (
     *     path="/order-point-controls/{id}",
     *     operationId="OrderPointControlUpdate",
     *     tags={"OrderPointControl"},
     *     summary="Update a order point control",
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
     *         @OA\JsonContent(ref="#/components/schemas/UpdateOrderPointControlRequest")
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
     * @param UpdateOrderPointControlRequest $request
     * @return Response
     * @throws ArrayWithMixedKeysException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws InvalidTypeException
     * @throws MissingConfigurationKeyException
     * @throws NotIntegerException
     * @throws Exception
     */
    public function update(int $id, UpdateOrderPointControlRequest $request): Response
    {
        $attributes = $request->only([
			'standard_stock',
            'ordering_lot',
            'box_type_code'
        ]);
        list($orderPoint, $msg) = $this->orderPointControlService->update($id, $attributes);
        if ($orderPoint) {
            return $this->responseWithTransformer($orderPoint, $this->transformer);
        } else {
            return $this->respondWithError(200, 400, $msg);
        }
    }

    /**
     * @OA\Delete (
     *     path="/order-point-controls/{id}",
     *     operationId="OrderPointControlDelete",
     *     tags={"OrderPointControl"},
     *     summary="Delete a order point control",
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
        $this->orderPointControlService->destroy($id);
        return $this->respond();
    }
}
