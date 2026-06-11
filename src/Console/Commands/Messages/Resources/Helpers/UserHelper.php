<?php

namespace BildVitta\SpHub\Console\Commands\Messages\Resources\Helpers;

use BildVitta\SpHub\Events\Users\UserUpdated;
use BildVitta\SpHub\Models\HubCompany;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use stdClass;

trait UserHelper
{
    use UserExtraFields;

    private function userCreateOrUpdate(stdClass $message): void
    {
        $modelUser = config('sp-hub.model_user');
        if (! $user = $modelUser::withTrashed()->where('hub_uuid', $message->uuid)->first()) {
            $user = new $modelUser();
            $user->hub_uuid = $message->uuid;
            $user->password = Hash::make(Str::random(16));
        }
        $user->name = $message->name;
        $user->email = $message->email;
        $user->avatar = $message->avatar;

        $user->created_at = $message->created_at;
        $user->updated_at = $message->updated_at;
        $user->deleted_at = $message->deleted_at;

        $user->company_id = $this->getCompanyId($message->company_uuid);
        $user->main_company_id = $this->getCompanyId($message->main_company_uuid);
        $user->is_superuser = $message->is_superuser;
        $user->is_active = $message->is_active;

        if ($this->userHasExtraFields($user->getFillable())) {
            $user->document = $message->document;
            $user->address = $message->address;
            $user->street_number = $message->street_number;
            $user->complement = $message->complement;
            $user->city = $message->city;
            $user->state = $message->state;
            $user->postal_code = $message->postal_code;
        }

        $this->handleUserWithSameEmailAndDifferentUUID($message->email, $message->uuid);

        $user->save();

        if (config('app.slug')) {
            $appSlug = config('app.slug');
            $this->updatePermissions($user, $message->user_permissions->$appSlug);
        }

        if (config('sp-hub.events.user_updated')) {
            event(new UserUpdated($user->hub_uuid));
        }
    }

    private function updatePermissions($user, $userPermissions)
    {
        $userPermissions = (array) $userPermissions;
        $permissionsArray = $this->userPermissionsToArray($userPermissions);

        $this->clearPermissionsCache();

        $localPermissions = Permission::toBase()->whereIn('name', $permissionsArray)
            ->orderBy('name')->get('name')->pluck('name')->toArray();

        $permissionsDiff = array_diff($permissionsArray, $localPermissions);
        $permissionsInsert = [];

        foreach ($permissionsDiff as $permission) {
            $permissionsInsert[] = ['name' => $permission, 'guard_name' => 'web'];
        }

        if (! empty($permissionsInsert)) {
            Permission::insert($permissionsInsert);
        }

        $userLocalPermissions = $user->permissions->pluck('name')->toArray();
        $userPermissionsDiff = array_diff($permissionsArray, $userLocalPermissions);
        $userLocalPermissionsDiff = array_diff($userLocalPermissions, $permissionsArray);

        if (! empty($userPermissionsDiff) || ! empty($userLocalPermissionsDiff)) {
            $user->syncPermissions(...collect($permissionsArray)->toArray());
            $user->refresh();
        }
    }

    private function clearPermissionsCache()
    {
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function userPermissionsToArray($userPermissions): array
    {
        $permissionsArray = [];
        foreach ($userPermissions as $key => $value) {
            if (! is_array($value)) {
                $permissionsArray[] = "$key.$value";

                continue;
            }
            foreach ($value as $array) {
                $permissionsArray[] = "$key.$array";
            }
        }

        return $permissionsArray;
    }

    private function userDelete(stdClass $message): void
    {
        $modelUser = config('sp-hub.model_user');
        $modelUser::where('hub_uuid', $message->uuid)->delete();
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

    private function handleUserWithSameEmailAndDifferentUUID(?string $email, ?string $hubUuid): void
    {
        if (empty($email) || empty($hubUuid)) {
            return;
        }

        $userClass = config('sp-hub.model_user');
        $usersWithSameEmail = $userClass::withTrashed()
            ->where('hub_uuid', '!=', $hubUuid)
            ->where('email', $email)
            ->get();
        foreach ($usersWithSameEmail as $userWithSameEmail) {
            $userWithSameEmail->email = sprintf('duplicated_%s|%s', Str::lower(Str::random(6)), $email);
            $userWithSameEmail->save();
        }
    }
}
