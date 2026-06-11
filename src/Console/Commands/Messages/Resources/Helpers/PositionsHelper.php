<?php

namespace BildVitta\SpHub\Console\Commands\Messages\Resources\Helpers;

use stdClass;

trait PositionsHelper
{
    private function positionCreateOrUpdate(stdClass $message): void
    {
        $companyClass = config('hub.model_company');
        $positionClass = config('hub.model_position');

        $positionModel = $positionClass::withTrashed()
            ->where('uuid', $message->uuid)
            ->first();
        if (! $positionModel) {
            $positionModel = new $positionClass();
        }

        $positionModel->uuid = $message->uuid;
        $positionModel->name = $message->name;
        $positionModel->parent_position_id = $positionClass::where('uuid', $message->parent_position_uuid)->first()?->id;
        $positionModel->company_id = $companyClass::where('uuid', $message->company_uuid)->first()->id;

        $positionModel->save();
    }

    private function positionDelete(stdClass $message): void
    {
        $positionClass = config('hub.model_position');
        $positionClass::where('uuid', $message->uuid)->delete();
    }
}
