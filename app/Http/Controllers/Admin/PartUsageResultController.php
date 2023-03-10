<?php

namespace App\Http\Controllers\Admin;

use App\Exports\PartUsageResultExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PartUsageResult\CreatePartUsageResultRequest;
use App\Http\Requests\Admin\PartUsageResult\UpdatePartUsageResultRequest;
use App\Services\PartUsageResultService;
use App\Transformers\PartUsageResultTransformer;
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

class PartUsageResultController extends Controller
{
    /**
     * @var PartUsageResultService
     */
    protected PartUsageResultService $partUsageResultService;

    /**
     * @var PartUsageResultTransformer
     */
    protected PartUsageResultTransformer $transformer;

    public function __construct(Manager $fractal, PartUsageResultService $partUsageResultService, PartUsageResultTransformer $partUsageResultTransformer)
    {
        $this->partUsageResultService = $partUsageResultService;
        $this->transformer = $partUsageResultTransformer;
        parent::__construct($fractal);
    }

    /**
     * @OA\Get (
     *     path="/part-usage-results",
     *     operationId="PartUsageResultList",
     *     tags={"PartUsageResult"},
     *     summary="List part",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\Parameter(
     *         name="used_date",
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
        $partUsageResults = $this->partUsageResultService->paginate();
        return $this->responseWithTransformer($partUsageResults, $this->transformer);
    }

    /**
     * @OA\Post  (
     *     path="/part-usage-results",
     *     operationId="PartUsageResultCreate",
     *     tags={"PartUsageResult"},
     *     summary="Creeat a part",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CreatePartUsageResultRequest")
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
     * @param CreatePartUsageResultRequest $request
     * @return Response
     * @throws ArrayWithMixedKeysException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws InvalidTypeException
     * @throws MissingConfigurationKeyException
     * @throws NotIntegerException
     * @throws Exception
     */
    public function store(CreatePartUsageResultRequest $request): Response
    {
        $attributes = $request->only([
            'used_date',
            'part_code',
            'part_color_code',
            'plant_code',
            'quantity'
        ]);
        list($partUsageResult, $msg) = $this->partUsageResultService->store($attributes);
        if ($msg) {
            return $this->respondWithError( 200, 400, $msg);
        } else {
            return $this->responseWithTransformer($partUsageResult, $this->transformer);
        }
    }

    /**
     * @OA\Get   (
     *     path="/part-usage-results/columns",
     *     operationId="PartUsageResultColumns",
     *     tags={"PartUsageResult"},
     *     summary="Get column of part",
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
        $codes = $this->partUsageResultService->getColumnValue();
        return $this->respond($codes);
    }

    /**
     * @OA\Get (
     *     path="/part-usage-results/export",
     *     operationId="PartUsageResultExport",
     *     tags={"PartUsageResult"},
     *     summary="Export part",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\Parameter(
     *         name="used_date",
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
        return $this->partUsageResultService->export($request, PartUsageResultExport::class, 'parts-usage-result');
    }

    /**
     * @OA\Get   (
     *     path="/part-usage-results/{id}",
     *     operationId="PartUsageResultShow",
     *     tags={"PartUsageResult"},
     *     summary="Get a part detail",
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
        $partUsageResult = $this->partUsageResultService->show($id);
        return $this->responseWithTransformer($partUsageResult, $this->transformer);
    }

    /**
     * @OA\Put (
     *     path="/part-usage-results/{id}",
     *     operationId="PartUsageResultUpdate",
     *     tags={"PartUsageResult"},
     *     summary="Update a part",
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
     *         @OA\JsonContent(ref="#/components/schemas/UpdatePartUsageResultRequest")
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
     * @param UpdatePartUsageResultRequest $request
     * @return Response
     * @throws ArrayWithMixedKeysException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws InvalidTypeException
     * @throws MissingConfigurationKeyException
     * @throws NotIntegerException
     * @throws Exception
     */
    public function update(int $id, UpdatePartUsageResultRequest $request): Response
    {
        $attributes = $request->only([
            'quantity'
        ]);
        $partUsageResult = $this->partUsageResultService->update($id, $attributes);
        return $this->responseWithTransformer($partUsageResult, $this->transformer);
    }

    /**
     * @OA\Delete (
     *     path="/part-usage-results/{id}",
     *     operationId="PartUsageResultDelete",
     *     tags={"PartUsageResult"},
     *     summary="Delete a part",
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
        $this->partUsageResultService->destroy($id);
        return $this->respond();
    }
}
