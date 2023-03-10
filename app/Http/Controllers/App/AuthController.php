<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Http\Requests\App\User\LoginRequest;
use App\Http\Requests\App\User\LogoutRequest;
use App\Http\Requests\App\User\RefreshTokenRequest;
use App\Http\Requests\App\User\RegisterRequest;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * @throws \Exception
     */
    public function register(RegisterRequest $request)
    {
        $result = DB::transaction(function() use ($request) {
            $data = $request->only('username', 'email', 'password');

            return $this->authService->register(User::class, ...array_values($data));
        });

        return $this->respond($result);
    }

    public function login(LoginRequest $request)
    {
        $data = $request->only('username', 'password');

        return $this->respond($this->authService->login(User::class, ...array_values($data)));
    }

    public function logout(LogoutRequest $request)
    {
        $this->authService->logout(auth()->user());

        return $this->respond();
    }

    public function refreshToken(RefreshTokenRequest $request)
    {
        $data = $request->only('refresh_token');

        $result = $this->authService->refreshToken(User::class, $data['refresh_token']);
        if (!$result) {
            throw new UnauthorizedHttpException('', __('api.exception.invalid_refresh_token'));
        }

        return $this->respond($result);
    }

    public function profile()
    {
        $user = $this->authService->profile();

        return $this->respond(['user' => $user]);
    }
}
