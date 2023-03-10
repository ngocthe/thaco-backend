<?php

namespace App\Http\Middleware;

use App\Helpers\LockTableHelper;
use Closure;
use Exception;
use Illuminate\Http\Request;

class LockTable
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @param string|null $guard
     * @return mixed
     * @throws Exception
     */
    public function handle(Request $request, Closure $next, string $guard = null)
    {
        LockTableHelper::checkLockTime($request);
        return $next($request);
    }
}
