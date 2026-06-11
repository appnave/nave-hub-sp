<?php

namespace BildVitta\SpHub\Console\Commands\DataImport\Hub\Resources;

use stdClass;

class PositionImport
{
    /**
     * @param  stdClass  $user
     */
    public function import(stdClass $position): void
    {
        $positionClass = config('hub.model_position');
        $positionModel = $positionClass::withTrashed()
            ->where('uuid', $position->uuid)
            ->first();
        if (! $positionModel) {
            $positionModel = new $positionClass();
        }
        $positionModel->uuid = $position->uuid;
        $positionModel->name = $position->name;
        $positionModel->parent_position_id = $this->getParentPositionId($position->parent_position_uuid);
        $positionModel->company_id = $this->getCompanyId($position->hub_company_uuid);
        $positionModel->deleted_at = $position->deleted_at;

        $this->checkExistingPosition($position->uuid);

        $positionModel->save();
    }

    /**
     * @param  string|null  $hubPositionUuid
     */
    private function getParentPositionId(?string $hubParentPositionUuid): ?int
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

    private function checkExistingPosition(string $hubUuid): void
    {
        $positionClass = config('hub.model_position');
        $positionWithDuplicatedEmail = $positionClass::withTrashed()
            ->where('uuid', '!=', $hubUuid)
            ->first();
        if ($positionWithDuplicatedEmail) {
            return;
        }
    }
}
