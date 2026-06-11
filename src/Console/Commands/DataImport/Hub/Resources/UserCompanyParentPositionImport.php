<?php

namespace BildVitta\SpHub\Console\Commands\DataImport\Hub\Resources;

use stdClass;

class UserCompanyParentPositionImport
{
    /**
     * @param  stdClass  $user
     */
    public function import(stdClass $userCompany): void
    {
        $userCompanyClass = config('hub.model_user_company_parent_position');
        $userCompanyModel = $userCompanyClass::withTrashed()
            ->where('user_company_id', $this->getUserCompanyId($userCompany->user_company_uuid))
            ->where('user_company_parent_id', $this->getUserCompanyId($userCompany->user_company_parent_uuid))
            ->first();
        if (! $userCompanyModel) {
            $userCompanyModel = new $userCompanyClass();
        }

        $userCompanyModel->user_company_id = $this->getUserCompanyId($userCompany->user_company_uuid);
        $userCompanyModel->user_company_parent_id = $this->getUserCompanyId($userCompany->user_company_parent_uuid);
        $userCompanyModel->deleted_at = $userCompany->deleted_at;

        $this->checkExistingUserCompany($userCompany);

        $userCompanyModel->save();
    }

    private function getUserCompanyId($userCompanyUuid)
    {
        if ($userCompanyUuid) {
            $userClass = config('hub.model_user_company');
            $hubUserCompany = $userClass::withTrashed()
                ->where('uuid', $userCompanyUuid)
                ->first();

            if ($hubUserCompany) {
                return $hubUserCompany->id;
            }
        }

        return null;
    }

    /**
     * @param  string  $hubUuid
     */
    private function checkExistingUserCompany(stdClass $userCompany): void
    {
        $companyLinkClass = config('hub.model_user_company_parent_position');
        $companyLinkWithDuplicatedEmail = $companyLinkClass::withTrashed()
            ->where('user_company_id', $userCompany->user_company_uuid)
            ->where('user_company_parent_id', $userCompany->user_company_parent_uuid)
            ->first();
        if ($companyLinkWithDuplicatedEmail) {
            return;
        }
    }
}
