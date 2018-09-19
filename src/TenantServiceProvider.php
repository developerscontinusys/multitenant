<?php
namespace DanTheDJ\MultiTenant;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Foundation\Application;

class TenantServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bindIf('DanTheDJ\MultiTenant\Contracts\TenantContract', function()
        {
            return new Tenant();
        }, true);

        $this->app->singleton('tenant', function($app)
        {
            $tenant = app('DanTheDJ\MultiTenant\Contracts\TenantContract');
            return new TenantResolver($app, $tenant, app('db'));
        });

        $this->commands([
            Console\Commands\MigrateTenantCommand::class
        ]);

    }

    public function boot()
    {
        $resolver = app('tenant');
        $resolver->resolveTenant();
    }
}