<?php

namespace BildVitta\SpHub\Console\Commands\DataImport\Hub\Resources;

use BildVitta\SpHub\Console\Commands\Messages\Resources\Helpers\UserExtraFields;
use BildVitta\SpHub\Models\HubCompany;
use Illuminate\Support\Str;
use stdClass;

class UserImport
{
    use UserExtraFields;

    public function import(stdClass $user): void
    {
        $userClass = config('sp-hub.model_user');
        $userModel = $userClass::withTrashed()
            ->where('hub_uuid', $user->uuid)
            ->first();
        if (! $userModel) {
            $userModel = new $userClass();
            $userModel->password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
        }
        $userModel->hub_uuid = $user->uuid;
        $userModel->name = $user->name;
        $userModel->email = $user->email;
        $userModel->avatar = $user->avatar;
        $userModel->deleted_at = $user->deleted_at;
        $userModel->is_active = $user->is_active;
        $userModel->is_superuser = $user->is_superuser;
        $userModel->company_id = $this->getCompanyId($user->hub_company_uuid);
        $userModel->main_company_id = $this->getCompanyId($user->hub_main_company_uuid);

        if ($this->userHasExtraFields($userModel->getFillable())) {
            $userModel->document = $user->document;
            $userModel->address = $user->address;
            $userModel->street_number = $user->street_number;
            $userModel->complement = $user->complement;
            $userModel->city = $user->city;
            $userModel->state = $user->state;
            $userModel->postal_code = $user->postal_code;
        }

        $this->checkExistingEmail($user->email, $user->uuid);

        $userModel->save();
    }

    private function getCompanyId(?string $hubCompanyUuid): ?int
    {
        if ($hubCompanyUuid) {
            $hubCompany = HubCompany::withTrashed()
                ->where('uuid', $hubCompanyUuid)
                ->first();
            if ($hubCompany) {
                return $hubCompany->id;
            }
        }

        return null;
    }

    private function checkExistingEmail(string $email, string $hubUuid): void
    {
        $userClass = config('sp-hub.model_user');
        $userWithDuplicatedEmail = $userClass::withTrashed()
            ->where('hub_uuid', '!=', $hubUuid)
            ->where('email', $email)
            ->first();
        if ($userWithDuplicatedEmail) {
            $userWithDuplicatedEmail->email = sprintf('duplicated_%s|%s', Str::lower(Str::random(6)), $email);
            $userWithDuplicatedEmail->save();
        }
    }
}
