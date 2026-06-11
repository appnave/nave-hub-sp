<?php

namespace BildVitta\SpHub\Console\Commands\DataImport\Hub\Resources;

use Illuminate\Support\Facades\DB;

class DbHubUser
{
    public function totalRecords(): int
    {
        $query = 'SELECT count(1) as total FROM users';
        $result = DB::connection('sp_hub')->select($query);

        return (int) $result[0]->total;
    }

    public function getUsers(int $limit, int $offset): array
    {
        $query = 'SELECT users.*, companies.uuid AS hub_company_uuid, main_companies.uuid AS hub_main_company_uuid
            FROM users
            LEFT JOIN companies
            ON users.company_id = companies.id
            LEFT JOIN companies AS main_companies
            ON users.main_company_id = main_companies.id
            LIMIT :limit
            OFFSET :offset';

        return DB::connection('sp_hub')->select($query, [
            'limit'  => $limit,
            'offset' => $offset,
        ]);
    }
}
