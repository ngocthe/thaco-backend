<?php

namespace App\Http\Controllers\Admin;

use App\Exports\MscExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Msc\CreateMscRequest;
use App\Http\Requests\Admin\Msc\UpdateMscRequest;
use App\Services\MscService;
use App\Transformers\MscTransformer;
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

class MscController extends Controller
{
    /**
     * @var MscService
     */
    protected MscService $mscService;
    /**
     * @var MscTransformer
     */
    protected MscTransformer $transformer;

    public function __construct(Manager $fractal, MscService $mscService, MscTransformer $mscTransformer)
    {
        $this->mscService = $mscService;
        $this->transformer = $mscTransformer;
        parent::__construct($fractal);
    }

    /**
     * @OA\Get (
     *     path="/mscs",
     *     operationId="MscList",
     *     tags={"Msc"},
     *     summary="List msc",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\Parameter(
     *         name="code",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="car_line",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="model_grade",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="body",
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
        $mscs = $this->mscService->paginate();
        return $this->responseWithTransformer($mscs, $this->transformer);
    }

    /**
     * @OA\Post  (
     *     path="/mscs",
     *     operationId="MscCreate",
     *     tags={"Msc"},
     *     summary="Creeat a msc",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CreateMscRequest")
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
     * @param CreateMscRequest $request
     * @return Response
     * @throws ArrayWithMixedKeysException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws InvalidTypeException
     * @throws MissingConfigurationKeyException
     * @throws NotIntegerException
     * @throws Exception
     */
    public function store(CreateMscRequest $request): Response
    {
        $attributes = $request->only([
            'code',
			'description',
			'interior_color',
			'car_line',
			'model_grade',
			'body',
			'engine',
			'transmission',
			'plant_code',
			'effective_date_in',
			'effective_date_out'
        ]);
        $msc = $this->mscService->store($attributes);
        return $this->responseWithTransformer($msc, $this->transformer);
    }

    /**
     * @OA\Get   (
     *     path="/mscs/codes",
     *     operationId="MscListCode",
     *     tags={"Msc"},
     *     summary="Get list msc codes",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\Parameter(
     *         name="code",
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
        $codes = $this->mscService->searchCode();
        return $this->respond($codes);
    }

    /**
     * @OA\Get   (
     *     path="/mscs/columns",
     *     operationId="MscColumns",
     *     tags={"Msc"},
     *     summary="Get column of msc",
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
        $codes = $this->mscService->getColumnValue();
        return $this->respond($codes);
    }

    /**
     * @OA\Get   (
     *     path="/mscs/export",
     *     operationId="MscExport",
     *     tags={"Msc"},
     *     summary="Export Msc",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\Parameter(
     *         name="code",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="car_line",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="model_grade",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="body",
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
        return $this->mscService->export($request, MscExport::class, 'msc-master');
    }

    /**
     * @OA\Get   (
     *     path="/mscs/{id}",
     *     operationId="MscShow",
     *     tags={"Msc"},
     *     summary="Get a msc detail",
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
        $msc = $this->mscService->show($id);
        return $this->responseWithTransformer($msc, $this->transformer);
    }

    /**
     * @OA\Put (
     *     path="/mscs/{id}",
     *     operationId="MscUpdate",
     *     tags={"Msc"},
     *     summary="Update a msc",
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
     *         @OA\JsonContent(ref="#/components/schemas/UpdateMscRequest")
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
     * @param UpdateMscRequest $request
     * @return Response
     * @throws ArrayWithMixedKeysException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws InvalidTypeException
     * @throws MissingConfigurationKeyException
     * @throws NotIntegerException
     * @throws Exception
     */
    public function update(int $id, UpdateMscRequest $request): Response
    {
        $attributes = $request->only([
			'description',
			'interior_color',
			'car_line',
			'model_grade',
			'body',
			'engine',
			'transmission',
			'effective_date_in',
			'effective_date_out'
        ]);
        $msc = $this->mscService->update($id, $attributes);
        return $this->responseWithTransformer($msc, $this->transformer);
    }

    /**
     * @OA\Delete (
     *     path="/mscs/{id}",
     *     operationId="MscDelete",
     *     tags={"Msc"},
     *     summary="Delete a msc",
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
        $result = $this->mscService->destroy($id);
        if ($result) return $this->respond(); else return $this->respondWithError( 200, 400, 'Item used! Can not delete!');

    }
}
