<?php

namespace BildVitta\SpHub;

use BildVitta\SpHub\Console\Commands\ConfigureRabbitMQ;
use BildVitta\SpHub\Console\Commands\DataImport\Hub\HubImportCommand;
use BildVitta\SpHub\Console\Commands\InstallSp;
use BildVitta\SpHub\Console\Commands\Messages\HubMessageWorkerCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class SpHubServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('sp-hub')
            ->hasConfigFile()
            ->hasCommands([
                HubMessageWorkerCommand::class,
                InstallSp::class,
                HubImportCommand::class,
                ConfigureRabbitMQ::class,
            ])
            ->hasMigrations([
                'add_column_main_company_id_on_hub_companies',
                'add_deleted_at_column_on_users_table',
                'create_workers_table',
                'add_document_column_to_users_table',
                'add_address_columns_to_users_table',
                'add_address_and_company_name_columns_to_companies_table',
                'add_public_list_on_hub_companies_table',
                'add_external_code_column_to_hub_companies_table',
                'add_is_approving_proposals_and_approval_level_to_user_companies_table',
                'add_main_company_id_on_brands_table',
                'remove_is_approving_proposals_and_approval_level_to_user_companies_table',
                'adjust_unique_indexes_on_role_table',
            ])
            ->runsMigrations();
    }
}
