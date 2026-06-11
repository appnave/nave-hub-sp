<?php

namespace BildVitta\SpHub\Console\Commands\DataImport\Hub\Resources;

use Illuminate\Support\Facades\DB;

class DbHubUserCompanyRealEstateDevelopment
{
    public function totalRecords(): int
    {
        $query = 'SELECT count(1) as total FROM user_company_real_estate_developments';
        $result = DB::connection('sp_hub')->select($query);

        return (int) $result[0]->total;
    }

    public function getUserCompaniesRealEstateDevelopments(int $limit, int $offset): array
    {
        $query = "SELECT user_company.uuid as user_company_uuid, ucred.*
            FROM user_company_real_estate_developments ucred
            INNER JOIN user_companies user_company on user_company.id = ucred.linkable_id
            WHERE ucred.linkable_type = 'App\\Models\\UserCompany'
            ORDER BY user_company_uuid
            LIMIT :limit
            OFFSET :offset";

        return DB::connection('sp_hub')->select($query, [
            'limit'  => $limit,
            'offset' => $offset,
        ]);
    }
}
