<?php

namespace BildVitta\SpHub\Console\Commands;

use BildVitta\SpHub\SpHubServiceProvider;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallSp extends Command
{
    /**
     * Arguments to vendor config publish.
     *
     * @const array
     */
    private const VENDOR_PUBLISH_CONFIG_PARAMS = [
        '--provider' => SpHubServiceProvider::class,
        '--tag'      => 'sp-hub-config',
    ];

    /**
     * Arguments to vendor migration publish.
     *
     * @const array
     */
    private const VENDOR_PUBLISH_MIGRATION_PARAMS = [
        '--provider' => SpHubServiceProvider::class,
    ];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sp-hub:install';

    /**
     * The console command description.
     *
     * @var string|null
     */
    protected $description = 'Install the SP Hub';

    /**
     * @return void
     */
    public function handle()
    {
        $this->info('Installing SP Hub...');

        $this->info('Publishing configuration...');

        if (! $this->configExists('sp-hub.php')) {
            $this->publishConfiguration();
            $this->info('Published configuration');
        } elseif ($this->shouldOverwriteConfig()) {
            $this->info('Overwriting configuration file...');
            $this->publishConfiguration($force = true);
        } else {
            $this->info('Existing configuration was not overwritten');
        }

        $this->info('Finish configuration!');

        $this->info('Publishing migration...');

        if ($this->shouldRunMigrations()) {
            $this->publishMigration();
        }

        $this->info('Finish migration!');

        $this->runMigrations();

        $this->info('Installed SPPackage');
    }

    private function configExists(string $fileName): bool
    {
        return File::exists(config_path($fileName));
    }

    /**
     * @param  bool|false  $forcePublish
     */
    private function publishConfiguration($forcePublish = false): void
    {
        $params = self::VENDOR_PUBLISH_CONFIG_PARAMS;

        if ($forcePublish === true) {
            $params['--force'] = '';
        }

        $this->call('vendor:publish', $params);
    }

    /**
     * Should overwrite config file.
     */
    private function shouldOverwriteConfig(): bool
    {
        return $this->confirm('Config file already exists. Do you want to overwrite it?', false);
    }

    private function shouldRunMigrations(): bool
    {
        return $this->confirm('Run migrations of SP package? If you have already done this step, do not do it again!');
    }

    private function publishMigration(): void
    {
        $this->call('vendor:publish', self::VENDOR_PUBLISH_MIGRATION_PARAMS);
    }

    private function runMigrations()
    {
        $this->info('Run migrations.');
        $this->call('migrate');
        $this->info('Finish migrations.');
    }
}
