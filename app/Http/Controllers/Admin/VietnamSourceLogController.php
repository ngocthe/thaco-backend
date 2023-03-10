<?php

namespace App\Http\Controllers\Admin;

use App\Exports\VietnamSourceRequestExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\VietnamSourceLog\CreateVietnamSourceLogRequest;
use App\Http\Requests\Admin\VietnamSourceLog\UpdateVietnamSourceLogRequest;
use App\Services\VietnamSourceLogService;
use App\Transformers\VietnamSourceLogTransformer;
use Exception;
use League\Fractal\Manager;
use MarcinOrlowski\ResponseBuilder\Exceptions\ArrayWithMixedKeysException;
use MarcinOrlowski\ResponseBuilder\Exceptions\ConfigurationNotFoundException;
use MarcinOrlowski\ResponseBuilder\Exceptions\IncompatibleTypeException;
use MarcinOrlowski\ResponseBuilder\Exceptions\InvalidTypeException;
use MarcinOrlowski\ResponseBuilder\Exceptions\MissingConfigurationKeyException;
use MarcinOrlowski\ResponseBuilder\Exceptions\NotIntegerException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;

class VietnamSourceLogController extends Controller
{
    /**
     * @var VietnamSourceLogService
     */
    protected VietnamSourceLogService $vietnamSourceLogService;
    /**
     * @var VietnamSourceLogTransformer
     */
    protected VietnamSourceLogTransformer $transformer;

    public function __construct(Manager $fractal, VietnamSourceLogService $vietnamSourceLogService, VietnamSourceLogTransformer $vietnamSourceLogTransformer)
    {
        $this->vietnamSourceLogService = $vietnamSourceLogService;
        $this->transformer = $vietnamSourceLogTransformer;
        parent::__construct($fractal);
    }

    /**
     *
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
        $vietnamSourceLogs = $this->vietnamSourceLogService->paginate();
        return $this->responseWithTransformer($vietnamSourceLogs, $this->transformer);
    }

    /**
     * @param CreateVietnamSourceLogRequest $request
     * @return Response
     * @throws ArrayWithMixedKeysException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws InvalidTypeException
     * @throws MissingConfigurationKeyException
     * @throws NotIntegerException
     * @throws Exception
     */
    public function store(CreateVietnamSourceLogRequest $request): Response
    {
        $attributes = $request->only([
            'contract_code',
			'invoice_code',
			'bill_of_lading_code',
			'container_code',
			'case_code',
			'part_code',
			'part_color_code',
			'box_type_code',
			'box_quantity',
			'part_quantity',
			'unit',
			'supplier_code',
			'delivery_date',
			'plant_code'
        ]);
        $vietnamSourceLog = $this->vietnamSourceLogService->store($attributes);
        return $this->responseWithTransformer($vietnamSourceLog, $this->transformer);
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
        $vietnamSourceLog = $this->vietnamSourceLogService->show($id);
        return $this->responseWithTransformer($vietnamSourceLog, $this->transformer);
    }

    /**
     * @param int $id
     * @param UpdateVietnamSourceLogRequest $request
     * @return Response
     * @throws ArrayWithMixedKeysException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws InvalidTypeException
     * @throws MissingConfigurationKeyException
     * @throws NotIntegerException
     * @throws Exception
     */
    public function update(int $id, UpdateVietnamSourceLogRequest $request): Response
    {
        $attributes = $request->only([
            'box_quantity',
            'part_quantity',
            'unit',
            'supplier_code',
            'delivery_date'
        ]);
        $vietnamSourceLog = $this->vietnamSourceLogService->update($id, $attributes);
        return $this->responseWithTransformer($vietnamSourceLog, $this->transformer);
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
        $this->vietnamSourceLogService->destroy($id);
        return $this->respond();
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
    public function columns(): Response
    {
        $codes = $this->vietnamSourceLogService->getColumnValue();
        return $this->respond($codes);
    }
    /**
     *
     * @param Request $request
     * @return BinaryFileResponse
     */
    public function export(Request $request): BinaryFileResponse
    {
        return $this->vietnamSourceLogService->export($request, VietnamSourceRequestExport::class, 'vietnam-source-log');
    }
}
