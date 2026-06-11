<?php

namespace BildVitta\SpHub\Console\Commands\DataImport\Hub\Jobs;

use BildVitta\SpHub\Console\Commands\DataImport\Hub\Resources\BrandImport;
use BildVitta\SpHub\Console\Commands\DataImport\Hub\Resources\CompanyImport;
use BildVitta\SpHub\Console\Commands\DataImport\Hub\Resources\ConfigConnection;
use BildVitta\SpHub\Console\Commands\DataImport\Hub\Resources\DbHubBrand;
use BildVitta\SpHub\Console\Commands\DataImport\Hub\Resources\DbHubCompany;
use BildVitta\SpHub\Console\Commands\DataImport\Hub\Resources\DbHubPermission;
use BildVitta\SpHub\Console\Commands\DataImport\Hub\Resources\DbHubPositions;
use BildVitta\SpHub\Console\Commands\DataImport\Hub\Resources\DbHubRole;
use BildVitta\SpHub\Console\Commands\DataImport\Hub\Resources\DbHubUser;
use BildVitta\SpHub\Console\Commands\DataImport\Hub\Resources\DbHubUserCompanies;
use BildVitta\SpHub\Console\Commands\DataImport\Hub\Resources\DbHubUserCompanyParentPosition;
use BildVitta\SpHub\Console\Commands\DataImport\Hub\Resources\DbHubUserCompanyRealEstateDevelopment;
use BildVitta\SpHub\Console\Commands\DataImport\Hub\Resources\PermissionImport;
use BildVitta\SpHub\Console\Commands\DataImport\Hub\Resources\PositionImport;
use BildVitta\SpHub\Console\Commands\DataImport\Hub\Resources\RoleImport;
use BildVitta\SpHub\Console\Commands\DataImport\Hub\Resources\UserCompanyImport;
use BildVitta\SpHub\Console\Commands\DataImport\Hub\Resources\UserCompanyParentPositionImport;
use BildVitta\SpHub\Console\Commands\DataImport\Hub\Resources\UserCompanyRealEstateDevelopmentsImport;
use BildVitta\SpHub\Console\Commands\DataImport\Hub\Resources\UserImport;
use BildVitta\SpHub\Models\Worker;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use InvalidArgumentException;
use Throwable;

class HubImportJob implements ShouldQueue
{
    use ConfigConnection;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 3300;

    /**
     * @var int
     */
    public $retryAfter = 60;

    private int $workerId;

    private string $currentTable;

    /**
     * @var Worker
     */
    private $worker;

    public function __construct(int $workerId)
    {
        $this->onQueue('default');
        $this->workerId = $workerId;
    }

    /**
     * Execute the job.
     *
     * @return void
     *
     * @throws InvalidArgumentException
     */
    public function handle()
    {
        if (! $this->worker = Worker::find($this->workerId)) {
            return;
        }
        $this->init();

        switch ($this->currentTable) {
            case 'brands':
                $this->importBrands();
                break;
            case 'companies':
                $this->importCompanies();
                break;
            case 'users':
                $this->importUsers();
                break;
            case 'positions':
                $this->importPositions();
                break;
            case 'permissions':
                $this->importPermissions();
                break;
            case 'roles':
                $this->importRoles();
                break;
            case 'user_companies':
                $this->importUserCompanies();
                break;
            case 'user_company_parent_positions':
                $this->importUserCompanyParentPositions();
                break;
            case 'user_company_real_estate_developments':
                $this->importUserCompanyRealEstateDevelopments();
                break;
            default:
                throw new InvalidArgumentException('Invalid current table');
        }
    }

    private function init(): void
    {
        $this->configConnection();
        $this->updateWorker(['status' => 'in_progress']);
        $this->currentTable = $this->worker->payload->tables[$this->worker->payload->table_index];
    }

    private function importBrands(): void
    {
        $dbHubBrand = app(DbHubBrand::class);
        $brandImport = app(BrandImport::class);
        $payload = $this->worker->payload;

        if (is_null($payload->total)) {
            $payload->total = $dbHubBrand->totalRecords();
            $this->updateWorker(['payload' => $payload]);
        }

        $brands = collect($dbHubBrand->getBrands($payload->limit, $payload->offset));
        foreach ($brands as $brand) {
            $brandImport->import($brand);
        }

        $this->dispatchNextJob();
    }

    private function importPermissions(): void
    {
        $dbHubPermission = app(DbHubPermission::class);
        $permissionImport = app(PermissionImport::class);
        $payload = $this->worker->payload;

        if (is_null($payload->total)) {
            $payload->total = $dbHubPermission->totalRecords();
            $this->updateWorker(['payload' => $payload]);
        }

        $permissions = collect($dbHubPermission->getPermissions($payload->limit, $payload->offset));
        foreach ($permissions as $permission) {
            $permissionImport->import($permission);
        }

        $this->dispatchNextJob();
    }

    private function importRoles(): void
    {
        $dbHubRole = app(DbHubRole::class);
        $roleImport = app(RoleImport::class);
        $payload = $this->worker->payload;

        if (is_null($payload->total)) {
            $payload->total = $dbHubRole->totalRecords();
            $this->updateWorker(['payload' => $payload]);
        }

        $permissions = collect($dbHubRole->getRoles($payload->limit, $payload->offset));
        foreach ($permissions as $permission) {
            $roleImport->import($permission);
        }

        $this->dispatchNextJob();
    }

