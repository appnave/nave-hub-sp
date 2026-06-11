<?php

namespace BildVitta\SpHub\Console\Commands\DataImport\Hub\Resources;

use Illuminate\Support\Facades\DB;

class DbHubUserCompanies
{
    public function totalRecords(): int
    {
        $query = 'SELECT count(1) as total FROM user_companies';
        $result = DB::connection('sp_hub')->select($query);

        return (int) $result[0]->total;
    }

    public function totalParentsRecords(): int
    {
        $query = 'SELECT count(1) as total FROM user_company_parent_positions';
        $result = DB::connection('sp_hub')->select($query);

        return (int) $result[0]->total;
    }

    public function totalRealEstateDevelopmentsRecords(): int
    {
        $query = 'SELECT count(1) as total FROM user_company_real_estate_developments';
        $result = DB::connection('sp_hub')->select($query);

        return (int) $result[0]->total;
    }

    public function getUserCompaniesRealEstateDevelopmentsPositions(int $limit, int $offset): array
    {
        $query = 'SELECT user_company.uuid as user_company_uuid, ucred.*
            FROM user_company_real_estate_developments ucred
            INNER JOIN user_companies user_company on user_company.id = ucred.user_company_id
            ORDER BY user_company_uuid;
            LIMIT :limit
            OFFSET :offset';

        return DB::connection('sp_hub')->select($query, [
            'limit'  => $limit,
            'offset' => $offset,
        ]);
    }

    public function getUserCompaniesParentPositions(int $limit, int $offset): array
    {
        $query = 'SELECT user_company.uuid as user_company_uuid, user_company_parent.uuid as user_company_parent_uuid, ucpp.*
            FROM user_company_parent_positions ucpp
            INNER JOIN user_companies user_company on user_company.id = ucpp.user_company_id
            INNER JOIN user_companies user_company_parent on user_company_parent.id = ucpp.user_company_parent_id
            ORDER BY user_company_uuid
            LIMIT :limit
            OFFSET :offset';

        return DB::connection('sp_hub')->select($query, [
            'limit'  => $limit,
            'offset' => $offset,
        ]);
    }

    public function getUserCompanies(int $limit, int $offset): array
    {
        $query = 'SELECT companies.uuid AS hub_company_uuid,users.uuid AS hub_user_uuid, positions.uuid AS hub_position_uuid, uc.*
            FROM user_companies uc
            INNER JOIN companies ON uc.company_id = companies.id
            INNER JOIN users ON uc.user_id = users.id
            LEFT JOIN positions ON uc.position_id = positions.id
            LIMIT :limit
            OFFSET :offset';

        return DB::connection('sp_hub')->select($query, [
            'limit'  => $limit,
            'offset' => $offset,
        ]);
    }

    /**
     * @param  int  $userCompanyId
     */
    public function getPermissionsByUserCompanyId($userCompanyId): array
    {
        $slug = config('app.slug');
        $query = "SELECT p.name
            FROM model_has_permissions mhp
            INNER JOIN permissions p ON mhp.permission_id = p.id
            LEFT JOIN permissions_project pp on pp.id = p.project_id
            WHERE pp.slug = '{$slug}'
            AND mhp.model_type = 'App\\\Models\\\UserCompany'
            AND mhp.model_id = {$userCompanyId}";

        return DB::connection('sp_hub')->select($query);
    }

    /**
     * @param  int  $userCompanyId
     */
    public function getPermissionsModelHasRolesByUserCompanyId($userCompanyId): array
    {
        $slug = config('app.slug');
        $query = "SELECT p.name
            FROM model_has_roles mhr
            INNER JOIN role_has_permissions rhp on rhp.role_id = mhr.role_id
            INNER JOIN permissions p ON rhp.permission_id = p.id
            LEFT JOIN permissions_project pp on pp.id = p.project_id
            WHERE pp.slug = '{$slug}'
            AND mhr.model_type = 'App\\\Models\\\UserCompany'
            AND mhr.model_id = {$userCompanyId}";

        return DB::connection('sp_hub')->select($query);
    }

    public function getRolesByUserCompanyId(int $userCompanyId)
    {
        $slug = config('app.slug');
        $query = "SELECT r.name
            FROM model_has_roles mhr
            INNER JOIN roles r on r.id = mhr.role_id
            AND mhr.model_type = 'App\\\Models\\\UserCompany'
            AND mhr.model_id = :model_id";

        return DB::connection('sp_hub')->select($query, [
            'model_id' => $userCompanyId,
        ]);
    }
}
