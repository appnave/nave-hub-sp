<?php

namespace BildVitta\SpHub\Console\Commands\DataImport\Hub\Resources;

use Illuminate\Support\Facades\DB;

class DbHubPermission
{
    public function totalRecords(): int
    {
        $slug = config('app.slug');
        $query = "SELECT count(p.id) as total
            FROM permissions p
            INNER JOIN permissions_project pp on pp.id = p.project_id
            WHERE pp.slug = '{$slug}'";
        $result = DB::connection('sp_hub')->select($query);

        return (int) $result[0]->total;
    }

    public function getPermissions(int $limit, int $offset): array
    {
        $slug = config('app.slug');
        $query = "SELECT p.*
            FROM permissions p
            INNER JOIN permissions_project pp on pp.id = p.project_id
            WHERE pp.slug = '{$slug}'
            LIMIT :limit
            OFFSET :offset";

        return DB::connection('sp_hub')->select($query, [
            'limit'  => $limit,
            'offset' => $offset,
        ]);
    }
}
