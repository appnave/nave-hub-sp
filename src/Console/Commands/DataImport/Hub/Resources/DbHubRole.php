<?php

namespace BildVitta\SpHub\Console\Commands\DataImport\Hub\Resources;

use Illuminate\Support\Facades\DB;

class DbHubRole
{
    public function totalRecords(): int
    {
        $query = 'SELECT count(r.id) as total FROM roles r';
        $result = DB::connection('sp_hub')->select($query);

        return (int) $result[0]->total;
    }

    public function getRoles(int $limit, int $offset): array
    {
        $query = 'SELECT r.*, c.uuid AS hub_company_uuid
            FROM roles r
            LEFT JOIN companies c ON c.id = r.company_id
            LIMIT :limit
            OFFSET :offset';

        return DB::connection('sp_hub')->select($query, [
            'limit'  => $limit,
            'offset' => $offset,
        ]);
    }

    public function getRolePermissions(int $roleId): array
    {
        $slug = config('app.slug');
        $query = 'SELECT p.name
            FROM role_has_permissions rp
            JOIN permissions p ON p.id = rp.permission_id
            LEFT JOIN permissions_project pp on pp.id = p.project_id
            WHERE pp.slug = :slug
            AND rp.role_id = :role_id';

        return DB::connection('sp_hub')->select($query, [
            'role_id' => $roleId,
            'slug'    => $slug,
        ]);
    }
}
