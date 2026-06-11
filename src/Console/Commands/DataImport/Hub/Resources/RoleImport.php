<?php

namespace BildVitta\SpHub\Console\Commands\DataImport\Hub\Resources;

use stdClass;

class RoleImport
{
    public function import(stdClass $role): void
    {
        $roleModelFromConfig = app(config('permission.models.role'));

        $roleModel = $roleModelFromConfig::where('uuid', $role->uuid)
            ->first();

        if (! $roleModel) {
            $roleModel = new $roleModelFromConfig();
        }

        $roleModel->uuid = $role->uuid;
        $roleModel->name = $role->name;
        $roleModel->description = $role->description;
        $roleModel->guard_name = 'web';
        $roleModel->hub_company_id = $this->getCompanyId($role->hub_company_uuid);
        $roleModel->has_all_real_estate_developments = $role->has_all_real_estate_developments;
        $roleModel->is_post_construction = $role->is_post_construction ?? 0;
        $roleModel->created_at = $role->created_at;
        $roleModel->updated_at = $role->updated_at;
        $roleModel->save();

        $dbHubUserCompanies = app(DbHubRole::class);
        $permissions = collect($dbHubUserCompanies->getRolePermissions($role->id))->pluck('name')->toArray();
        $roleModel->syncPermissions($permissions);
    }

    private function getCompanyId(?string $hubCompanyUuid): ?int
    {
        if ($hubCompanyUuid) {
            $companyClass = config('hub.model_company');
            $hubCompany = $companyClass::withTrashed()
                ->where('uuid', $hubCompanyUuid)
                ->first();
            if ($hubCompany) {
                return $hubCompany->id;
            }
        }

        return null;
    }
}
