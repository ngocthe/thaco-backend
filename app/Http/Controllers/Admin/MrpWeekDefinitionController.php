<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MrpWeekDefinition\CreateMrpWeekDefinitionRequest;
use App\Services\MrpWeekDefinitionService;
use App\Transformers\MrpWeekDefinitionTransformer;
use Exception;
use League\Fractal\Manager;
use MarcinOrlowski\ResponseBuilder\Exceptions\ArrayWithMixedKeysException;
use MarcinOrlowski\ResponseBuilder\Exceptions\ConfigurationNotFoundException;
use MarcinOrlowski\ResponseBuilder\Exceptions\IncompatibleTypeException;
use MarcinOrlowski\ResponseBuilder\Exceptions\InvalidTypeException;
use MarcinOrlowski\ResponseBuilder\Exceptions\MissingConfigurationKeyException;
use MarcinOrlowski\ResponseBuilder\Exceptions\NotIntegerException;
use Symfony\Component\HttpFoundation\Response;

class MrpWeekDefinitionController extends Controller
{
    /**
     * @var MrpWeekDefinitionService
     */
    protected MrpWeekDefinitionService $mrpWeekDefinitionService;
    /**
     * @var MrpWeekDefinitionTransformer
     */
    protected MrpWeekDefinitionTransformer $transformer;

    public function __construct(Manager $fractal, MrpWeekDefinitionService $mrpWeekDefinitionService, MrpWeekDefinitionTransformer $mrpWeekDefinitionTransformer)
    {
        $this->mrpWeekDefinitionService = $mrpWeekDefinitionService;
        $this->transformer = $mrpWeekDefinitionTransformer;
        parent::__construct($fractal);
    }

    /**
     * @OA\Get (
     *     path="/mrp-week-definitions",
     *     operationId="MrpWeekDefinitionList",
     *     tags={"MrpWeekDefinition"},
     *     summary="List mrp week definitions",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\Parameter(
     *         name="year",
     *         in="query",
     *         description="format: yyyy"
     *     ),
     *     @OA\Parameter(
     *         name="month",
     *         in="query",
     *         description="format: mm"
     *     ),
     *     @OA\Parameter(
     *         name="extract_month",
     *         in="query",
     *         description="format: 1 | 0"
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
        $mrpWeekDefinitions = $this->mrpWeekDefinitionService->paginate();
        return $this->responseWithTransformer($mrpWeekDefinitions, $this->transformer);
    }

    /**
     * @OA\Post (
     *     path="/mrp-week-definitions",
     *     operationId="MrpWeekDefinitionCreate",
     *     tags={"MrpWeekDefinition"},
     *     summary="Creeat a MrpWeekDefinition",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CreateMrpWeekDefinitionRequest")
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
     * @param CreateMrpWeekDefinitionRequest $request
     * @return Response
     * @throws ArrayWithMixedKeysException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws InvalidTypeException
     * @throws MissingConfigurationKeyException
     * @throws NotIntegerException
     * @throws Exception
     */
    public function store(CreateMrpWeekDefinitionRequest $request): Response
    {
        $attributes = $request->only([
            'date',
			'is_holiday',
			'month_no',
			'week_no'
        ]);
        $mrpWeekDefinition = $this->mrpWeekDefinitionService->store($attributes, false);
        return $mrpWeekDefinition ? $this->responseWithTransformer($mrpWeekDefinition, $this->transformer) : $this->respond();
    }

}
