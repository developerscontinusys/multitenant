## Laravel MultiTenant 1.0

[![Build Status](https://travis-ci.org/DanTheDJ/multitenant.svg?branch=master)](https://travis-ci.org/DanTheDJ/multitenant)

The Laravel 5.3 MultiTenant package enables you to easily add multi-tenant capabilities to your application.
This package is designed using a minimalist approach providing just the essentials - no views, routes,
or configs. Just drop it in, run the migration, and start adding tenants. Your applications will
have access to current tenant information through the dynamically set config('tenant') values.
Optionally, you can let applications reconnect to the default master database so a tenant
could manage all tenant other accounts for example. And, perhaps the best part, Artisan
is completely multi-tenant aware! Just add the --tenant option to any command to
run that command on one or all tenants. Works on migrations, queueing, etc.!

MultiTenant also offers a TenantContract, triggers Laravel events, and throws a TenantNotResolvedException and TenantDatabaseNameEmptyException, so you can easily add in custom functionality and tweak it for your needs.

Laravel MultiTenant was forked from @thinksaydo, who modified the original Tenantable project by @leemason. All of the main code is due to them. The difference in this project is that it allows for a database per tenant, compared to a single database with table prefixes. This allows for a more managed approach in some cases.

MultiTenant relies on your ENV and Database config and stores just the
conneciton name in the table and only allows one subdomain and
domain per tenant, which is most often plenty for most apps.
MultiTenant also throws a TenantNotResolvedException when
tenants are not found, and a TenantDatabaseNameEmptyException when the database name could not be determined.


## Simple Installation & Usage

Composer install:

```
composer require danthedj/multitenant:1.0
```

Generate composer autoload file:

```
composer dump-autoload
```

Tenants database table install (uses default database connection):

```php 
artisan migrate --path /vendor/danthedj/multitenant/migrations
```

Service provider install:

After `Illuminate\Database\DatabaseServiceProvider::class,` in `config/app.php` place the following to register the service provider:

```php
DanTheDJ\MultiTenant\TenantServiceProvider::class,
```

Database connection:

In `config/database.php` create a new connection. For the `host`, `port` ,`username` and `password`, these are picked up from the `.env` file.

```php

'tenant_db' => [
    'driver' => 'mysql',
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', '3306'),
    'database' => '', // this will be filled in dynamically based on the tenant subdomain.
    'username' => env('DB_USERNAME', 'forge'),
    'password' => env('DB_PASSWORD', ''),
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => 'tenant_', // this can be changed and represents a database prefix e.g. 'business_acme'
    'strict' => true,
    'engine' => null,
],

```

Tenant creation (just uses a standard Eloquent model):

```php
$tenant = new \DanTheDJ\MultiTenant\Tenant();
$tenant->name = 'ACME Inc.';
$tenant->email = 'person@acmeinc.com';
$tenant->subdomain = 'acme';
$tenant->alias_domain = 'acmeinc.com';
$tenant->connection = 'tenant_db';
$tenant->meta = ['phone' => '123-123-1234'];
$tenant->save();
```

And you're done! Minimalist, simple. Whenever your app is visited via http://acme.domain.com or http://acmeinc.com
the connection "tenant_db" will be used, the database name will switch to "{prefix}_acme", and config('tenant')
will be set with tenant details allowing you to access values from your views or application.


## Advanced EnvTenant Usage

### Artisan

```php
// migrate master database tables (in tenant database)
php artisan migrate

// migrate specific tenant database tables
php artisan migrate --tenant=acme

// migrate all tenant database tables
php artisan migrate --tenant=*
```

The --tenant option works on all Artisan commands:

```php
php artisan migrate:rollback --tenant=acme

```


### Tenant

The ```\DanTheDJ\MultiTenant\Tenant``` class is a simple Eloquent model providing basic tenant settings.

```php
$tenant = new \DanTheDJ\MultiTenant\Tenant();

// The unique name field identifies the tenant profile
$tenant->name = 'ACME Inc.';

// The non-unique email field lets you email tenants
$tenant->email = 'person@acmeinc.com';

// The unique subdomain field represents the subdomain portion of a domain and the database table prefix
// Set subdomain and alias_domain field to NULL to access tenant by ID instead
$tenant->subdomain = 'acme';

// The unique alias_domain field represents an alternate full domain that can be used to access the tenant
// Set subdomain and alias_domain field to NULL to access tenant by ID instead
$tenant->alias_domain = 'acmeinc.com';

// The non-unique connection field stores the Laravel database connection name
$tenant->connection = 'db1';

// The meta field is cast to an array and allows you to store any extra values you might need to know
$tenant->meta = ['phone' => '123-123-1234'];

$tenant->save();
```


### TenantResolver

The ```\DanTheDJ\MultiTenant\TenantResolver``` class is responsible for resolving and managing the active tenant
during Web and Artisan access. You can access the resolver class using ```app('tenant')```.

```php
// get the resolver instance
$resolver = app('tenant');

// check if valid tenant
$resolver->isResolved();

// get the active tenant (returns Tenant model or null)
$tenant = $resolver->getActiveTenant();

// get all tenants (returns collection of Tenant models or null)
$tenants = $resolver->getAllTenants();

// activate and run all tenants through a callback function
$resolver->mapAllTenants(function ($tenant) {});

// reconnect default connection enabling access to "tenants" table
$resolver->reconnectDefaultConnection();

// reconnect tenant connection disabling access to "tenants" table
$resolver->reconnectTenantConnection();
```

If you want to use a custom model, register a custom service provider that binds a singleton to the TenantContract
and resolves to an instance of your custom tenant model. EnvTenant will automatically defer to your custom model
as long as you load your service provider before loading the EnvTenant\TenantServiceProvider.

Create this example service provider in your app/Providers folder as CustomTenantServiceProvider.php:

```php
<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Tenant;

class CustomTenantServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->app->singleton('TenantContract', function()
        {
            return new Tenant();
        });
    }

    public function register()
    {
        //
    }
}
```

Then register ```App\Providers\CustomTenantServiceProvider::class``` in your config/app.php file.

### Events

Throughout the lifecycle events are fired allowing you to listen and customize behavior.

Tenant activated:
```php
DanTheDJ\MultiTenant\Events\TenantActivatedEvent
```

Tenant resolved:
```php
DanTheDJ\MultiTenant\Events\TenantResolvedEvent
```

Tenant not resolved:
```php
DanTheDJ\MultiTenant\Events\TenantNotResolvedEvent
```

Tenant not resolved via the Web, an exception is thrown:
```php
DanTheDJ\MultiTenant\Events\TenantNotResolvedException
```

Tenant database name not be determined or empty:
```php
DanTheDJ\MultiTenant\Events\TenantDatabaseNameEmptyException
```
