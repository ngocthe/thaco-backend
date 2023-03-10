<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AdminExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Admin\ChangePassRequest;
use App\Http\Requests\Admin\Admin\CreateAdminRequest;
use App\Http\Requests\Admin\Admin\UpdateAdminRequest;
use App\Models\Admin;
use App\Services\AdminService;
use App\Services\AuthService;
use App\Transformers\AdminTransformer;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use League\Fractal\Manager;
use MarcinOrlowski\ResponseBuilder\Exceptions\ArrayWithMixedKeysException;
use MarcinOrlowski\ResponseBuilder\Exceptions\ConfigurationNotFoundException;
use MarcinOrlowski\ResponseBuilder\Exceptions\IncompatibleTypeException;
use MarcinOrlowski\ResponseBuilder\Exceptions\InvalidTypeException;
use MarcinOrlowski\ResponseBuilder\Exceptions\MissingConfigurationKeyException;
use MarcinOrlowski\ResponseBuilder\Exceptions\NotIntegerException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class AdminController extends Controller
{
    /**
     * @var AdminService
     */
    protected AdminService $adminService;

    /**
     * @var AuthService
     */
    protected AuthService $authService;

    /**
     * @var AdminTransformer
     */
    protected AdminTransformer $transformer;

    public function __construct(Manager $fractal, AdminService $adminService, AuthService $authService, AdminTransformer $adminTransformer)
    {
        $this->adminService = $adminService;
        $this->authService = $authService;
        $this->transformer = $adminTransformer;
        $this->transformer->setDefaultIncludes(['roles', 'user', 'remarks']);
        parent::__construct($fractal);
    }

    /**
     * @OA\Get (
     *     path="/admins",
     *     operationId="AdminList",
     *     tags={"Admin"},
     *     summary="List admin",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\Parameter(
     *         name="code",
     *         in="query"
     *     ),
     *     @OA\Parameter(
     *         name="company",
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

        $admins = $this->adminService->paginate();
        return $this->responseWithTransformer($admins, $this->transformer);
    }

    /**
     * @OA\Post  (
     *     path="/admins",
     *     operationId="AdminCreate",
     *     tags={"Admin"},
     *     summary="Creeat a admin",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CreateAdminRequest")
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
     * @param CreateAdminRequest $request
     * @return Response
     * @throws ArrayWithMixedKeysException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws InvalidTypeException
     * @throws MissingConfigurationKeyException
     * @throws NotIntegerException
     * @throws Exception
     */
    public function store(CreateAdminRequest $request): Response
    {
        $attributes = $request->only([
            'code',
            'name',
            'company',
            'password'
        ]);
        $attributes['username'] = $attributes['code'];
        if (isset($attributes['password'])) {
            $attributes['password'] = Hash::make($attributes['password']);
        } else {
            $attributes['password'] = Hash::make(config('env.PASSWORD_DEFAULT'));
            $attributes['password_default'] = true;
        }
        $admin = $this->adminService->store($attributes);
        $admin->assignRole($request->get('role'));
        return $this->responseWithTransformer($admin, $this->transformer);
    }

    /**
     * @OA\Get   (
     *     path="/admins/{id}",
     *     operationId="AdminShow",
     *     tags={"Admin"},
     *     summary="Get a admin detail",
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
        $admin = $this->adminService->show($id);
        return $this->responseWithTransformer($admin, $this->transformer);
    }

    /**
     * @OA\Put (
     *     path="/admins/{id}",
     *     operationId="AdminUpdate",
     *     tags={"Admin"},
     *     summary="Update a admin",
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
     *         @OA\JsonContent(ref="#/components/schemas/UpdateAdminRequest")
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
     * @param UpdateAdminRequest $request
     * @return Response
     * @throws ArrayWithMixedKeysException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws InvalidTypeException
     * @throws MissingConfigurationKeyException
     * @throws NotIntegerException
     * @throws Exception
     */
    public function update(int $id, UpdateAdminRequest $request): Response
    {
        $attributes = $request->only([
            'name',
            'company',
            'password_default'
        ]);
        if (($attributes['password_default']))
        {
            $attributes['password'] = Hash::make(config('env.PASSWORD_DEFAULT'));
            $user = Admin::find($id);
            $this->authService->revokeOtherUserTokens($user);
        }
        $admin = $this->adminService->update($id, $attributes);
        if ($request->has('role'))
            $admin->syncRoles($request->get('role', []));
        return $this->responseWithTransformer($admin, $this->transformer);
    }

    /**
     * @OA\Delete (
     *     path="/admins/{id}",
     *     operationId="AdminDelete",
     *     tags={"Admin"},
     *     summary="Delete a admin",
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
        $this->adminService->destroy($id);
        return $this->respond();
    }

    /**
     * @OA\Post (
     *      path="/change-password",
     *      operationId="ChangePass",
     *      tags={"Admin"},
     *      summary="ChangePass",
     *      security={
     *         {"sanctum": {}}
     *      },
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(ref="#/components/schemas/ChangePassRequest")
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(
     *             @OA\Examples(example="result", value={"status": true, "message": "OK", "data": null}, summary="Change password success."),
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
     *                      "message": "The password field is required.",
     *                      "data": {
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
     * @param ChangePassRequest $request
     * @return Response
     * @throws ArrayWithMixedKeysException
     * @throws ConfigurationNotFoundException
     * @throws IncompatibleTypeException
     * @throws InvalidTypeException
     * @throws MissingConfigurationKeyException
     * @throws NotIntegerException
     *
     */
    public function changePass(ChangePassRequest $request): Response
    {
        $data = $request->only('password', 'current_password');
        $userId = $request->user_id ?? auth()->id();
        if ($this->adminService->changePassword($userId, $data['current_password'], $data['password'])) {
            $user = Admin::find($userId);
            $this->authService->revokeOtherUserTokens($user);
            return $this->respond();
        } else {
            return $this->respondWithError(200, 400, __('api.password_not_match'));
        }
    }


    /**
     * @OA\Get (
     *     path="/admins/export",
     *     operationId="UserExport",
     *     tags={"Admin"},
     *     summary="Export user",
     *     security={
     *         {"sanctum": {}}
     *     },
     *     @OA\Parameter(
     *         name="code",
     *         in="query"
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
        return $this->adminService->export($request, AdminExport::class, 'user-master');
    }

}
