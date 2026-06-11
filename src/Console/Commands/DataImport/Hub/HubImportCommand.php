<?php

namespace BildVitta\SpHub\Console\Commands\DataImport\Hub;

use BildVitta\SpHub\Console\Commands\DataImport\Hub\Jobs\HubImportJob;
use BildVitta\SpHub\Models\Worker;
use Illuminate\Console\Command;

class HubImportCommand extends Command
{
    /**
     * @var string
     */
    public const WORKER_TYPE = 'sp-hub.dataimport';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dataimport:hub {--select=500} {--offset=0} {--tables=brands,companies,users,positions,permissions,roles,user_companies,user_company_parent_positions,user_company_real_estate_developments}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Call init sync users in database';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting import');

        $selectLimit = 500;
        if ($optionSelect = $this->option('select')) {
            $selectLimit = (int) $optionSelect;
        }

        $offset = 0;
        if ($optionOffset = $this->option('offset')) {
            $offset = (int) $optionOffset;
        }

        $tableIndex = 0;
        $tables = explode(',', $this->option('tables'));

        $worker = new Worker();
        $worker->type = self::WORKER_TYPE;
        $worker->status = Worker::STATUS_CREATED;
        $worker->schedule = now();
        $worker->payload = [
            'limit'       => $selectLimit,
            'offset'      => $offset,
            'total'       => null,
            'table_index' => $tableIndex,
            'tables'      => $tables,
        ];
        $worker->save();

        HubImportJob::dispatch($worker->id);

        $this->info('Worker type: '.self::WORKER_TYPE);
        $this->info('Job started, command execution ended');

        return 0;
    }
}
