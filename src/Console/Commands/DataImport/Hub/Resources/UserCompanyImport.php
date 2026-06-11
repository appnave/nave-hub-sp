<?php

namespace BildVitta\SpHub\Console\Commands\DataImport\Hub\Resources;

use stdClass;

class UserCompanyImport
{
    /**
     * @param  stdClass  $user
     */
    public function import(stdClass $userCompany): void
    {
        $userCompanyClass = config('hub.model_user_company');
        $userCompanyModel = $userCompanyClass::withTrashed()
            ->where('uuid', $userCompany->uuid)
            ->first();
        if (! $userCompanyModel) {
            $userCompanyModel = new $userCompanyClass();
        }

        $userCompanyModel->uuid = $userCompany->uuid;
        $userCompanyModel->user_id = $this->getUserId($userCompany->hub_user_uuid);
        $userCompanyModel->company_id = $this->getCompanyId($userCompany->hub_company_uuid);
        $userCompanyModel->position_id = $this->getPositionId($userCompany->hub_position_uuid);
        $userCompanyModel->is_seller = $userCompany->is_seller;
        $userCompanyModel->is_post_construction = $userCompany->is_post_construction ?? 0;
        $userCompanyModel->has_all_real_estate_developments = $userCompany->has_all_real_estate_developments;
        $userCompanyModel->has_specific_permissions = $userCompany->has_specific_permissions;
        $userCompanyModel->created_at = $userCompany->created_at;
        $userCompanyModel->updated_at = $userCompany->updated_at;
        $userCompanyModel->deleted_at = $userCompany->deleted_at;

        $this->checkExistingUserCompany($userCompany->uuid);

        $userCompanyModel->save();

        $dbHubUserCompanies = app(DbHubUserCompanies::class);
        $permissions = collect($dbHubUserCompanies->getPermissionsByUserCompanyId($userCompany->id));
        $permissionsModel = collect($dbHubUserCompanies->getPermissionsModelHasRolesByUserCompanyId($userCompany->id));

        $roles = collect($dbHubUserCompanies->getRolesByUserCompanyId($userCompany->id))->pluck('name')->toArray();
        $userCompanyModel->syncRoles($roles);

        $allPermissions = $permissions->merge($permissionsModel)->pluck('name')->toArray();
        $userCompanyModel->syncPermissions($allPermissions);
    }

    /**
     * @param  string|null  $hubPositionUuid
     */
    private function getUserId(?string $hubUserUuid): ?int
    {
        if ($hubUserUuid) {
            $userClass = config('hub.model_user');
            $hubPosition = $userClass::withTrashed()
                ->where('hub_uuid', $hubUserUuid)
                ->first();

            if ($hubPosition) {
                return $hubPosition->id;
            }
        }

        return null;
    }

    /**
     * @param  string|null  $hubPositionUuid
     */
    private function getPositionId(?string $hubParentPositionUuid): ?int
    {
        if ($hubParentPositionUuid) {
            $positionClass = config('hub.model_position');
            $hubPosition = $positionClass::withTrashed()
                ->where('uuid', $hubParentPositionUuid)
                ->first();

            if ($hubPosition) {
                return $hubPosition->id;
            }
        }

        return null;
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

    private function checkExistingUserCompany(string $hubUuid): void
    {
        $companyLinkClass = config('hub.model_user_company');
        $companyLinkWithDuplicatedEmail = $companyLinkClass::withTrashed()
            ->where('uuid', '!=', $hubUuid)
            ->first();
        if ($companyLinkWithDuplicatedEmail) {
            return;
        }
    }
}
