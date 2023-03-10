<?php

namespace App\Http\Controllers\Admin;

use App\Constants\ValidationMessages;
use App\Exports\ProcurementExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Procurement\CreateProcurementRequest;
use App\Http\Requests\Admin\Procurement\UpdateProcurementRequest;
use App\Services\ProcurementService;
use App\Transformers\ProcurementTransformer;
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

class ProcurementController extends Controller
{
    /**
     * @var ProcurementService
     */
    protected ProcurementService $procurementService;
    /**
     * @var ProcurementTransformer
     */
    protected ProcurementTransformer $transformer;

    public function __construct(Manager $fractal, ProcurementService $procurementService, ProcurementTransformer $procurementTransformer)
    {
        $this->procurementService = $procurementService;
        $this->transformer = $procurementTransformer;
        parent::__construct($fractal);
    }

    /**
     * @OA\Get (
     *     path="/procurements",
     *     operationId="ProcurementList",
     *     tags={"Procurement"},
     *     summary="List procurement",
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
     *         name="supplier_code",
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
        $procurements = $this->procurementService->paginate();
        return $this->responseWithTransformer($procurements, $this->transformer);
    }

    /**
     * @OA\Post  (
     *     path="/procurements",
     *     operationId="ProcurementCreate",
     *     tags={"Procurement"},
     *     summary="Creeat a procurement",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CreateProcurementRequest")
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
     * @param CreateProcurementRequest $request
     * @return Response
     * @throws ArrayWithMixedKeysException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws InvalidTypeException
     * @throws MissingConfigurationKeyException
     * @throws NotIntegerException
     * @throws Exception
     */
    public function store(CreateProcurementRequest $request): Response
    {
        $attributes = $request->only([
            'part_code',
			'part_color_code',
			'minimum_order_quantity',
			'standard_box_quantity',
			'part_quantity',
			'unit',
			'supplier_code',
			'plant_code'
        ]);
        $procurement = $this->procurementService->store($attributes);
        return $this->responseWithTransformer($procurement, $this->transformer);
    }

    /**
     * @OA\Get   (
     *     path="/procurements/columns",
     *     operationId="ProcurementColumns",
     *     tags={"Procurement"},
     *     summary="Get column of procurements",
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
        $codes = $this->procurementService->getColumnValue();
        return $this->respond($codes);
    }

    /**
     * @OA\Get (
     *     path="/procurements/export",
     *     operationId="ProcurementExport",
     *     tags={"Procurement"},
     *     summary="Export procurement",
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
     *         name="supplier_code",
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
        return $this->procurementService->export($request, ProcurementExport::class, 'part-procurement');
    }

    /**
     * @OA\Get   (
     *     path="/procurements/{id}",
     *     operationId="ProcurementShow",
     *     tags={"Procurement"},
     *     summary="Get a procurement detail",
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
        $procurement = $this->procurementService->show($id);
        return $this->responseWithTransformer($procurement, $this->transformer);
    }

    /**
     * @OA\Put (
     *     path="/procurements/{id}",
     *     operationId="ProcurementUpdate",
     *     tags={"Procurement"},
     *     summary="Update a procurement",
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
     *         @OA\JsonContent(ref="#/components/schemas/UpdateProcurementRequest")
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
     * @param UpdateProcurementRequest $request
     * @return Response
     * @throws ArrayWithMixedKeysException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws InvalidTypeException
     * @throws MissingConfigurationKeyException
     * @throws NotIntegerException
     * @throws Exception
     */
    public function update(int $id, UpdateProcurementRequest $request): Response
    {
        $attributes = $request->only([
			'minimum_order_quantity',
			'standard_box_quantity',
			'part_quantity',
			'unit',
			'supplier_code'
        ]);
        $procurement = $this->procurementService->update($id, $attributes);
        return $this->responseWithTransformer($procurement, $this->transformer);
    }

    /**
     * @OA\Delete (
     *     path="/procurements/{id}",
     *     operationId="ProcurementDelete",
     *     tags={"Procurement"},
     *     summary="Delete a procurement",
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
        $result = $this->procurementService->destroy($id);
        if ($result) return $this->respond(); else return $this->respondWithError( 200, 400, 'Item used! Can not delete!');
    }
}
