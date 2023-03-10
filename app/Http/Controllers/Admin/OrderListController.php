<?php

namespace App\Http\Controllers\Admin;

use App\Exports\DeliveringListPdfExport;
use App\Exports\DeliveringListsExport;
use App\Exports\OrderListPdfExport;
use App\Exports\OrderListsExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OrderList\CreateOrderListRequest;
use App\Http\Requests\Admin\OrderList\OrderListSystemRunRequest;
use App\Http\Requests\Admin\OrderList\ReleaseOrderListRequest;
use App\Http\Requests\Admin\OrderList\UpdateOrderListRequest;
use App\Services\MrpProductionPlanImportService;
use App\Services\OrderListService;
use App\Transformers\OrderListTransformer;
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

class OrderListController extends Controller
{
    /**
     * @var OrderListService
     */
    protected OrderListService $orderListService;
    /**
     * @var OrderListTransformer
     */
    protected OrderListTransformer $transformer;

    public function __construct(Manager $fractal, OrderListService $orderListService, OrderListTransformer $orderListTransformer)
    {
        $this->orderListService = $orderListService;
        $this->transformer = $orderListTransformer;
        parent::__construct($fractal);
    }

    /**
     * @OA\Get (
     *     path="/order-list",
     *     operationId="OrderListList",
     *     tags={"OrderList"},
     *     summary="List order list",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\Parameter(
     *         name="contract_code",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="part_group",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="supplier_code",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="plant_code",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="import_id",
     *         in="query",
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="eta",
     *         in="query",
     *         description="format: yyyy-mm-dd"
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
     * @param MrpProductionPlanImportService $mrpProductionPlanImportService
     * @return Response
     * @throws ArrayWithMixedKeysException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws InvalidTypeException
     * @throws MissingConfigurationKeyException
     * @throws NotIntegerException
     */
    public function index(MrpProductionPlanImportService $mrpProductionPlanImportService): Response
    {
        $orderLists = $this->orderListService->paginateWithDefaultAttribute();
        return $this->responseWithTransformer(
            $orderLists,
            $this->transformer,
            null,
            [
                'import_file' => $this->orderListService->currentFilterImport,
                'running_file' => $mrpProductionPlanImportService->getFileRunningOrder()
            ]
        );
    }

    /**
     * @OA\Get (
     *     path="/order-list/delivering",
     *     operationId="OrderListListDelivering",
     *     tags={"OrderList"},
     *     summary="List order list",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\Parameter(
     *         name="contract_code",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="part_group",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="supplier_code",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="plant_code",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="import_id",
     *         in="query",
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="eta",
     *         in="query",
     *         description="format: yyyy-mm-dd"
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
    public function delivering(): Response
    {
        $orderLists = $this->orderListService->paginateWithDeliveringStatus();
        return $this->responseWithTransformer(
            $orderLists,
            $this->transformer,
            null,
            [
                'import_file' => $this->orderListService->currentFilterImport
            ]
        );
    }

    /**
     * @OA\Post  (
     *     path="/order-list",
     *     operationId="OrderListCreate",
     *     tags={"OrderList"},
     *     summary="Creeat a order list",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CreateOrderListRequest")
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
     * @param CreateOrderListRequest $request
     * @return Response
     * @throws ArrayWithMixedKeysException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws InvalidTypeException
     * @throws MissingConfigurationKeyException
     * @throws NotIntegerException
     * @throws Exception
     */
    public function store(CreateOrderListRequest $request): Response
    {
        $attributes = $request->only([
            'contract_code',
            'part_code',
            'part_color_code',
            'part_group',
            'actual_quantity',
            'supplier_code',
            'import_id',
            'moq',
            'mrp_quantity',
            'plant_code',
            'remark'
        ]);

        $attributes = $this->orderListService->mergeDataStore($attributes);
        $isValidEta = $this->orderListService->validateGroupKeyWithEtaUnique($attributes);
        if ($isValidEta) {
            $orderList = $this->orderListService->store($attributes);
            return $this->responseWithTransformer($orderList, $this->transformer);
        } else {
            $message = "The Contract No., Part No., ETA, Part Color Code has already been taken.";
            return $this->respondWithError(200, 400, $message, ["data" => [
                "contract_code" => [
                    "code" => 10096,
                    "message" => $message
                ]
            ]]);
        }
    }

