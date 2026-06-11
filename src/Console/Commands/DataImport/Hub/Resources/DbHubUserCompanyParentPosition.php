<?php

namespace BildVitta\SpHub\Console\Commands\DataImport\Hub\Resources;

use Illuminate\Support\Facades\DB;

class DbHubUserCompanyParentPosition
{
    public function totalRecords(): int
    {
        $query = 'SELECT count(1) as total FROM user_company_parent_positions';
        $result = DB::connection('sp_hub')->select($query);

        return (int) $result[0]->total;
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
}
