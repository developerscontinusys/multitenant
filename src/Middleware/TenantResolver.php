<?php

namespace DanTheDJ\MultiTenant\Middleware;

use Closure;

class TenantResolver
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $resolver = app('tenant');
        $resolver->resolveTenant();

        return $next($request);
    }
}
