<?php

namespace App\Http\Controllers\Admin;

use App\Exports\PartExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Part\CreatePartRequest;
use App\Http\Requests\Admin\Part\UpdatePartRequest;
use App\Services\PartService;
use App\Transformers\PartTransformer;
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

class PartController extends Controller
{
    /**
     * @var PartService
     */
    protected PartService $partService;
    /**
     * @var PartTransformer
     */
    protected PartTransformer $transformer;

    public function __construct(Manager $fractal, PartService $partService, PartTransformer $partTransformer)
    {
        $this->partService = $partService;
        $this->transformer = $partTransformer;
        parent::__construct($fractal);
    }

    /**
     * @OA\Get (
     *     path="/parts",
     *     operationId="PartList",
     *     tags={"Part"},
     *     summary="List part",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\Parameter(
     *         name="code",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="name",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="group",
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
        $parts = $this->partService->paginate();
        return $this->responseWithTransformer($parts, $this->transformer);
    }

    /**
     * @OA\Post  (
     *     path="/parts",
     *     operationId="PartCreate",
     *     tags={"Part"},
     *     summary="Creeat a part",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CreatePartRequest")
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
     * @param CreatePartRequest $request
     * @return Response
     * @throws ArrayWithMixedKeysException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws InvalidTypeException
     * @throws MissingConfigurationKeyException
     * @throws NotIntegerException
     * @throws Exception
     */
    public function store(CreatePartRequest $request): Response
    {
        $attributes = $request->only([
            'code',
			'name',
			'group',
			'ecn_in',
			'ecn_out',
			'plant_code'
        ]);
        $part = $this->partService->store($attributes);
        return $this->responseWithTransformer($part, $this->transformer);
    }

    /**
     * @OA\Get   (
     *     path="/parts/codes",
     *     operationId="PartCode",
     *     tags={"Part"},
     *     summary="Get list part codes",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\Parameter(
     *         name="code",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="group",
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
     *     @OA\Parameter(
     *         name="is_with_parent_code",
     *         in="query",
     *         @OA\Schema(type="boolean")
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
        $codes = $this->partService->searchCode();
        return $this->respond($codes);
    }

    /**
     * @OA\Get   (
     *     path="/parts/columns",
     *     operationId="PartColumns",
     *     tags={"Part"},
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
        $codes = $this->partService->getColumnValue();
        return $this->respond($codes);
    }

    /**
     * @OA\Get (
     *     path="/parts/export",
     *     operationId="PartExport",
     *     tags={"Part"},
     *     summary="Export part",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\Parameter(
     *         name="code",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="name",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="group",
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
        return $this->partService->export($request, PartExport::class, 'part-master');
    }

    /**
     * @OA\Get   (
     *     path="/parts/{id}",
     *     operationId="PartShow",
     *     tags={"Part"},
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
        $part = $this->partService->show($id);
        return $this->responseWithTransformer($part, $this->transformer);
    }

    /**
     * @OA\Put (
     *     path="/parts/{id}",
     *     operationId="PartUpdate",
     *     tags={"Part"},
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
     *         @OA\JsonContent(ref="#/components/schemas/UpdatePartRequest")
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
     * @param UpdatePartRequest $request
     * @return Response
     * @throws ArrayWithMixedKeysException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws InvalidTypeException
     * @throws MissingConfigurationKeyException
     * @throws NotIntegerException
     * @throws Exception
     */
    public function update(int $id, UpdatePartRequest $request): Response
    {
        $attributes = $request->only([
			'name',
			'group',
			'ecn_out'
        ]);
        $part = $this->partService->update($id, $attributes);
        return $this->responseWithTransformer($part, $this->transformer);
    }

    /**
     * @OA\Delete (
     *     path="/parts/{id}",
     *     operationId="PartDelete",
     *     tags={"Part"},
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
        $result = $this->partService->destroy($id);
        if ($result) return $this->respond(); else return $this->respondWithError( 200, 400, 'Item used! Can not delete!');
    }
}
