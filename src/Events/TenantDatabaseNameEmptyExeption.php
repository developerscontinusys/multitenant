<?php
namespace DanTheDJ\MultiTenant\Events;

class TenantDatabaseNameEmptyException extends \Exception
{
    public function getTenant()
    {
        return $this->getMessage();
    }
}