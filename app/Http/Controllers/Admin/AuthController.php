<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Admin\LoginRequest;
use App\Http\Requests\Admin\Admin\LogoutRequest;
use App\Http\Requests\Admin\Admin\RefreshTokenRequest;
use App\Models\Admin;
use App\Services\AuthService;
use App\Transformers\AdminAuthTransformer;
use Exception;
use League\Fractal\Manager;
use MarcinOrlowski\ResponseBuilder\Exceptions\ArrayWithMixedKeysException;
use MarcinOrlowski\ResponseBuilder\Exceptions\ConfigurationNotFoundException;
use MarcinOrlowski\ResponseBuilder\Exceptions\IncompatibleTypeException;
use MarcinOrlowski\ResponseBuilder\Exceptions\InvalidTypeException;
use MarcinOrlowski\ResponseBuilder\Exceptions\MissingConfigurationKeyException;
use MarcinOrlowski\ResponseBuilder\Exceptions\NotIntegerException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class AuthController extends Controller
{
    /**
     * @var AuthService
     */
    protected AuthService $authService;

    public function __construct(Manager $fractal, AuthService $authService)
    {
        $this->authService = $authService;
        parent::__construct($fractal);
    }

    /**
     * @OA\Post (
     *      path="/auth/login",
     *      operationId="AuthLogin",
     *      tags={"Auth"},
     *      summary="Login",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(ref="#/components/schemas/LoginRequest")
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(
     *             @OA\Examples(example="result", value={"status": true, "message": "OK", "data": {"token_type": "Bearer", "expires_in": 31536000, "access_token": "", "refresh_token": ""}}, summary="Login success."),
     *         )
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request",
     *         @OA\JsonContent(
     *             @OA\Examples(
     *                  example="result",
     *                  value={
     *                      "status": false,
     *                      "message": "The username field is required.",
     *                      "data": {
     *                          "username": {
     *                              "code": "10080",
     *                              "message": "The username field is required."
     *                          },
     *                          "password": {
     *                              "code": "10080",
     *                              "message": "The password field is required."
     *                          }
     *                      }
     *                  },
     *                  summary="Bad request"
     *              ),
     *          )
     *      )
     * )
     * @param LoginRequest $request
     * @return Response
     * @throws ArrayWithMixedKeysException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws InvalidTypeException
     * @throws MissingConfigurationKeyException
     * @throws NotIntegerException
     *
     */
    public function login(LoginRequest $request): Response
    {
        $data = $request->only('username', 'password');

        $result = $this->authService->generateToken(Admin::class, $data['username'], $data['password']);
        if (!$result) {
            throw new UnauthorizedHttpException(__('api.exception.user_not_found'));
        }

        return $this->respond($result);
    }

    /**
     * @OA\Post (
     *      path="/auth/refresh-token",
     *      operationId="AuthRefreshToken",
     *      tags={"Auth"},
     *      summary="Refresh token",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(ref="#/components/schemas/RefreshTokenRequest")
     *      ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(
     *             @OA\Examples(example="result", value={"status": true, "message": "OK", "data": {"token_type": "Bearer", "expires_in": 31536000, "access_token": "", "refresh_token": ""}}, summary="Login success."),
     *         )
     *     ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request",
     *         @OA\JsonContent(
     *             @OA\Examples(
     *                  example="result",
     *                  value={
     *                      "status": false,
     *                      "message": "The refresh token field is required.",
     *                      "data": {
     *                          "refresh_token": {
     *                              "code": "10080",
     *                              "message": "The refresh token field is required."
     *                          }
     *                      }
     *                  },
     *                  summary="Bad request"
     *              ),
     *          )
     *      )
     * )
     * @param RefreshTokenRequest $request
     * @return Response
     * @throws ArrayWithMixedKeysException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws InvalidTypeException
     * @throws MissingConfigurationKeyException
     * @throws NotIntegerException
     */
    public function refreshToken(RefreshTokenRequest $request): Response
    {
        $data = $request->only('refresh_token');

        try {
            $result = $this->authService->refreshToken(Admin::class, $data['refresh_token']);
            if (!$result) {
                throw new UnauthorizedHttpException('', __('api.exception.invalid_refresh_token'));
            }

            return $this->respond($result);
        } catch (Exception $e) {
            return $this->respondWithError(200, 500, $e->getMessage());
        }
    }

    /**
     * @OA\Get (
     *     path="/profile",
     *     operationId="AuthProfile",
     *     tags={"Auth"},
     *     summary="Get current user info",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(
     *             @OA\Examples(example="result", value={"status": true, "message": "OK", "data": {"token_type": "Bearer", "expires_in": 31536000, "access_token": "", "refresh_token": ""}}, summary="Login success."),
     *         )
     *     ),
     *      @OA\Response(
     *          response=422,
     *          description="Not authorized "
     *      )
     * )
     * @param AdminAuthTransformer $adminTransformer
     * @return Response
     * @throws ArrayWithMixedKeysException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws InvalidTypeException
     * @throws MissingConfigurationKeyException
     * @throws NotIntegerException
     */
    public function profile(AdminAuthTransformer $adminTransformer): Response
    {
        $user = auth()->user();
        return $this->responseWithTransformer($user, $adminTransformer);
    }

    /**
     * @OA\Delete  (
     *     path="/logout",
     *     operationId="AuthLogout",
     *     tags={"Auth"},
     *     summary="Logout",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(
     *             @OA\Examples(example="result", value={"status": true, "message": "OK", "data": null}, summary="Logout success."),
     *         )
     *     ),
     *      @OA\Response(
     *          response=422,
     *          description="Not authorized "
     *      )
     * )
     * @param LogoutRequest $request
     * @return Response
     * @throws ArrayWithMixedKeysException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws InvalidTypeException
     * @throws MissingConfigurationKeyException
     * @throws NotIntegerException
     */
    public function logout(LogoutRequest $request): Response
    {
        $user = auth()->user();
        $this->authService->revokeToken($user->token());

        return $this->respond();
    }
}

