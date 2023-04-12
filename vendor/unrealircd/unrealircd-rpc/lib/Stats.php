<?php

namespace UnrealIRCd;

use Exception;
use stdClass;

class Stats
{

    public Connection $connection;

    public function __construct(Connection $conn)
    {
        $this->connection = $conn;
    }

    /**
     * Get basic statistical information: user counts, channel counts, etc.
     *
     * @return stdClass|array|bool
     */
    public function get(int $object_detail_level=1): stdClass|array|bool
    {
        return $this->connection->query('stats.get', [
            'object_detail_level' => $object_detail_level,
        ]);
    }
}
