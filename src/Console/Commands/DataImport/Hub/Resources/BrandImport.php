<?php

namespace BildVitta\SpHub\Console\Commands\DataImport\Hub\Resources;

class BrandImport
{
    public function import(\stdClass $brand): void
    {
        $brandModelFromConfig = app(config('hub.model_brand'));

        $brandModel = $brandModelFromConfig::where('uuid', $brand->uuid)
            ->first();

        if (! $brandModel) {
            $brandModel = new $brandModelFromConfig();
        }

        $brandModel->uuid = $brand->uuid;
        $brandModel->name = $brand->name;
        $brandModel->main_company_id = $this->getCompanyId($brand->main_company_uuid);
        $brandModel->created_at = $brand->created_at;
        $brandModel->updated_at = $brand->updated_at;
        $brandModel->deleted_at = $brand->deleted_at;

        $brandModel->save();
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
