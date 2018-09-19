<?php

namespace DanTheDJ\MultiTenant\Traits;

use Illuminate\Database\Migrations\Migrator;

trait MigrationChanges
{

    public function __construct()
    {
        $migrator = app('migrator');

        parent::__construct($migrator);
        $this->setName('tenant:migrate --tenant');

        $this->specifyParameters();
    }

    /**
     * Instance of Tenant Repository
     *
     * @var TenantRepository
     */
    protected $tenantRepo;

    public function handle()
    {

        // Get the name arguments and the age option from the input instance.
        $tenantSubdomain = $this->arguments('tenant');

        $tenantSubdomain = '*';

        if(is_null($tenantSubdomain))
        {

            $this->warn('You must specify a tenant or <info>*</info> to run for all tenants.');

            return;

        }

        if($tenantSubdomain == '*')
        {

            $this->line('*** Running migrations for <info>all</info> tenants ***');

            $this->handleTenantMigrations();

        }
        else
        {

            $this->handleTenantMigrations($tenantSubdomain);

        }

    }

    public function handleTenantMigrations($tenantSubdomain = null)
    {

        $resolver = app('tenant');

        $tenants = [];

        if(is_null($tenantSubdomain))
        {

            $tenants = $resolver->getAllTenants();

        }
        else
        {

            $resolver->resolveBySubdomain($tenantSubdomain);

            $tenant = $resolver->getActiveTenant();

            if(!is_null($tenant))
            {

                $tenants = [$tenant];

            }

        }

        if(count($tenants) == 0)
        {

            $this->warn('Could not find tenant [<info>' . $tenantSubdomain . '</info>]');

        }

        foreach($tenants as $tenant) {

            $this->line('Running migrations for [<info>' . $tenant->subdomain . '</info>]');

            $resolver->resolveBySubdomain($tenant->subdomain);

            parent::handle();

            $resolver->purgeConnection();

        }

    }

}