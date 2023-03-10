<?php

namespace App\Http\Controllers\Admin;

use App\Constants\MRP;
use App\Exports\MRPOrderingCalendarExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MrpOrderCalendar\CreateMrpOrderCalendarRequest;
use App\Http\Requests\Admin\MrpOrderCalendar\UpdateMrpOrderCalendarRequest;
use App\Services\MrpOrderCalendarService;
use App\Transformers\MrpOrderCalendarTransformer;
use Exception;
use Illuminate\Support\Facades\DB;
use League\Fractal\Manager;
use MarcinOrlowski\ResponseBuilder\Exceptions\ArrayWithMixedKeysException;
use MarcinOrlowski\ResponseBuilder\Exceptions\ConfigurationNotFoundException;
use MarcinOrlowski\ResponseBuilder\Exceptions\IncompatibleTypeException;
use MarcinOrlowski\ResponseBuilder\Exceptions\InvalidTypeException;
use MarcinOrlowski\ResponseBuilder\Exceptions\MissingConfigurationKeyException;
use MarcinOrlowski\ResponseBuilder\Exceptions\NotIntegerException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;

class MrpOrderCalendarController extends Controller
{
    /**
     * @var MrpOrderCalendarService
     */
    protected MrpOrderCalendarService $mrpOrderCalendarService;
    /**
     * @var MrpOrderCalendarTransformer
     */
    protected MrpOrderCalendarTransformer $transformer;

    public function __construct(Manager $fractal, MrpOrderCalendarService $mrpOrderCalendarService, MrpOrderCalendarTransformer $mrpOrderCalendarTransformer)
    {
        $this->mrpOrderCalendarService = $mrpOrderCalendarService;
        $this->transformer = $mrpOrderCalendarTransformer;
        parent::__construct($fractal);
    }

    /**
     * @OA\Get (
     *     path="/mrp-order-calendars",
     *     operationId="MrprOderCalendarList",
     *     tags={"MrprOderCalendar"},
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
     *         name="status",
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
        $mrpOrderCalendars = $this->mrpOrderCalendarService->paginate();
        return $this->responseWithTransformer($mrpOrderCalendars, $this->transformer);
    }

    /**
     * @OA\Post  (
     *     path="/mrp-order-calendars",
     *     operationId="MrprOderCalendarCreate",
     *     tags={"MrprOderCalendar"},
     *     summary="Creeat a order list",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CreateMrpOrderCalendarRequest")
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
     * @param CreateMrpOrderCalendarRequest $request
     * @return Response
     * @throws ArrayWithMixedKeysException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws InvalidTypeException
     * @throws MissingConfigurationKeyException
     * @throws NotIntegerException
     * @throws Exception
     */
    public function store(CreateMrpOrderCalendarRequest $request): Response
    {
        $attributes = $request->only([
            'contract_code',
			'part_group',
			'etd',
			'eta',
			'target_plan_from',
			'target_plan_to',
			'buffer_span_from',
			'buffer_span_to',
			'order_span_from',
			'order_span_to',
			'mrp_or_run',
            'remark'
        ]);

        $payload = $this->mrpOrderCalendarService->convertPayload($attributes);
        $mrpOrderCalendar = $this->mrpOrderCalendarService->store($payload);
        return $this->responseWithTransformer($mrpOrderCalendar, $this->transformer);
    }

    /**
     * @OA\Get   (
     *     path="/mrp-order-calendars/columns",
     *     operationId="MrprOderCalendarColumns",
     *     tags={"MrprOderCalendar"},
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
        $codes = $this->mrpOrderCalendarService->getColumnValue();
        return $this->respond($codes);
    }

    /**
     * @OA\Get   (
     *     path="/mrp-order-calendars/export",
     *     operationId="MrprOderCalendarExport",
     *     tags={"MrprOderCalendar"},
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
     *         name="status",
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
        return $this->mrpOrderCalendarService->export($request, MRPOrderingCalendarExport::class, 'mrp-ordering-calendar');
    }

    /**
     * @OA\Get   (
     *     path="/mrp-order-calendars/{id}",
     *     operationId="MrprOderCalendarShow",
     *     tags={"MrprOderCalendar"},
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
        $mrpOrderCalendar = $this->mrpOrderCalendarService->show($id);
        return $this->responseWithTransformer($mrpOrderCalendar, $this->transformer);
    }

    /**
     * @OA\Put (
     *     path="/mrp-order-calendars/{id}",
     *     operationId="MrprOderCalendarUpdate",
     *     tags={"MrprOderCalendar"},
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
     *         @OA\JsonContent(ref="#/components/schemas/UpdateMrpOrderCalendarRequest")
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
     * @param UpdateMrpOrderCalendarRequest $request
     * @return Response
     * @throws ArrayWithMixedKeysException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws InvalidTypeException
     * @throws MissingConfigurationKeyException
     * @throws NotIntegerException
     * @throws Exception
     */
    public function update(int $id, UpdateMrpOrderCalendarRequest $request): Response
    {
        $attributes = $request->only([
			'etd',
			'eta',
			'target_plan_from',
			'target_plan_to',
			'buffer_span_from',
			'buffer_span_to',
			'order_span_from',
			'order_span_to',
			'mrp_or_run',
            'remark'
        ]);

        $payload = $attributes;

        $mrpOrderCalendar = $this->mrpOrderCalendarService->findById($id);

        if (!$mrpOrderCalendar) {
            abort(404);
        }

        $validateResult = $this->mrpOrderCalendarService->validateETA($mrpOrderCalendar, $payload);

        if($validateResult) {
            return $validateResult;
        }

        try {
            DB::beginTransaction();
            if ($mrpOrderCalendar->status === MRP::MRP_ORDER_CALENDAR_STATUS_WAIT) {
                $payload = $this->mrpOrderCalendarService->convertPayload($attributes);
                $this->mrpOrderCalendarService->updateEtaOrders($id, $payload);
                $mrpOrderCalendar = $this->mrpOrderCalendarService->update($id, $payload);
            } elseif ($mrpOrderCalendar->status === MRP::MRP_ORDER_CALENDAR_STATUS_DONE) {
                $mrpOrderCalendar = $this->mrpOrderCalendarService->updateEtaWhenStatusDone($id, $payload);
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            abort(404);
        }

        return $this->responseWithTransformer($mrpOrderCalendar, $this->transformer);
    }

    /**
     * @OA\Delete (
     *     path="/mrp-order-calendars/{id}",
     *     operationId="MrprOderCalendarDelete",
     *     tags={"MrprOderCalendar"},
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
        $this->mrpOrderCalendarService->destroy($id);
        return $this->respond();
    }
}
