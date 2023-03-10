<?php

namespace App\Http\Controllers\Admin;

use App\Exports\BoxTypeExport;
use App\Exports\SupplierExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BoxType\CreateBoxTypeRequest;
use App\Http\Requests\Admin\BoxType\UpdateBoxTypeRequest;
use App\Services\BoxTypeService;
use App\Transformers\BoxTypeTransformer;
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

class BoxTypeController extends Controller
{
    /**
     * @var BoxTypeService
     */
    protected BoxTypeService $boxTypeService;
    /**
     * @var BoxTypeTransformer
     */
    protected BoxTypeTransformer $transformer;

    public function __construct(Manager $fractal, BoxTypeService $boxTypeService, BoxTypeTransformer $boxTypeTransformer)
    {
        $this->boxTypeService = $boxTypeService;
        $this->transformer = $boxTypeTransformer;
        parent::__construct($fractal);
    }

    /**
     * @OA\Get (
     *     path="/box-types",
     *     operationId="BoxTypeList",
     *     tags={"BoxType"},
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
        $boxTypes = $this->boxTypeService->paginate();
        return $this->responseWithTransformer($boxTypes, $this->transformer);
    }

    /**
     * @OA\Post  (
     *     path="/box-types",
     *     operationId="BoxTypeCreate",
     *     tags={"BoxType"},
     *     summary="Creeat a box type",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CreateBoxTypeRequest")
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
     * @param CreateBoxTypeRequest $request
     * @return Response
     * @throws ArrayWithMixedKeysException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws InvalidTypeException
     * @throws MissingConfigurationKeyException
     * @throws NotIntegerException
     * @throws Exception
     */
    public function store(CreateBoxTypeRequest $request): Response
    {
        $attributes = $request->only([
            'code',
			'part_code',
			'description',
			'weight',
			'width',
			'height',
			'depth',
			'quantity',
			'unit',
			'plant_code'
        ]);
        $boxType = $this->boxTypeService->store($attributes);
        return $this->responseWithTransformer($boxType, $this->transformer);
    }

    /**
     * @OA\Get   (
     *     path="/box-types/codes",
     *     operationId="BoxTypeListCode",
     *     tags={"BoxType"},
     *     summary="Get list box type codes",
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
        $codes = $this->boxTypeService->searchCode();
        return $this->respond($codes);
    }

    /**
     * @OA\Get (
     *     path="/box-types/export",
     *     operationId="BoxTypeExport",
     *     tags={"BoxType"},
     *     summary="Export Box Type",
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
        return $this->boxTypeService->export($request, BoxTypeExport::class, 'box-type-master');
    }

    /**
     * @OA\Get   (
     *     path="/box-types/{id}",
     *     operationId="BoxTypeShow",
     *     tags={"BoxType"},
     *     summary="Get a BoxType detail",
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
        $boxType = $this->boxTypeService->show($id);
        return $this->responseWithTransformer($boxType, $this->transformer);
    }

    /**
     * @OA\Put (
     *     path="/box-types/{id}",
     *     operationId="BoxTypeUpdate",
     *     tags={"BoxType"},
     *     summary="Update a box type",
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
     *         @OA\JsonContent(ref="#/components/schemas/UpdateBoxTypeRequest")
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
     * @param UpdateBoxTypeRequest $request
     * @return Response
     * @throws ArrayWithMixedKeysException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws InvalidTypeException
     * @throws MissingConfigurationKeyException
     * @throws NotIntegerException
     * @throws Exception
     */
    public function update(int $id, UpdateBoxTypeRequest $request): Response
    {
        $attributes = $request->only([
			'description',
			'weight',
			'width',
			'height',
			'depth',
			'quantity',
			'unit'
        ]);
        $boxType = $this->boxTypeService->update($id, $attributes);
        return $this->responseWithTransformer($boxType, $this->transformer);
    }

    /**
     * @OA\Delete (
     *     path="/box-types/{id}",
     *     operationId="BoxTypeDelete",
     *     tags={"BoxType"},
     *     summary="Delete a box type",
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
        $result = $this->boxTypeService->destroy($id);
        if ($result) return $this->respond(); else return $this->respondWithError( 200, 400, 'Item used! Can not delete!');
    }
}
