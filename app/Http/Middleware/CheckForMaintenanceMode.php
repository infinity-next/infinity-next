<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode as MaintenanceMiddleware;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Closure;
use Request;

class CheckForMaintenanceMode extends MaintenanceMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($this->app->isDownForMaintenance()) {
            $admins = explode(',', (string) env('APP_ROOT_IP'));

            if (!is_array($admins) || !count($admins) || !in_array(Request::ip(), $admins)) {
                throw new HttpException(503);
            }
        }

        return $next($request);
    }
}
