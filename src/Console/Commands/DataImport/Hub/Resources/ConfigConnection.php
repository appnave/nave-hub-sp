<?php

namespace BildVitta\SpHub\Console\Commands\DataImport\Hub\Resources;

trait ConfigConnection
{
    private function configConnection(): void
    {
        config([
            'database.connections.sp_hub' => [
                'driver'         => 'mysql',
                'host'           => config('sp-hub.db.host'),
                'port'           => config('sp-hub.db.port'),
                'database'       => config('sp-hub.db.database'),
                'username'       => config('sp-hub.db.username'),
                'password'       => config('sp-hub.db.password'),
                'unix_socket'    => env('DB_SOCKET', ''),
                'charset'        => 'utf8mb4',
                'collation'      => 'utf8mb4_unicode_ci',
                'prefix'         => '',
                'prefix_indexes' => true,
                'strict'         => true,
                'engine'         => null,
                'options'        => [],
            ],
        ]);
    }
}
