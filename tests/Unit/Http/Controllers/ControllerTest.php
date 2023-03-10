<?php

namespace Tests\Unit\Http\Controllers;

use App\Constants\ApiCodes;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class ControllerTest extends TestCase
{
    /**
     * @var Controller
     */
    private $controller;

    protected function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub

        $this->controller = $this->app->make(Controller::class);
    }

    public function test_respond_method()
    {
        $user = User::factory()->make();

        $response = $this->controller->respond($user);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->status());
        $this->assertEquals('OK', $response->statusText());
        $this->assertEquals(['item' => $user->toArray()], $response->getOriginalContent());
    }

    public function test_response_with_message_method()
    {
        $user = User::factory()->make();
        $message = 'User Created';

        $response = $this->controller->respondWithMessage($message);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_OK, $response->status());
        $this->assertEquals('OK', $response->statusText());
        $this->assertEquals(['message' => $message], $response->getOriginalContent());
    }

    public function test_response_with_error_method()
    {
        $message = 'This is an error';
        $error = ['trace' => 'Error trace'];
        foreach (array_keys(Arr::except(ApiCodes::READABLE_CODE_MAP, ['default'])) as $apiCode) {
            $responseWithMessage = $this->controller->respondWithError($apiCode, Response::HTTP_BAD_GATEWAY, $message, $error);
            $responseWithoutMessage = $this->controller->respondWithError($apiCode, Response::HTTP_BAD_GATEWAY, null, $error);

            $this->assertInstanceOf(JsonResponse::class, $responseWithMessage);
            $this->assertEquals(Response::HTTP_BAD_GATEWAY, $responseWithMessage->status());
            $this->assertEquals(Response::$statusTexts[Response::HTTP_BAD_GATEWAY], $responseWithMessage->statusText());
            $this->assertArraySubset([
                'code' => ApiCodes::convertToReadable($apiCode),
                'message' => $message,
                'errors' => (object) $error,
            ], $responseWithMessage->getOriginalContent());

            $this->assertInstanceOf(JsonResponse::class, $responseWithoutMessage);
            $this->assertEquals(Response::HTTP_BAD_GATEWAY, $responseWithoutMessage->status());
            $this->assertEquals(Response::$statusTexts[Response::HTTP_BAD_GATEWAY], $responseWithoutMessage->statusText());
            $this->assertArraySubset([
                'code' => ApiCodes::convertToReadable($apiCode),
                'message' => "Error #$apiCode",
                'errors' => (object) $error,
            ], $responseWithoutMessage->getOriginalContent());
        }
    }

    public function test_response_bad_request_method()
    {
        $responseDefaultCode = $this->controller->respondBadRequest();
        $responseCustomCode = $this->controller->respondBadRequest(ApiCodes::HTTP_EXCEPTION);

        $this->assertInstanceOf(JsonResponse::class, $responseDefaultCode);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $responseDefaultCode->status());
        $this->assertEquals(Response::$statusTexts[Response::HTTP_BAD_REQUEST], $responseDefaultCode->statusText());
        $this->assertArraySubset([
            'code' => ApiCodes::convertToReadable(ApiCodes::UNCAUGHT_EXCEPTION)
        ], $responseDefaultCode->getOriginalContent());

        $this->assertInstanceOf(JsonResponse::class, $responseCustomCode);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $responseCustomCode->status());
        $this->assertEquals(Response::$statusTexts[Response::HTTP_BAD_REQUEST], $responseCustomCode->statusText());
        $this->assertArraySubset([
            'code' => ApiCodes::convertToReadable(ApiCodes::HTTP_EXCEPTION)
        ], $responseCustomCode->getOriginalContent());
    }

    public function test_response_unauthorized_request_method()
    {
        $responseDefaultCode = $this->controller->respondUnauthorizedRequest();
        $responseCustomCode = $this->controller->respondUnauthorizedRequest(ApiCodes::HTTP_EXCEPTION);

        $this->assertInstanceOf(JsonResponse::class, $responseDefaultCode);
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $responseDefaultCode->status());
        $this->assertEquals(Response::$statusTexts[Response::HTTP_UNAUTHORIZED], $responseDefaultCode->statusText());
        $this->assertArraySubset([
            'code' => ApiCodes::convertToReadable(ApiCodes::UNAUTHORIZED_EXCEPTION)
        ], $responseDefaultCode->getOriginalContent());

        $this->assertInstanceOf(JsonResponse::class, $responseCustomCode);
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $responseCustomCode->status());
        $this->assertEquals(Response::$statusTexts[Response::HTTP_UNAUTHORIZED], $responseCustomCode->statusText());
        $this->assertArraySubset([
            'code' => ApiCodes::convertToReadable(ApiCodes::HTTP_EXCEPTION)
        ], $responseCustomCode->getOriginalContent());
    }

    public function test_response_not_found_method()
    {
        $responseDefaultCode = $this->controller->respondNotFound();
        $responseCustomCode = $this->controller->respondNotFound(ApiCodes::HTTP_EXCEPTION);

        $this->assertInstanceOf(JsonResponse::class, $responseDefaultCode);
        $this->assertEquals(Response::HTTP_NOT_FOUND, $responseDefaultCode->status());
        $this->assertEquals(Response::$statusTexts[Response::HTTP_NOT_FOUND], $responseDefaultCode->statusText());
        $this->assertArraySubset([
            'code' => ApiCodes::convertToReadable(ApiCodes::HTTP_NOT_FOUND)
        ], $responseDefaultCode->getOriginalContent());

        $this->assertInstanceOf(JsonResponse::class, $responseCustomCode);
        $this->assertEquals(Response::HTTP_NOT_FOUND, $responseCustomCode->status());
        $this->assertEquals(Response::$statusTexts[Response::HTTP_NOT_FOUND], $responseCustomCode->statusText());
        $this->assertArraySubset([
            'code' => ApiCodes::convertToReadable(ApiCodes::HTTP_EXCEPTION)
        ], $responseCustomCode->getOriginalContent());
    }
}
