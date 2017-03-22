<?php
namespace DanTheDJ\MultiTenant\Providers;

use DanTheDJ\MultiTenant\Middleware\TenantResolver;
use DanTheDJ\MultiTenant\TenantServiceProvider;
use Illuminate\Support\ServiceProvider;

class TenantMiddlewareServiceProvider extends TenantServiceProvider
{
    /**
     * This function overrides the TenantServiceProvider class.
     *
     * Instead of trying to resolve the tenant upon boot
     * it only adds in a middleware alias that will
     * check when added to any route.
     */
    public function boot()
    {
        $this->app['router']->aliasMiddleware('tenantResolver', TenantResolver::class);
    }
}