<?php

namespace App\Http\Controllers\Admin;

use App\Exports\PartGroupExport;
use App\Exports\SupplierExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Supplier\CreateSupplierRequest;
use App\Http\Requests\Admin\Supplier\UpdateSupplierRequest;
use App\Services\SupplierService;
use App\Transformers\SupplierTransformer;
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

class SupplierController extends Controller
{
    /**
     * @var SupplierService
     */
    protected SupplierService $supplierService;
    /**
     * @var SupplierTransformer
     */
    protected SupplierTransformer $transformer;

    public function __construct(Manager $fractal, SupplierService $supplierService, SupplierTransformer $supplierTransformer)
    {
        $this->supplierService = $supplierService;
        $this->transformer = $supplierTransformer;
        parent::__construct($fractal);
    }

    /**
     * @OA\Get (
     *     path="/suppliers",
     *     operationId="SupplierList",
     *     tags={"Supplier"},
     *     summary="List supplier",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\Parameter(
     *         name="code",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="description",
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
        $suppliers = $this->supplierService->paginate();
        return $this->responseWithTransformer($suppliers, $this->transformer);
    }

    /**
     * @OA\Post  (
     *     path="/suppliers",
     *     operationId="SupplierCreate",
     *     tags={"Supplier"},
     *     summary="Creeat a supplier",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CreateSupplierRequest")
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
     * @param CreateSupplierRequest $request
     * @return Response
     * @throws ArrayWithMixedKeysException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws InvalidTypeException
     * @throws MissingConfigurationKeyException
     * @throws NotIntegerException
     * @throws Exception
     */
    public function store(CreateSupplierRequest $request): Response
    {
        $attributes = $request->only([
            'code',
			'description',
			'address',
			'phone',
			'forecast_by_week',
			'forecast_by_month',
            'receiver',
            'bcc',
            'cc'
        ]);
        $supplier = $this->supplierService->store($attributes);
        return $this->responseWithTransformer($supplier, $this->transformer);
    }

    /**
     * @OA\Get   (
     *     path="/suppliers/codes",
     *     operationId="SupplierListCode",
     *     tags={"Supplier"},
     *     summary="Get list supplier codes",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\Parameter(
     *         name="code",
     *         in="query",
     *         @OA\Schema(type="string")
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
        $codes = $this->supplierService->searchCode();
        return $this->respond($codes);
    }

    /**
     * @OA\Get (
     *     path="/suppliers/export",
     *     operationId="SupplierExport",
     *     tags={"Supplier"},
     *     summary="Export supplier",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\Parameter(
     *         name="code",
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
        return $this->supplierService->export($request, SupplierExport::class, 'procurement-supplier-master');
    }

    /**
     * @OA\Get   (
     *     path="/suppliers/{id}",
     *     operationId="SupplierShow",
     *     tags={"Supplier"},
     *     summary="Get a supplier detail",
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
        $supplier = $this->supplierService->show($id);
        return $this->responseWithTransformer($supplier, $this->transformer);
    }

    /**
     * @OA\Put (
     *     path="/suppliers/{id}",
     *     operationId="SupplierUpdate",
     *     tags={"Supplier"},
     *     summary="Update a supplier",
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
     *         @OA\JsonContent(ref="#/components/schemas/UpdateSupplierRequest")
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
     * @param UpdateSupplierRequest $request
     * @return Response
     * @throws ArrayWithMixedKeysException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws InvalidTypeException
     * @throws MissingConfigurationKeyException
     * @throws NotIntegerException
     * @throws Exception
     */
    public function update(int $id, UpdateSupplierRequest $request): Response
    {
        $attributes = $request->only([
			'description',
			'address',
			'phone',
			'forecast_by_week',
			'forecast_by_month',
            'receiver',
            'bcc',
            'cc'
        ]);
        $supplier = $this->supplierService->update($id, $attributes);
        return $this->responseWithTransformer($supplier, $this->transformer);
    }

    /**
     * @OA\Delete (
     *     path="/suppliers/{id}",
     *     operationId="SupplierDelete",
     *     tags={"Supplier"},
     *     summary="Delete a supplier",
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
        $result = $this->supplierService->destroy($id);
        if ($result) return $this->respond(); else return $this->respondWithError( 200, 400, 'Item used! Can not delete!');
    }
}
