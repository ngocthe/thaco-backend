<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DefectInventory\CreateDefectInventoryRequest;
use App\Http\Requests\Admin\DefectInventory\UpdateDefectInventoryRequest;
use App\Services\DefectInventoryService;
use App\Transformers\DefectInventoryTransformer;
use Exception;
use League\Fractal\Manager;
use MarcinOrlowski\ResponseBuilder\Exceptions\ArrayWithMixedKeysException;
use MarcinOrlowski\ResponseBuilder\Exceptions\ConfigurationNotFoundException;
use MarcinOrlowski\ResponseBuilder\Exceptions\IncompatibleTypeException;
use MarcinOrlowski\ResponseBuilder\Exceptions\InvalidTypeException;
use MarcinOrlowski\ResponseBuilder\Exceptions\MissingConfigurationKeyException;
use MarcinOrlowski\ResponseBuilder\Exceptions\NotIntegerException;
use Symfony\Component\HttpFoundation\Response;

class DefectInventoryController extends Controller
{
    /**
     * @var DefectInventoryService
     */
    protected DefectInventoryService $defectInventoryService;
    /**
     * @var DefectInventoryTransformer
     */
    protected DefectInventoryTransformer $transformer;

    public function __construct(Manager $fractal, DefectInventoryService $defectInventoryService, DefectInventoryTransformer $defectInventoryTransformer)
    {
        $this->defectInventoryService = $defectInventoryService;
        $this->transformer = $defectInventoryTransformer;
        parent::__construct($fractal);
    }

    /**
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
        $defectInventories = $this->defectInventoryService->paginate();
        return $this->responseWithTransformer($defectInventories, $this->transformer);
    }

    /**
     * @param CreateDefectInventoryRequest $request
     * @return Response
     * @throws ArrayWithMixedKeysException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws InvalidTypeException
     * @throws MissingConfigurationKeyException
     * @throws NotIntegerException
     * @throws Exception
     */
    public function store(CreateDefectInventoryRequest $request): Response
    {
        $attributes = $request->only([
            'modelable_type',
			'modelable_id',
			'box_id',
			'defect_id',
			'part_defect_quantity'
        ]);
        $defectInventory = $this->defectInventoryService->store($attributes);
        return $this->responseWithTransformer($defectInventory, $this->transformer);
    }

    /**
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
        $defectInventory = $this->defectInventoryService->show($id);
        return $this->responseWithTransformer($defectInventory, $this->transformer);
    }

    /**
     * @param int $id
     * @param UpdateDefectInventoryRequest $request
     * @return Response
     * @throws ArrayWithMixedKeysException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws InvalidTypeException
     * @throws MissingConfigurationKeyException
     * @throws NotIntegerException
     * @throws Exception
     */
    public function update(int $id, UpdateDefectInventoryRequest $request): Response
    {
        $attributes = $request->only([
            'modelable_type',
			'modelable_id',
			'box_id',
			'defect_id',
			'part_defect_quantity'
        ]);
        $defectInventory = $this->defectInventoryService->update($id, $attributes);
        return $this->responseWithTransformer($defectInventory, $this->transformer);
    }

    /**
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
        $this->defectInventoryService->destroy($id);
        return $this->respond();
    }
}
