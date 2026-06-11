<?php

namespace BildVitta\SpHub\Console\Commands\DataImport\Hub\Resources;

use Illuminate\Support\Facades\DB;

class DbHubBrand
{
    public function totalRecords(): int
    {
        $query = 'SELECT count(1) as total FROM brands';
        $result = DB::connection('sp_hub')->select($query);

        return (int) $result[0]->total;
    }

    public function getBrands(int $limit, int $offset): array
    {
        $query = 'SELECT b.*, c.uuid as main_company_uuid FROM brands b LEFT JOIN companies c ON b.main_company_id = c.id LIMIT :limit OFFSET :offset';

        return DB::connection('sp_hub')->select($query, [
            'limit'  => $limit,
            'offset' => $offset,
        ]);
    }
}
