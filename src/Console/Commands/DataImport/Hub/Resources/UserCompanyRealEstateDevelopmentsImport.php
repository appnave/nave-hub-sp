<?php

namespace BildVitta\SpHub\Console\Commands\DataImport\Hub\Resources;

use stdClass;

class UserCompanyRealEstateDevelopmentsImport
{
    /**
     * @param  stdClass  $user
     */
    public function import(stdClass $userCompany): void
    {
        $userCompanyClass = config('hub.model_user_company_real_estate_development');
        $userCompanyModel = $userCompanyClass::where('user_company_id', $this->getUserCompanyId($userCompany->user_company_uuid))
            ->first();
        if (! $userCompanyModel) {
            $userCompanyModel = new $userCompanyClass();
        }

        $userCompanyModel->user_company_id = $this->getUserCompanyId($userCompany->user_company_uuid);
        $userCompanyModel->real_estate_development_uuid = $userCompany->real_estate_development_uuid;

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
        $companyLinkClass = config('hub.model_user_company_real_estate_development');
        $companyLinkWithDuplicatedEmail = $companyLinkClass::where('user_company_id', $userCompany->user_company_uuid)
            ->first();
        if ($companyLinkWithDuplicatedEmail) {
            return;
        }
    }
}
