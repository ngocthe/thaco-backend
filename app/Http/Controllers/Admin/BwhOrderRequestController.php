<?php

namespace App\Http\Controllers\Admin;

use App\Exports\BwhOrderRequestExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BwhOrderRequest\CreateBwhOrderRequestRequest;
use App\Http\Requests\Admin\BwhOrderRequest\ConfirmBwhOrderRequestRequest;
use App\Services\BwhOrderRequestService;
use App\Transformers\BwhOrderRequestTransformer;
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

class BwhOrderRequestController extends Controller
{
    /**
     * @var BwhOrderRequestService
     */
    protected BwhOrderRequestService $bwhOrderRequestService;
    /**
     * @var BwhOrderRequestTransformer
     */
    protected BwhOrderRequestTransformer $transformer;

    public function __construct(
        Manager $fractal,
        BwhOrderRequestService $bwhOrderRequestService,
        BwhOrderRequestTransformer $bwhOrderRequestTransformer
    ) {
        $this->bwhOrderRequestService = $bwhOrderRequestService;
        $this->transformer = $bwhOrderRequestTransformer;
        parent::__construct($fractal);
    }

    /**
     * @OA\Get (
     *     path="/bwh-order-requests",
     *     operationId="BwhOrderRequestList",
     *     tags={"BwhOrderRequest"},
     *     summary="List box type",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\Parameter(
     *         name="code",
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
        $bwhOrderRequests = $this->bwhOrderRequestService->paginate();
        return $this->responseWithTransformer($bwhOrderRequests, $this->transformer);
    }

    /**
     * @OA\Post  (
     *     path="/bwh-order-requests",
     *     operationId="BwhOrderRequestCreate",
     *     tags={"BwhOrderRequest"},
     *     summary="Creeat a box type",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CreateBwhOrderRequestRequest")
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
     * @param CreateBwhOrderRequestRequest $request
     * @return Response
     * @throws ArrayWithMixedKeysException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws InvalidTypeException
     * @throws MissingConfigurationKeyException
     * @throws NotIntegerException
     * @throws Exception
     */
    public function store(CreateBwhOrderRequestRequest $request): Response
    {
        $attributes = $request->only([
            'part_code',
            'part_color_code',
            'box_quantity',
            'plant_code'
        ]);
        $rsl = $this->bwhOrderRequestService->store($attributes);
        if ($rsl) {
            return $this->respond();
        } else {
            return $this->respondWithError(200, 400, "Couldn't find the right data to create order request");
        }
    }

    /**
     * @OA\Get   (
     *     path="/bwh-order-requests/columns",
     *     operationId="BwhOrderRequestColumns",
     *     tags={"BwhOrderRequest"},
     *     summary="Get column of BwhOrderRequest",
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
        $codes = $this->bwhOrderRequestService->getColumnValue();
        return $this->respond($codes);
    }

    /**
     * @OA\Get   (
     *     path="/bwh-order-requests/export",
     *     operationId="BwhOrderRequestExport",
     *     tags={"BwhOrderRequest"},
     *     summary="Export BwhOrderRequest",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\Parameter(
     *         name="code",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="page_number",
     *         in="query",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="line_number",
     *         in="query",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="released_party",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="released_date",
     *         in="query",
     *     ),
     *     @OA\Parameter(
     *         name="planned_line_off_date",
     *         in="query",
     *         description="format: yyyy-mm-dd"
     *     ),
     *     @OA\Parameter(
     *         name="actual_line_off_date",
     *         in="query",
     *         description="format: yyyy-mm-dd"
     *     ),
     *     @OA\Parameter(
     *         name="planned_packing_date",
     *         in="query",
     *         description="format: yyyy-mm-dd"
     *     ),
     *     @OA\Parameter(
     *         name="actual_packing_date",
     *         in="query",
     *         description="format: yyyy-mm-dd"
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
        return $this->bwhOrderRequestService->export($request, BwhOrderRequestExport::class,
            'bonded-warehouse-order-request');
    }

    /**
     * @OA\Get   (
     *     path="/bwh-order-requests/{id}",
     *     operationId="BwhOrderRequestShow",
     *     tags={"BwhOrderRequest"},
     *     summary="Get a BwhOrderRequest detail",
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
        $bwhOrderRequest = $this->bwhOrderRequestService->show($id);
        return $this->responseWithTransformer($bwhOrderRequest, $this->transformer);
    }

    /**
     * @OA\Post (
     *     path="/bwh-order-requests/{id}/confirm",
     *     operationId="BwhOrderRequestConfirm",
     *     tags={"BwhOrderRequest"},
     *     summary="Confirm a BwhOrderRequest",
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
     *         @OA\JsonContent(ref="#/components/schemas/ConfirmBwhOrderRequestRequest")
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
     * @param ConfirmBwhOrderRequestRequest $request
     * @return Response
     * @throws ArrayWithMixedKeysException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws InvalidTypeException
     * @throws MissingConfigurationKeyException
     * @throws NotIntegerException
     */
    public function confirm(int $id, ConfirmBwhOrderRequestRequest $request): Response
    {
        $data = $request->only([
            'received_date',
            'warehouse_code',
            'shelf_location_code',
            'defect_id',
            'remark'
        ]);
        list($bwhOrderRequest, $message) = $this->bwhOrderRequestService->confirmBwhOrderRequest($id, $data);
        if ($message) {
            return $this->respondWithError(200, 400, $message);
        } else {
            return $this->responseWithTransformer($bwhOrderRequest, $this->transformer);
        }
    }

    /**
     * @throws InvalidTypeException
     * @throws NotIntegerException
     * @throws ArrayWithMixedKeysException
     * @throws MissingConfigurationKeyException
     * @throws IncompatibleTypeException
     * @throws ConfigurationNotFoundException
     */
    public function requestOrders(Request $request): Response
    {
        $bwhOrderRequests = $this->bwhOrderRequestService->listOrderRequests($request);
        return $this->responseWithTransformer($bwhOrderRequests, $this->transformer);
    }
}
