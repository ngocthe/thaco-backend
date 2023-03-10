<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Client as OClient;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\Token;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Laravel\Passport\TokenRepository;
use Laravel\Passport\RefreshTokenRepository;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class AuthService
{
    protected $oClient;
    protected $guards;

    public function __construct()
    {
        $this->_retrieveClients();
    }

    private function _retrieveClients()
    {
        $clients = OClient::where('password_client', 1)->get();
        $configProviders = config('auth.providers');
        $configGuards = config('auth.guards');

        $providerGuardMaps = [];
        foreach ($configGuards as $guard => $configGuard) {
            if (isset($configGuard['provider']) && $configGuard['driver'] === 'session') {
                $providerGuardMaps[$configGuard['provider']] = $guard;
            }
        }

        foreach ($clients as $client) {
            if (isset($client['provider']) && isset($configProviders[$client['provider']])) {
                $model = $configProviders[$client['provider']]['model'];
                $this->oClient[$model] = $client;
                $this->guards[$model] = $providerGuardMaps[$client['provider']];
            }
        }
    }

    private function _getClient(string $key)
    {
        return $this->oClient[$key] ?? null;
    }

    private function _getGuard(string $key)
    {
        return $this->guards[$key] ?? null;
    }

    /**
     * @throws \Exception
     */
    public function register(string $modelNamespace, $username, $email, $password): array
    {
        $data = compact('username', 'email', 'password');
        $data['password'] = Hash::make($password);
        $user = $modelNamespace::create($data);

        $tokenData = $this->generateToken($modelNamespace, $username, $password);
        if (!$tokenData) {
            throw new BadRequestException();
        }

        return Arr::add($tokenData, 'user', $user);
    }

    /**
     * @throws \Exception
     */
    public function login(string $modelNamespace, $username, $password)
    {
        if (!auth($this->_getGuard($modelNamespace))->attempt(compact('username', 'password'))) {
            throw new UnauthorizedHttpException(__('api.exception.user_not_found'));
        }

        $result = $this->generateToken($modelNamespace, $username, $password);
        if (!$result) {
            throw new UnauthorizedHttpException(__('api.exception.user_not_found'));
        }
        $user = auth($this->_getGuard($modelNamespace))->user();
        $result['user'] = $user;

        return $result;
    }

    public function logout($user)
    {
        return $this->revokeToken($user->token());
    }

    public function profile(): ?\Illuminate\Contracts\Auth\Authenticatable
    {
        return auth()->user();
    }

    /**
     * @param string $modelNamespace
     * @param $username
     * @param $password
     * @return mixed|null
     * @throws \Exception
     */
    public function generateToken(string $modelNamespace, $username, $password)
    {
        $oClient = $this->_getClient($modelNamespace);
        if (!$oClient) return null;
        $request = Request::create('/oauth/token', 'POST', [
            'grant_type' => 'password',
            'client_id' => (string)$oClient->id,
            'client_secret' => $oClient->secret,
            'username' => $username,
            'password' => $password,
            'scope' => '*',
        ]);
        $response = app()->handle($request);
        if ($response->getStatusCode() === HttpResponse::HTTP_OK) {
            return json_decode((string)$response->getContent(), true);
        }

        return null;
    }

    /**
     * @param string $modelNamespace
     * @param $refreshToken
     * @return mixed|null
     * @throws \Exception
     */
    public function refreshToken(string $modelNamespace, $refreshToken)
    {
        $oClient = $this->_getClient($modelNamespace);
        if (!$oClient) return null;

        $request = Request::create('/oauth/token', 'POST', [
            'grant_type' => 'refresh_token',
            'client_id' => (string)$oClient->id,
            'client_secret' => $oClient->secret,
            'refresh_token' => $refreshToken,
            'scope' => '*',
        ]);
        $response = app()->handle($request);
        if ($response->getStatusCode() === HttpResponse::HTTP_OK) {
            return json_decode((string)$response->getContent(), true);
        }
        return null;
    }

    /**
     * @param Token $token
     * @return mixed
     */
    public function revokeToken(Token $token)
    {
        $tokenRepository = app(TokenRepository::class);
        $refreshTokenRepository = app(RefreshTokenRepository::class);

        $tokenRepository->revokeAccessToken($token->id);
        return $refreshTokenRepository->revokeRefreshTokensByAccessTokenId($token->id);
    }

    /**
     * @param Authenticatable $user
     * @param bool $include
     */
    public function revokeAllTokens(Authenticatable $user, bool $include = true)
    {
        $tokens = $user->tokens;
        $currentToken = $user->token();
        if (count($tokens)) {
            foreach ($tokens as $token) {
                if ($token === $currentToken && !$include) continue;
                $this->revokeToken($token);
            }
        }
    }

    public function revokeOtherUserTokens(Authenticatable $user, bool $include = true)
    {
        $tokens = $user->tokens;
        if (count($tokens)) {
            foreach ($tokens as $token) {
                $this->revokeToken($token);
            }
        }
    }
}