    /**
     * @OA\Get   (
     *     path="/order-list/columns",
     *     operationId="OrderListColumns",
     *     tags={"OrderList"},
     *     summary="Get column of order list",
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
        $codes = $this->orderListService->getColumnValue();
        return $this->respond($codes);
    }

    /**
     * @OA\Get   (
     *     path="/order-list/export",
     *     operationId="OrderListExport",
     *     tags={"OrderList"},
     *     summary="Export order list",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\Parameter(
     *         name="contract_code",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="part_group",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="supplier_code",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="plant_code",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="import_id",
     *         in="query",
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="eta",
     *         in="query",
     *         description="format: yyyy-mm-dd"
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
        $type = $request->get('type');
        if ($type == 'pdf') {
            return $this->orderListService->export($request, OrderListPdfExport::class, 'order-list');
        } else {
            return $this->orderListService->export($request, OrderListsExport::class, 'order-list');
        }
    }

    /**
     * @OA\Get   (
     *     path="/order-list/export-delivering",
     *     operationId="OrderListExportDelivering",
     *     tags={"OrderList"},
     *     summary="Export order delivering list",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\Parameter(
     *         name="contract_code",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="part_group",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="supplier_code",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="plant_code",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="import_id",
     *         in="query",
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="eta",
     *         in="query",
     *         description="format: yyyy-mm-dd"
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
    public function exportDelivering(Request $request): BinaryFileResponse
    {
        $type = $request->get('type');
        if ($type == 'pdf') {
            return $this->orderListService->export($request, DeliveringListPdfExport::class, 'order-list');
        } else {
            return $this->orderListService->export($request, DeliveringListsExport::class, 'order-list');
        }
    }

    /**
     * @OA\Get   (
     *     path="/order-list/{id}",
     *     operationId="OrderListShow",
     *     tags={"OrderList"},
     *     summary="Get a ecn detail",
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
        $orderList = $this->orderListService->show($id);
        return $this->responseWithTransformer($orderList, $this->transformer);
    }

    /**
     * @OA\Put (
     *     path="/order-list/{id}",
     *     operationId="OrderListUpdate",
     *     tags={"OrderList"},
     *     summary="Update a order list",
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
     *         @OA\JsonContent(ref="#/components/schemas/UpdateOrderListRequest")
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
     * @param UpdateOrderListRequest $request
     * @return Response
     * @throws ArrayWithMixedKeysException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws InvalidTypeException
     * @throws MissingConfigurationKeyException
     * @throws NotIntegerException
     * @throws Exception
     */
    public function update(int $id, UpdateOrderListRequest $request): Response
    {
        $attributes = $request->only([
            'actual_quantity',
            'remark'
        ]);
        $orderList = $this->orderListService->update($id, $attributes);
        return $this->responseWithTransformer($orderList, $this->transformer);
    }

    /**
     * @OA\Delete (
     *     path="/order-list/{id}",
     *     operationId="OrderListDelete",
     *     tags={"OrderList"},
     *     summary="Delete a order list",
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
        $this->orderListService->destroy($id);
        return $this->respond();
    }

    /**
     *
     * @OA\Put  (
     *     path="/order-list/release",
     *     operationId="OrderListRelease",
     *     tags={"OrderList"},
     *     summary="Creeat a order list",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\Parameter(
     *         in="path",
     *         name="cotract_code",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         in="path",
     *         name="supplier_code",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         in="path",
     *         name="plant_code",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         in="path",
     *         name="part_group",
     *         required=false,
     *         @OA\Schema(type="string")
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
     * @param ReleaseOrderListRequest $request
     * @return Response
     * @throws ArrayWithMixedKeysException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws InvalidTypeException
     * @throws MissingConfigurationKeyException
     * @throws NotIntegerException
     */
    public function release(ReleaseOrderListRequest $request): Response
    {
        $this->orderListService->release($request->toArray());
        return $this->respond();
    }

    /**
     * @OA\Post  (
     *     path="/order-list/check-shortage-part",
     *     operationId="OrderListCheckShortagePart",
     *     tags={"OrderList"},
     *     summary="Check shortage part before System Run",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/OrderListSystemRunRequest")
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
     * @param OrderListSystemRunRequest $request
     * @return Response
     * @throws ArrayWithMixedKeysException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws InvalidTypeException
     * @throws MissingConfigurationKeyException
     * @throws NotIntegerException
     */
    public function checkShortagePart(OrderListSystemRunRequest $request): Response
    {
        $total = $this->orderListService->checkShortagePart($request->get('import_id'), $request->get('contract_code'), $request->get('part_group'));
        return $this->respond([
            'total_shortage_part' => $total
        ]);
    }

    /**
     * @OA\Post  (
     *     path="/order-list/system-run",
     *     operationId="OrderListSystemRun",
     *     tags={"OrderList"},
     *     summary="System Run",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/OrderListSystemRunRequest")
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
     * @param OrderListSystemRunRequest $request
     * @return Response
     * @throws ArrayWithMixedKeysException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws InvalidTypeException
     * @throws MissingConfigurationKeyException
     * @throws NotIntegerException
     */
    public function systemRun(OrderListSystemRunRequest $request): Response
    {
        list($rsl, $msg) = $this->orderListService->orderRun(
            $request->get('import_id'), $request->get('contract_code'),
            $request->get('part_group'), $request->get('mrp_run_date')
        );
        if ($rsl) {
            return $this->respond();
        } else {
            return $this->respondWithError(200, 400, $msg);
        }
    }
}
