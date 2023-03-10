<?php

namespace App\Http\Controllers\Admin;

use App\Exports\PartColorExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PartColor\CreatePartColorRequest;
use App\Http\Requests\Admin\PartColor\UpdatePartColorRequest;
use App\Services\PartColorService;
use App\Transformers\PartColorTransformer;
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

class PartColorController extends Controller
{
    /**
     * @var PartColorService
     */
    protected PartColorService $partColorService;
    /**
     * @var PartColorTransformer
     */
    protected PartColorTransformer $transformer;

    public function __construct(Manager $fractal, PartColorService $partColorService, PartColorTransformer $partColorTransformer)
    {
        $this->partColorService = $partColorService;
        $this->transformer = $partColorTransformer;
        parent::__construct($fractal);
    }

    /**
     * @OA\Get (
     *     path="/part-colors",
     *     operationId="PartColorList",
     *     tags={"PartColor"},
     *     summary="List part color",
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
     *         name="name",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="interior_code",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="vehicle_color_code",
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
        $partColors = $this->partColorService->paginate();
        return $this->responseWithTransformer($partColors, $this->transformer);
    }

    /**
     * @OA\Post  (
     *     path="/part-colors",
     *     operationId="PartColorCreate",
     *     tags={"PartColor"},
     *     summary="Creeat a part color",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CreatePartColorRequest")
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
     * @param CreatePartColorRequest $request
     * @return Response
     * @throws ArrayWithMixedKeysException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws InvalidTypeException
     * @throws MissingConfigurationKeyException
     * @throws NotIntegerException
     * @throws Exception
     */
    public function store(CreatePartColorRequest $request): Response
    {
        $attributes = $request->only([
            'code',
            'part_code',
            'name',
            'interior_code',
            'vehicle_color_code',
            'ecn_in',
            'ecn_out',
            'plant_code'
        ]);
        $partColor = $this->partColorService->store($attributes);
        return $this->responseWithTransformer($partColor, $this->transformer);
    }

    /**
     * @OA\Get   (
     *     path="/part-colors/codes",
     *     operationId="PartColorCode",
     *     tags={"PartColor"},
     *     summary="Get list part color codes",
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
     *         name="vehicle_color_code",
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
        $codes = $this->partColorService->searchCode();
        return $this->respond($codes);
    }

    /**
     * @OA\Get   (
     *     path="/part-colors/columns",
     *     operationId="PartColorColumns",
     *     tags={"PartColor"},
     *     summary="Get column of part-colors",
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
        $codes = $this->partColorService->getColumnValue();
        return $this->respond($codes);
    }

    /**
     * @OA\Get (
     *     path="/part-colors/export",
     *     operationId="PartColorExport",
     *     tags={"PartColor"},
     *     summary="Export part color",
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
     *         name="name",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="interior_code",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="vehicle_color_code",
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
        return $this->partColorService->export($request, PartColorExport::class, 'part-color-code-master');
    }

    /**
     * @OA\Get   (
     *     path="/part-colors/{id}",
     *     operationId="PartColorShow",
     *     tags={"PartColor"},
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
        $partColor = $this->partColorService->show($id);
        return $this->responseWithTransformer($partColor, $this->transformer);
    }

    /**
     * @OA\Put (
     *     path="/part-colors/{id}",
     *     operationId="PartColorUpdate",
     *     tags={"PartColor"},
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
     *         @OA\JsonContent(ref="#/components/schemas/UpdatePartColorRequest")
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
     * @param UpdatePartColorRequest $request
     * @return Response
     * @throws ArrayWithMixedKeysException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws InvalidTypeException
     * @throws MissingConfigurationKeyException
     * @throws NotIntegerException
     * @throws Exception
     */
    public function update(int $id, UpdatePartColorRequest $request): Response
    {
        $attributes = $request->only([
            'name',
            'interior_code',
            'vehicle_color_code',
            'ecn_out'
        ]);
        $partColor = $this->partColorService->update($id, $attributes);
        return $this->responseWithTransformer($partColor, $this->transformer);
    }

    /**
     * @OA\Delete (
     *     path="/part-colors/{id}",
     *     operationId="PartColorDelete",
     *     tags={"PartColor"},
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
        $result = $this->partColorService->destroy($id);
        if ($result) return $this->respond(); else return $this->respondWithError( 200, 400, 'Item used! Can not delete!');
    }
}
