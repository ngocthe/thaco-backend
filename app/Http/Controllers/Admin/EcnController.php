<?php

namespace App\Http\Controllers\Admin;

use App\Constants\ValidationMessages;
use App\Exports\EcnExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Ecn\CreateEcnRequest;
use App\Http\Requests\Admin\Ecn\UpdateEcnRequest;
use App\Services\EcnService;
use App\Transformers\EcnTransformer;
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

class EcnController extends Controller
{
    /**
     * @var EcnService
     */
    protected EcnService $ecnService;
    /**
     * @var EcnTransformer
     */
    protected EcnTransformer $transformer;

    public function __construct(Manager $fractal, EcnService $ecnService, EcnTransformer $ecnTransformer)
    {
        $this->ecnService = $ecnService;
        $this->transformer = $ecnTransformer;
        parent::__construct($fractal);
    }

    /**
     * @OA\Get (
     *     path="/ecns",
     *     operationId="ECNList",
     *     tags={"ECN"},
     *     summary="List ecn",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\Parameter(
     *         name="code",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="page_number",
     *         in="query",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="line_number",
     *         in="query",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="released_party",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="released_date",
     *         in="query",
     *     ),
     *     @OA\Parameter(
     *         name="planned_line_off_date",
     *         in="query",
     *         description="format: yyyy-mm-dd"
     *     ),
     *     @OA\Parameter(
     *         name="actual_line_off_date",
     *         in="query",
     *         description="format: yyyy-mm-dd"
     *     ),
     *     @OA\Parameter(
     *         name="planned_packing_date",
     *         in="query",
     *         description="format: yyyy-mm-dd"
     *     ),
     *     @OA\Parameter(
     *         name="actual_packing_date",
     *         in="query",
     *         description="format: yyyy-mm-dd"
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
        $ecns = $this->ecnService->paginate();
        return $this->responseWithTransformer($ecns, $this->transformer);
    }

    /**
     * @OA\Post  (
     *     path="/ecns",
     *     operationId="ECNCreate",
     *     tags={"ECN"},
     *     summary="Creeat a ecn",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CreateEcnRequest")
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
     * @param CreateEcnRequest $request
     * @return Response
     * @throws ArrayWithMixedKeysException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws InvalidTypeException
     * @throws MissingConfigurationKeyException
     * @throws NotIntegerException
     * @throws Exception
     */
    public function store(CreateEcnRequest $request): Response
    {
        $attributes = $request->only([
            'code',
			'page_number',
			'line_number',
			'description',
			'mandatory_level',
			'production_interchangeability',
			'service_interchangeability',
			'released_party',
			'released_date',
			'planned_line_off_date',
			'actual_line_off_date',
			'planned_packing_date',
			'actual_packing_date',
			'vin',
			'complete_knockdown',
			'plant_code'
        ]);
        $ecn = $this->ecnService->store($attributes);
        if ($ecn) {
            return $this->responseWithTransformer($ecn, $this->transformer);
        } else {
            return $this->respondWithError(205, 400, '', [
                'actual_packing_date' => [
                    'code' => '10005',
                    'message' => 'The actual packing date must be a date after or equal to previous actual line off date.'
                ]
            ]);
        }
    }

    /**
     * @OA\Get   (
     *     path="/ecns/codes",
     *     operationId="EcnListCode",
     *     tags={"ECN"},
     *     summary="Get list ecn codes",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\Parameter(
     *         name="code",
     *         in="query",
     *         @OA\Schema(type="string")
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
        $codes = $this->ecnService->searchCode();
        return $this->respond($codes);
    }

    /**
     * @OA\Get   (
     *     path="/ecns/columns",
     *     operationId="EcnColumns",
     *     tags={"ECN"},
     *     summary="Get column of ecn",
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
        $codes = $this->ecnService->getColumnValue();
        return $this->respond($codes);
    }

    /**
     * @OA\Get   (
     *     path="/ecns/export",
     *     operationId="ECNExport",
     *     tags={"ECN"},
     *     summary="Export ECN",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\Parameter(
     *         name="code",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="page_number",
     *         in="query",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="line_number",
     *         in="query",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="released_party",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="released_date",
     *         in="query",
     *     ),
     *     @OA\Parameter(
     *         name="planned_line_off_date",
     *         in="query",
     *         description="format: yyyy-mm-dd"
     *     ),
     *     @OA\Parameter(
     *         name="actual_line_off_date",
     *         in="query",
     *         description="format: yyyy-mm-dd"
     *     ),
     *     @OA\Parameter(
     *         name="planned_packing_date",
     *         in="query",
     *         description="format: yyyy-mm-dd"
     *     ),
     *     @OA\Parameter(
     *         name="actual_packing_date",
     *         in="query",
     *         description="format: yyyy-mm-dd"
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
        return $this->ecnService->export($request, EcnExport::class, 'ecn-master');
    }

    /**
     * @OA\Get   (
     *     path="/ecns/{id}",
     *     operationId="ECNShow",
     *     tags={"ECN"},
     *     summary="Get a ecn detail",
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
        $ecn = $this->ecnService->show($id);
        return $this->responseWithTransformer($ecn, $this->transformer);
    }

    /**
     * @OA\Put (
     *     path="/ecns/{id}",
     *     operationId="ECNUpdate",
     *     tags={"ECN"},
     *     summary="Update a ecn",
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
     *         @OA\JsonContent(ref="#/components/schemas/UpdateEcnRequest")
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
     * @param UpdateEcnRequest $request
     * @return Response
     * @throws ArrayWithMixedKeysException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws InvalidTypeException
     * @throws MissingConfigurationKeyException
     * @throws NotIntegerException
     * @throws Exception
     */
    public function update(int $id, UpdateEcnRequest $request): Response
    {
        $attributes = $request->only([
            'page_number',
            'line_number',
			'description',
			'mandatory_level',
			'production_interchangeability',
			'service_interchangeability',
			'released_party',
			'released_date',
			'planned_line_off_date',
			'actual_line_off_date',
			'planned_packing_date',
			'actual_packing_date',
			'vin',
			'complete_knockdown'
        ]);
        $ecn = $this->ecnService->update($id, $attributes);
        if (!$ecn) {
            $messageCode = ValidationMessages::getMessageCode('unique');
            $message = 'Page and Line number value pair already exist';
            $errors = [
                'page_number' => ['code' => $messageCode, 'message' => $message],
                'line_number' => ['code' => $messageCode, 'message' => $message]
            ];
            return $this->respondWithError(400, 400, $message, $errors);
        }
        return $this->responseWithTransformer($ecn, $this->transformer);
    }

    /**
     * @OA\Delete (
     *     path="/ecns/{id}",
     *     operationId="ECNDelete",
     *     tags={"ECN"},
     *     summary="Delete a ecn",
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
        $result = $this->ecnService->destroy($id);
        if ($result) return $this->respond(); else return $this->respondWithError( 200, 400, 'Item used! Can not delete!');
    }
}
