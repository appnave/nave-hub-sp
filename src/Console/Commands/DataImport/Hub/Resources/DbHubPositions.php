<?php

namespace BildVitta\SpHub\Console\Commands\DataImport\Hub\Resources;

use Illuminate\Support\Facades\DB;

class DbHubPositions
{
    public function totalRecords(): int
    {
        $query = 'SELECT count(1) as total FROM positions';
        $result = DB::connection('sp_hub')->select($query);

        return (int) $result[0]->total;
    }

    public function getPositions(int $limit, int $offset): array
    {
        $query = 'SELECT positions.*, parent_position.uuid AS parent_position_uuid, companies.uuid AS hub_company_uuid
            FROM positions
            LEFT JOIN positions as parent_position
            ON parent_position.id = positions.parent_position_id
            INNER JOIN companies
            ON positions.company_id = companies.id
            LIMIT :limit
            OFFSET :offset';

        return DB::connection('sp_hub')->select($query, [
            'limit'  => $limit,
            'offset' => $offset,
        ]);
    }
}
