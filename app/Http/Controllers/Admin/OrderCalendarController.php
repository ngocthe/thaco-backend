<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OrderCalendar\CreateOrderCalendarRequest;
use App\Http\Requests\Admin\OrderCalendar\UpdateOrderCalendarRequest;
use App\Services\OrderCalendarService;
use App\Transformers\OrderCalendarTransformer;
use Exception;
use League\Fractal\Manager;
use MarcinOrlowski\ResponseBuilder\Exceptions\ArrayWithMixedKeysException;
use MarcinOrlowski\ResponseBuilder\Exceptions\ConfigurationNotFoundException;
use MarcinOrlowski\ResponseBuilder\Exceptions\IncompatibleTypeException;
use MarcinOrlowski\ResponseBuilder\Exceptions\InvalidTypeException;
use MarcinOrlowski\ResponseBuilder\Exceptions\MissingConfigurationKeyException;
use MarcinOrlowski\ResponseBuilder\Exceptions\NotIntegerException;
use Symfony\Component\HttpFoundation\Response;

class OrderCalendarController extends Controller
{
    /**
     * @var OrderCalendarService
     */
    protected OrderCalendarService $orderCalendarService;
    /**
     * @var OrderCalendarTransformer
     */
    protected OrderCalendarTransformer $transformer;

    public function __construct(Manager $fractal, OrderCalendarService $orderCalendarService, OrderCalendarTransformer $orderCalendarTransformer)
    {
        $this->orderCalendarService = $orderCalendarService;
        $this->transformer = $orderCalendarTransformer;
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
        $orderCalendars = $this->orderCalendarService->paginate();
        return $this->responseWithTransformer($orderCalendars, $this->transformer);
    }

    /**
     * @param CreateOrderCalendarRequest $request
     * @return Response
     * @throws ArrayWithMixedKeysException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws InvalidTypeException
     * @throws MissingConfigurationKeyException
     * @throws NotIntegerException
     * @throws Exception
     */
    public function store(CreateOrderCalendarRequest $request): Response
    {
        $attributes = $request->only([
            'contract_code',
			'part_group',
			'etd',
			'eta  ',
			'lead_time',
			'ordering_cycle'
        ]);
        $orderCalendar = $this->orderCalendarService->store($attributes);
        return $this->responseWithTransformer($orderCalendar, $this->transformer);
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
        $orderCalendar = $this->orderCalendarService->show($id);
        return $this->responseWithTransformer($orderCalendar, $this->transformer);
    }

    /**
     * @param int $id
     * @param UpdateOrderCalendarRequest $request
     * @return Response
     * @throws ArrayWithMixedKeysException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws InvalidTypeException
     * @throws MissingConfigurationKeyException
     * @throws NotIntegerException
     * @throws Exception
     */
    public function update(int $id, UpdateOrderCalendarRequest $request): Response
    {
        $attributes = $request->only([
            'contract_code',
			'part_group',
			'etd',
			'eta  ',
			'lead_time',
			'ordering_cycle'
        ]);
        $orderCalendar = $this->orderCalendarService->update($id, $attributes);
        return $this->responseWithTransformer($orderCalendar, $this->transformer);
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
        $this->orderCalendarService->destroy($id);
        return $this->respond();
    }
}
