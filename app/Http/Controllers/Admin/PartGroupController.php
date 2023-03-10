<?php

namespace App\Http\Controllers\Admin;

use App\Exports\PartGroupExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PartGroup\CreatePartGroupRequest;
use App\Http\Requests\Admin\PartGroup\UpdatePartGroupRequest;
use App\Models\PartGroup;
use App\Services\PartGroupService;
use App\Transformers\PartGroupTransformer;
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

class PartGroupController extends Controller
{
    /**
     * @var PartGroupService
     */
    protected PartGroupService $partGroupService;
    /**
     * @var PartGroupTransformer
     */
    protected PartGroupTransformer $transformer;

    public function __construct(Manager $fractal, PartGroupService $partGroupService, PartGroupTransformer $partGroupTransformer)
    {
        $this->partGroupService = $partGroupService;
        $this->transformer = $partGroupTransformer;
        parent::__construct($fractal);
    }

    /**
     * @OA\Get (
     *     path="/part-groups",
     *     operationId="PartGroupList",
     *     tags={"PartGroup"},
     *     summary="List part group",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\Parameter(
     *         name="code",
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
        $partGroups = $this->partGroupService->paginate();
        return $this->responseWithTransformer($partGroups, $this->transformer);
    }

    /**
     * @OA\Get (
     *     path="/part-groups/entity/{code}",
     *     operationId="PartGroupByCode",
     *     tags={"PartGroup"},
     *     summary="List part group",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\Parameter(
     *         in="path",
     *         name="code",
     *         required=true,
     *         @OA\Schema(type="string")
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
     * @param $code
     * @return Response
     * @throws ArrayWithMixedKeysException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws InvalidTypeException
     * @throws MissingConfigurationKeyException
     * @throws NotIntegerException
     */
    public function getPartGroupByCode($code): ?Response
    {
        $partGroup = $this->partGroupService->getPartGroupByCode($code);
        if (!$partGroup) {
            return $this->respond($partGroup, '');
        }
        return $this->responseWithTransformer($partGroup, $this->transformer);
    }

    /**
     * @OA\Post  (
     *     path="/part-groups",
     *     operationId="PartGroupCreate",
     *     tags={"PartGroup"},
     *     summary="Creeat a part group",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CreatePartGroupRequest")
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
     * @param CreatePartGroupRequest $request
     * @return Response
     * @throws ArrayWithMixedKeysException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws InvalidTypeException
     * @throws MissingConfigurationKeyException
     * @throws NotIntegerException
     * @throws Exception
     */
    public function store(CreatePartGroupRequest $request): Response
    {
        $attributes = $request->only([
            'code',
			'description',
			'lead_time',
			'ordering_cycle',
            'delivery_lead_time',
            'type_part_group'
        ]);
        $partGroup = $this->partGroupService->store($attributes);
        return $this->responseWithTransformer($partGroup, $this->transformer);
    }

    /**
     * @OA\Get   (
     *     path="/part-groups/codes",
     *     operationId="PartGroupListCode",
     *     tags={"PartGroup"},
     *     summary="Get list part group codes",
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
        $codes = $this->partGroupService->searchCode();
        return $this->respond($codes);
    }

    /**
     * @OA\Get (
     *     path="/part-groups/export",
     *     operationId="PartGroupExport",
     *     tags={"PartGroup"},
     *     summary="Export part group",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\Parameter(
     *         name="code",
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
        return $this->partGroupService->export($request, PartGroupExport::class, 'part-group-master');
    }

    /**
     * @OA\Get   (
     *     path="/part-groups/{id}",
     *     operationId="PartGroupShow",
     *     tags={"PartGroup"},
     *     summary="Get a part group detail",
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
        $partGroup = $this->partGroupService->show($id);
        return $this->responseWithTransformer($partGroup, $this->transformer);
    }

    /**
     * @OA\Put (
     *     path="/part-groups/{id}",
     *     operationId="PartGroupUpdate",
     *     tags={"PartGroup"},
     *     summary="Update a part group",
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
     *         @OA\JsonContent(ref="#/components/schemas/UpdatePartGroupRequest")
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
     * @param UpdatePartGroupRequest $request
     * @return Response
     * @throws ArrayWithMixedKeysException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws InvalidTypeException
     * @throws MissingConfigurationKeyException
     * @throws NotIntegerException
     * @throws Exception
     */
    public function update(int $id, UpdatePartGroupRequest $request): Response
    {
        $attributes = $request->only([
			'description',
			'lead_time',
			'ordering_cycle',
            'delivery_lead_time',
            'type_part_group'
        ]);
        $partGroup = $this->partGroupService->update($id, $attributes);
        return $this->responseWithTransformer($partGroup, $this->transformer);
    }

    /**
     * @OA\Delete (
     *     path="/part-groups/{id}",
     *     operationId="PartGroupDelete",
     *     tags={"PartGroup"},
     *     summary="Delete a part group",
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
        $result = $this->partGroupService->destroy($id);
        if ($result) return $this->respond(); else return $this->respondWithError( 200, 400, 'Item used! Can not delete!');
    }
}
