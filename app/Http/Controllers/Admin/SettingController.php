<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Setting\SettingRequest;
use App\Services\SettingService;
use App\Transformers\SettingTransformer;
use League\Fractal\Manager;
use MarcinOrlowski\ResponseBuilder\Exceptions\ArrayWithMixedKeysException;
use MarcinOrlowski\ResponseBuilder\Exceptions\ConfigurationNotFoundException;
use MarcinOrlowski\ResponseBuilder\Exceptions\IncompatibleTypeException;
use MarcinOrlowski\ResponseBuilder\Exceptions\InvalidTypeException;
use MarcinOrlowski\ResponseBuilder\Exceptions\MissingConfigurationKeyException;
use MarcinOrlowski\ResponseBuilder\Exceptions\NotIntegerException;
use Symfony\Component\HttpFoundation\Response;

class SettingController extends Controller
{
    /**
     * @var SettingService
     */
    protected SettingService $settingService;

    /**
     * @var SettingTransformer
     */
    protected SettingTransformer $transformer;

    public function __construct(Manager $fractal, SettingService $settingService, SettingTransformer $settingTransformer)
    {
        $this->settingService = $settingService;
        $this->transformer = $settingTransformer;
        parent::__construct($fractal);
    }

    /**
     * @OA\Get    (
     *     path="/settings",
     *     operationId="SettingList",
     *     tags={"Setting"},
     *     summary="Get Setting list",
     *     security={
     *         {"sanctum": {}}
     *     },
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
        $settings = $this->settingService->findAll();
        return $this->responseWithTransformer($settings, $this->transformer);
    }

    /**
     * @OA\Post    (
     *     path="/settings",
     *     operationId="SettingUpdate",
     *     tags={"Setting"},
     *     summary="Add a Setting",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/SettingRequest")
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
     *
     * @param SettingRequest $request
     * @return Response
     * @throws ArrayWithMixedKeysException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws InvalidTypeException
     * @throws MissingConfigurationKeyException
     * @throws NotIntegerException
     */
    public function store(SettingRequest $request): Response
    {
        $this->settingService->store($request->only(['key', 'value']));
        return $this->respond();
    }

}
