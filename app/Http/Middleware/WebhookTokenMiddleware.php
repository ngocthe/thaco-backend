<?php

namespace App\Http\Middleware;

use App\Enums\LanguageEnum;
use Closure;
use Exception;
use Illuminate\Http\Request;

class WebhookTokenMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     * @throws Exception
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->header('Api-Token');
        if ($token && $token === config('env.WEBHOOK_TOKEN')) {
            $request->merge(['search_like' => false]);

            if ($request->header('language')) {
                if (in_array($request->header('language'), ['vi', 'en'])) {
                    app()->setLocale($request->header('language'));
                }
            }
            return $next($request);
        }
        throw new Exception('Api Token not correct.');
    }
}