    private function importUserCompanies(): void
    {
        $dbHubUserCompanies = app(DbHubUserCompanies::class);
        $userCompanyImport = app(UserCompanyImport::class);
        $payload = $this->worker->payload;

        if (is_null($payload->total)) {
            $payload->total = $dbHubUserCompanies->totalRecords();
            $this->updateWorker(['payload' => $payload]);
        }

        $companies = collect($dbHubUserCompanies->getUserCompanies($payload->limit, $payload->offset));
        foreach ($companies as $company) {
            $userCompanyImport->import($company);
        }

        $this->dispatchNextJob();
    }

    private function importUserCompanyParentPositions(): void
    {
        $dbHubUserCompanyParentPosition = app(DbHubUserCompanyParentPosition::class);
        $userCompanyParentPositionImport = app(UserCompanyParentPositionImport::class);
        $payload = $this->worker->payload;

        if (is_null($payload->total)) {
            $payload->total = $dbHubUserCompanyParentPosition->totalRecords();
            $this->updateWorker(['payload' => $payload]);
        }

        $companies = collect($dbHubUserCompanyParentPosition->getUserCompaniesParentPositions($payload->limit, $payload->offset));
        foreach ($companies as $company) {
            $userCompanyParentPositionImport->import($company);
        }

        $this->dispatchNextJob();
    }

    private function importUserCompanyRealEstateDevelopments(): void
    {
        $dbHubUserCompanyParentPosition = app(DbHubUserCompanyRealEstateDevelopment::class);
        $userCompanyParentPositionImport = app(UserCompanyRealEstateDevelopmentsImport::class);
        $payload = $this->worker->payload;

        if (is_null($payload->total)) {
            $payload->total = $dbHubUserCompanyParentPosition->totalRecords();
            $this->updateWorker(['payload' => $payload]);
        }

        $companies = collect($dbHubUserCompanyParentPosition->getUserCompaniesRealEstateDevelopments($payload->limit, $payload->offset));
        foreach ($companies as $company) {
            $userCompanyParentPositionImport->import($company);
        }

        $this->dispatchNextJob();
    }

    private function importPositions(): void
    {
        $dbHubPositions = app(DbHubPositions::class);
        $positionImport = app(PositionImport::class);
        $payload = $this->worker->payload;

        if (is_null($payload->total)) {
            $payload->total = $dbHubPositions->totalRecords();
            $this->updateWorker(['payload' => $payload]);
        }

        $companies = collect($dbHubPositions->getPositions($payload->limit, $payload->offset));
        foreach ($companies as $company) {
            $positionImport->import($company);
        }

        $this->dispatchNextJob();
    }

    private function importCompanies(): void
    {
        $dbHubCompany = app(DbHubCompany::class);
        $companyImport = app(CompanyImport::class);
        $payload = $this->worker->payload;

        if (is_null($payload->total)) {
            $payload->total = $dbHubCompany->totalRecords();
            $this->updateWorker(['payload' => $payload]);
        }

        $companies = collect($dbHubCompany->getCompanies($payload->limit, $payload->offset));
        foreach ($companies as $company) {
            $companyImport->import($company);
        }

        $this->dispatchNextJob();
    }

    private function importUsers(): void
    {
        $dbHubUser = app(DbHubUser::class);
        $userImport = app(UserImport::class);
        $payload = $this->worker->payload;

        if (is_null($payload->total)) {
            $payload->total = $dbHubUser->totalRecords();
            $this->updateWorker(['payload' => $payload]);
        }

        $users = collect($dbHubUser->getUsers($payload->limit, $payload->offset));
        foreach ($users as $user) {
            $userImport->import($user);
        }

        $this->dispatchNextJob();
    }

    private function dispatchNextJob(): void
    {
        if (! $this->worker = Worker::find($this->workerId)) {
            return;
        }
        $payload = $this->worker->payload;
        $nextOffset = $payload->offset + $payload->limit;

        if ($nextOffset < $payload->total) {
            $payload->offset = $nextOffset;
            $this->updateWorker(['payload' => $payload]);
            HubImportJob::dispatch($this->worker->id);
        } else {
            $nextTableIndex = $payload->table_index + 1;
            if (isset($payload->tables[$nextTableIndex])) {
                $payload->table_index = $nextTableIndex;
                $payload->offset = 0;
                $payload->total = null;
                $this->updateWorker(['payload' => $payload]);
                HubImportJob::dispatch($this->worker->id);
            } else {
                unset($payload->table_index);
                unset($payload->offset);
                unset($payload->total);
                $payload->finished_jobs = true;
                $this->updateWorker(['payload' => $payload, 'status' => 'finished']);
            }
        }
    }

    private function updateWorker(array $props): void
    {
        foreach ($props as $key => $value) {
            $this->worker->{$key} = $value;
        }
        $this->worker->created_at = now();
        $this->worker->save();
    }

    public function failed(Throwable $exception): void
    {
        if (! $worker = Worker::find($this->workerId)) {
            return;
        }
        $worker->error = [
            'message' => $exception->getMessage(),
            'file'    => $exception->getFile(),
            'line'    => $exception->getLine(),
            'trace'   => $exception->getTraceAsString(),
        ];
        $worker->status = 'error';
        $worker->save();
    }
}
