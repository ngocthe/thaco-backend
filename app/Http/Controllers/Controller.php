<?php

namespace App\Http\Controllers;

use App\Constants\ApiCodes;
use App\Helpers\KaopizSerializerHelper;
use App\Http\ResponseBuilder\ResponseBuilder;
use Illuminate\Database\Eloquent\Collection as DBCollection;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\Controller as BaseController;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use MarcinOrlowski\ResponseBuilder\Exceptions\ArrayWithMixedKeysException;
use MarcinOrlowski\ResponseBuilder\Exceptions\ConfigurationNotFoundException;
use MarcinOrlowski\ResponseBuilder\Exceptions\IncompatibleTypeException;
use MarcinOrlowski\ResponseBuilder\Exceptions\InvalidTypeException;
use MarcinOrlowski\ResponseBuilder\Exceptions\MissingConfigurationKeyException;
use MarcinOrlowski\ResponseBuilder\Exceptions\NotIntegerException;
use Symfony\Component\HttpFoundation\Response;

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="THACO APIs",
 *      description="API Endpoints of THACO",
 *      @OA\Contact(
 *          email="nhiembv@kaopiz.com"
 *      ),
 *      @OA\License(
 *          name="Apache 2.0",
 *          url="http://www.apache.org/licenses/LICENSE-2.0.html"
 *      )
 * )
 *
 * @OA\Server(
 *      url=L5_SWAGGER_CONST_HOST,
 *      description="THACO API Server"
 * )
 *
 * @OA\PathItem(path="/api")
 *
 */
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var Manager
     */
    protected Manager $fractal;

    function __construct(Manager $fractal)
    {
        $this->fractal = $fractal;
    }

    /**
     * @throws InvalidTypeException
     * @throws NotIntegerException
     * @throws IncompatibleTypeException
     * @throws ConfigurationNotFoundException
     * @throws ArrayWithMixedKeysException
     * @throws MissingConfigurationKeyException
     */
    public function respond($data = null, $msg = null): Response
    {
        return ResponseBuilder::asSuccess()->withData($data)->withMessage($msg)->build();
    }

    /**
     * @param $data
     * @param $transformer
     * @param null $msg
     * @param array $additionalData
     * @return Response
     * @throws ArrayWithMixedKeysException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws InvalidTypeException
     * @throws MissingConfigurationKeyException
     * @throws NotIntegerException
     */
    public function responseWithTransformer($data, $transformer, $msg = null, array $additionalData = []): Response
    {
        if (isset($_GET['include'])) {
            $this->fractal->parseIncludes($_GET['include']);
        }
        $this->fractal->setSerializer(new KaopizSerializerHelper());
        $pagination = null;
        switch (true) {
            case $data instanceof DBCollection:
                $resource = new Collection($data, $transformer);
                break;
            case $data instanceof LengthAwarePaginator:
                $resource = $data->getCollection();
                $pagination = [
                    'total' => $data->total(),
                    'current_page' => $data->currentPage(),
                    'per_page' => $data->perPage(),
                    'total_page' => $data->lastPage()
                ];
                $resource = new Collection($resource, $transformer);
                break;
            default:
                $resource = new Item($data, $transformer);
                break;
        }

        $resource = $this->fractal->createData($resource);
        if ($pagination) {
            $result = [
                'data' => $resource->toArray(),
                'pagination' => $pagination
            ];
            if ($additionalData) {
                $result = array_merge($result, $additionalData);
            }
        } else {
            $result = $resource->toArray();
        }
        return $this->respond($result, $msg);
    }

    /**
     * @throws InvalidTypeException
     * @throws NotIntegerException
     * @throws IncompatibleTypeException
     * @throws ConfigurationNotFoundException
     * @throws ArrayWithMixedKeysException
     * @throws MissingConfigurationKeyException
     */
    public function respondWithMessage($msg = null): Response
    {
        return ResponseBuilder::asSuccess()->withMessage($msg)->build();
    }

    /**
     * @throws InvalidTypeException
     * @throws NotIntegerException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws ArrayWithMixedKeysException
     * @throws MissingConfigurationKeyException
     */
    public function respondWithError($apiCode, $HttpCode, $message = null, $error = null): Response
    {
        return ResponseBuilder::asError($apiCode)->withHttpCode($HttpCode)->withMessage($message)->withData($error)->build();
    }

    /**
     * @throws InvalidTypeException
     * @throws NotIntegerException
     * @throws ArrayWithMixedKeysException
     * @throws MissingConfigurationKeyException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     */
    public function respondBadRequest($apiCode = ApiCodes::UNCAUGHT_EXCEPTION): Response
    {
        return $this->respondWithError($apiCode, Response::HTTP_BAD_REQUEST);
    }

    /**
     * @throws InvalidTypeException
     * @throws NotIntegerException
     * @throws ArrayWithMixedKeysException
     * @throws MissingConfigurationKeyException
     * @throws IncompatibleTypeException
     * @throws ConfigurationNotFoundException
     */
    public function respondUnauthorizedRequest($apiCode = ApiCodes::UNAUTHORIZED_EXCEPTION): Response
    {
        return $this->respondWithError($apiCode, Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @throws InvalidTypeException
     * @throws NotIntegerException
     * @throws ArrayWithMixedKeysException
     * @throws MissingConfigurationKeyException
     * @throws IncompatibleTypeException
     * @throws ConfigurationNotFoundException
     */
    public function respondNotFound($apiCode = ApiCodes::HTTP_NOT_FOUND): Response
    {
        return $this->respondWithError($apiCode, Response::HTTP_NOT_FOUND);
    }
}
