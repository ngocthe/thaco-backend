<?php

namespace App\Http\Middleware;

use App\Constants\Permission;
use App\Models\Setting;
use Carbon\Carbon;
use Closure;
use Exception;
use Illuminate\Http\Request;

class CheckPermissionImport
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
        $type = $request->get('type');
        $permission = Permission::getPermissionByImportType($type);

        if (!$permission) {
            throw new Exception('The selected type is invalid');
        }

        if (auth()->user()->can($permission)) {
            return $next($request);
        } else {
            throw new Exception('You are not authorized to perform this action.');
        }

    }
}
