<?php

namespace BildVitta\SpHub\Console\Commands\Messages\Resources\Helpers;

use BildVitta\Hub\Entities\HubBrand;
use BildVitta\SpHub\Models\HubCompany;
use stdClass;

trait CompanyHelper
{
    private function companyCreateOrUpdate(stdClass $message): void
    {
        if (! $company = HubCompany::withTrashed()->where('uuid', $message->uuid)->first()) {
            $company = new HubCompany();
            $company->uuid = $message->uuid;
        }
        $company->name = $message->name;
        $company->created_at = $message->created_at;
        $company->updated_at = $message->updated_at;
        $company->deleted_at = $message->deleted_at;
        $company->external_code = $message->external_code;

        if (property_exists($message, 'public_list') && $message->public_list !== null) {
            $company->public_list = $message->public_list;
        }

        if (property_exists($message, 'main_company_uuid')) {
            $company->main_company_id = null;
            if ($message->main_company_uuid) {
                $company->main_company_id = HubCompany::withTrashed()->where('uuid', $message->main_company_uuid)->value('id');
            }
        } else {
            $company->main_company_id = $message->main_company_id ?? null;
        }

        if (property_exists($message, 'brand_uuid')) {
            $company->brand_id = null;
            if ($message->brand_uuid) {
                $company->brand_id = HubBrand::withTrashed()->where('uuid', $message->brand_uuid)->value('id');
            }
        } else {
            $company->brand_id = $message->brand_id ?? null;
        }

        $userModel = app(config('hub.model_user'));
        if ($this->userHasExtraFields($userModel->getFillable())) {
            $company->document = $message->document;
            $company->company_name = $message->company_name;
            $company->address = $message->address;
            $company->street_number = $message->street_number;
            $company->complement = $message->complement;
            $company->city = $message->city;
            $company->state = $message->state;
            $company->postal_code = $message->postal_code;
        }

        $company->save();
    }

    private function companyDelete(stdClass $message): void
    {
        HubCompany::where('uuid', $message->uuid)->delete();
    }
}
