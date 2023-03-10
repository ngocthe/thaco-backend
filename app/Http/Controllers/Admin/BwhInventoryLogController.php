<?php

namespace App\Http\Controllers\Admin;

use App\Exports\BwhInventoryLogExport;
use App\Exports\BwhInventoryLogForUpkwhExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BwhInventoryLog\CreateBwhInventoryLogRequest;
use App\Http\Requests\Admin\BwhInventoryLog\ShippedBwhInventoryLogRequest;
use App\Http\Requests\Admin\BwhInventoryLog\UpdateBwhInventoryLogRequest;
use App\Services\BwhInventoryLogService;
use App\Transformers\BwhInventoryLogTransformer;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use League\Fractal\Manager;
use MarcinOrlowski\ResponseBuilder\Exceptions\ArrayWithMixedKeysException;
use MarcinOrlowski\ResponseBuilder\Exceptions\ConfigurationNotFoundException;
use MarcinOrlowski\ResponseBuilder\Exceptions\IncompatibleTypeException;
use MarcinOrlowski\ResponseBuilder\Exceptions\InvalidTypeException;
use MarcinOrlowski\ResponseBuilder\Exceptions\MissingConfigurationKeyException;
use MarcinOrlowski\ResponseBuilder\Exceptions\NotIntegerException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class BwhInventoryLogController extends Controller
{
    /**
     * @var BwhInventoryLogService
     */
    protected BwhInventoryLogService $bwhInventoryLogService;
    /**
     * @var BwhInventoryLogTransformer
     */
    protected BwhInventoryLogTransformer $transformer;

    public function __construct(Manager $fractal, BwhInventoryLogService $bwhInventoryLogService, BwhInventoryLogTransformer $bwhInventoryLogTransformer)
    {
        $this->bwhInventoryLogService = $bwhInventoryLogService;
        $this->transformer = $bwhInventoryLogTransformer;
        parent::__construct($fractal);
    }

    /**
     * @OA\Get (
     *     path="/bwh-inventory-logs",
     *     operationId="BWHInventoryLogList",
     *     tags={"BWHInventoryLog"},
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
     *         name="devanned_date",
     *         in="query",
     *         description="format: yyyy-mm-dd"
     *     ),
     *     @OA\Parameter(
     *         name="stored_date",
     *         in="query",
     *         description="format: yyyy-mm-dd"
     *     ),
     *     @OA\Parameter(
     *         name="warehouse_location_code",
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
     *         description="false: OK, true: Has Defect, null: ALl"
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
        $bwhInventoryLogs = $this->bwhInventoryLogService->paginate();
        return $this->responseWithTransformer($bwhInventoryLogs, $this->transformer);
    }

    /**
     * @OA\Get   (
     *     path="/bwh-inventory-logs/parts",
     *     operationId="PartCodeByBWH",
     *     tags={"BWHInventoryLog"},
     *     summary="Get list part codes",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\Parameter(
     *         name="code",
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
    public function parts(): Response
    {
        $codes = $this->bwhInventoryLogService->searchPart();
        return $this->respond($codes);
    }

    /**
     * @OA\Get   (
     *     path="/bwh-inventory-logs/cases",
     *     operationId="CaseCodeByBWH",
     *     tags={"BWHInventoryLog"},
     *     summary="Get list case codes",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\Parameter(
     *         name="code",
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
    public function cases(): Response
    {
        $codes = $this->bwhInventoryLogService->searchCase();
        return $this->respond($codes);
    }


    /**
     * @OA\Get   (
     *     path="/bwh-inventory-logs/part-colors",
     *     operationId="PartColorCodeByBWH",
     *     tags={"BWHInventoryLog"},
     *     summary="Get list part color codes",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\Parameter(
     *         name="code",
     *         in="query"
     *     ),
     *        @OA\Parameter(
     *         name="part_code",
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
    public function partColors(): Response
    {
        $codes = $this->bwhInventoryLogService->searchPartColor();
        return $this->respond($codes);
    }

    /**
     * @OA\Post  (
     *     path="/bwh-inventory-logs",
     *     operationId="BwhInventoryLogCreate",
     *     tags={"BWHInventoryLog"},
     *     summary="Create a bwh-inventory-logs",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CreateBwhInventoryLogRequest")
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
     * @param CreateBwhInventoryLogRequest $request
     * @return Response
     * @throws ArrayWithMixedKeysException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws InvalidTypeException
     * @throws MissingConfigurationKeyException
     * @throws NotIntegerException
     * @throws Exception
     */
    public function store(CreateBwhInventoryLogRequest $request): Response
    {
        $attributes = $request->only([
            'contract_code',
            'invoice_code',
            'bill_of_lading_code',
            'container_code',
            'case_code',
            'devanned_date',
            'container_received',
            'stored_date',
            'warehouse_location_code',
            'warehouse_code',
            'shipped_date',
            'defect_id',
            'plant_code'
        ]);
        try {
            $bwhInventoryLog = $this->bwhInventoryLogService->store($attributes);
            if ($bwhInventoryLog) {
                return $this->responseWithTransformer($bwhInventoryLog, $this->transformer);
            } else {
                $message = 'There is no corresponding data in the table in transit inventory';
                return $this->respondWithError(200, 400, $message, [
                    'code' => '',
                    'message' => $message
                ]);
            }
        } catch (ValidationException $exception) {
            $errors = $exception->errors();
            $message = $errors[0][0]->errors()[0];
            $fails = [];
            foreach ($errors as $error) {
                $error = $error[0];
                $fails[] = [
                    'attribute' => $error->attribute(),
                    'error' => $error->errors()[0]
                ];
            }
            return $this->respondWithError(200, 400, $message, $fails);
        }
    }

    /**
     * @OA\Get   (
     *     path="/bwh-inventory-logs/columns",
     *     operationId="BWHInventoryLogColumns",
     *     tags={"BWHInventoryLog"},
     *     summary="Get column of bwh-inventory-logs",
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
        $codes = $this->bwhInventoryLogService->getColumnValue();
        return $this->respond($codes);
    }

    /**
     * @OA\Get (
     *     path="/bwh-inventory-logs/export",
     *     operationId="BWHInventoryLogExport",
     *     tags={"BWHInventoryLog"},
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
     *         name="devanned_date",
     *         in="query",
     *         description="format: yyyy-mm-dd"
     *     ),
     *     @OA\Parameter(
     *         name="stored_date",
     *         in="query",
     *         description="format: yyyy-mm-dd"
     *     ),
     *     @OA\Parameter(
     *         name="warehouse_location_code",
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
     *         name="export_for_upkwh",
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
        $export_for_upkwh = $request->get('export_for_upkwh');
        if ($export_for_upkwh == 'true' || $export_for_upkwh == 1) {
            return $this->bwhInventoryLogService->export($request, BwhInventoryLogForUpkwhExport::class, 'unpack-warehouse-scanning-data');
        } else {
            return $this->bwhInventoryLogService->export($request, BwhInventoryLogExport::class, 'bonded-warehouse-inventory');
        }

    }

    /**
     * @OA\Get   (
     *     path="/bwh-inventory-logs/{id}",
     *     operationId="BWHInventoryLogShow",
     *     tags={"BWHInventoryLog"},
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
        $bwhInventoryLog = $this->bwhInventoryLogService->show($id);
        return $this->responseWithTransformer($bwhInventoryLog, $this->transformer);
    }

    /**
     * @OA\Put (
     *     path="/bwh-inventory-logs/{id}",
     *     operationId="BWHInventoryLogUpdate",
     *     tags={"BWHInventoryLog"},
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
     *         @OA\JsonContent(ref="#/components/schemas/UpdateBwhInventoryLogRequest")
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
     * @param UpdateBwhInventoryLogRequest $request
     * @return Response
     * @throws ArrayWithMixedKeysException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws InvalidTypeException
     * @throws MissingConfigurationKeyException
     * @throws NotIntegerException
     * @throws Exception
     */
    public function update(int $id, UpdateBwhInventoryLogRequest $request): Response
    {
        $attributes = $request->only([
            'container_received',
			'devanned_date',
			'stored_date',
			'warehouse_location_code',
            'warehouse_code',
			'shipped_date',
            'defect_id'
        ]);
        try {
            list($bwhInventoryLog, $message) = $this->bwhInventoryLogService->update($id, $attributes);
            if ($message) {
                return $this->respondWithError(200, 400, $message, [
                    'code' => '',
                    'message' => $message
                ]);
            } else {
                return $this->responseWithTransformer($bwhInventoryLog, $this->transformer);
            }
        } catch (ValidationException $exception) {
            $errors = $exception->errors();
            $message = $errors[0][0]->errors()[0];
            $fails = [];
            foreach ($errors as $error) {
                $error = $error[0];
                $fails[] = [
                    'attribute' => $error->attribute(),
                    'error' => $error->errors()[0]
                ];
            }
            return $this->respondWithError(200, 400, $message, $fails);
        }
    }

    /**
     * @OA\Delete (
     *     path="/bwh-inventory-logs/{id}",
     *     operationId="BWHInventoryLogDelete",
     *     tags={"BWHInventoryLog"},
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
        list($result, $msg) = $this->bwhInventoryLogService->destroy($id);
        if ($result) {
            return $this->respond();
        } else {
            return $this->respondWithError(200, 400, $msg);
        }
    }

    /**
     * @param ShippedBwhInventoryLogRequest $request
     * @return Response
     * @throws ArrayWithMixedKeysException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws InvalidTypeException
     * @throws MissingConfigurationKeyException
     * @throws NotIntegerException
     */
    public function shipped(ShippedBwhInventoryLogRequest $request): Response
    {
        $attributes = $request->only([
            'warehouse_code',
            'plant_code',
            'contract_code',
            'bill_of_lading_code',
            'container_code',
            'case_code',
            'invoice_code'
        ]);

        list($status, $message, $bwhInventoryLog) = $this->bwhInventoryLogService->shipped($attributes);
        if (!$status) {
            return $this->respondWithError(200, 400, $message);
        } else {
            return $this->responseWithTransformer($bwhInventoryLog, $this->transformer);
        }
    }

    /**
     * @param Request $request
     * @return Response
     * @throws ArrayWithMixedKeysException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws InvalidTypeException
     * @throws MissingConfigurationKeyException
     * @throws NotIntegerException
     */
    public function orderRequests(Request $request): Response
    {
        $bwhInventoryLogs = $this->bwhInventoryLogService->orderRequests($request);

        return $this->responseWithTransformer($bwhInventoryLogs, $this->transformer);
    }
}
