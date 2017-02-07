<?php
namespace DanTheDJ\MultiTenant\Events;

use Illuminate\Queue\SerializesModels;
use DanTheDJ\MultiTenant\Tenant;

class TenantEvent
{
    use SerializesModels;

    public $tenant;

    public function __construct(Tenant $tenant)
    {
        $this->tenant = $tenant;
    }

}