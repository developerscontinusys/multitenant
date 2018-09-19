<?php

namespace DanTheDJ\MultiTenant\Console\Commands;

use Illuminate\Database\Console\Migrations\MigrateCommand as BaseCommand;
use DanTheDJ\MultiTenant\Traits\MigrationChanges;

class MigrateTenantCommand extends BaseCommand
{

    use MigrationChanges;

}
