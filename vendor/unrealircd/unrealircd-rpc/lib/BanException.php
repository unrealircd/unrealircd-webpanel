<?php

namespace UnrealIRCd;

use Exception;
use stdClass;

class BanException
{

    public Connection $connection;

    public function __construct(Connection $conn)
    {
        $this->connection = $conn;
    }

    /**
     * Add a ban exceptions.
     *
     * @param  string  $user
     * @return stdClass|array|bool
     * @throws Exception
     */
    public function add(string $name, string $types, string $reason, string $set_by = NULL, string $duration = NULL): stdClass|array|bool
    {
        $query = [
            'name' => $name,
            'exception_types' => $types,
            'reason' => $reason,
        ];
        if ($set_by)
            $query['set_by'] = $set_by;

        if ($duration)
            $query['duration_string'] = $duration;
    
        $response = $this->connection->query('server_ban_exception.add', $query);

        if (is_bool($response))
            return false;

        if (property_exists($response, 'tkl'))
            return $response->tkl;
        return FALSE;
    }

    /**
     * Delete a ban exceptions.
     *
     * @param  string  $name
     * @return stdClass|array|bool
     * @throws Exception
     */
    public function delete(string $name): stdClass|array|bool
    {
        $response = $this->connection->query('server_ban_exception.del', [
            'name' => $name,
        ]);

        if (is_bool($response))
            return false;
            
        if (property_exists($response, 'tkl'))
            return $response->tkl;
        return FALSE;
    }

    /**
     * Return a list of all exceptions.
     *
     * @return stdClass|array|bool
     * @throws Exception
     */
    public function getAll(): stdClass|array|bool
    {
        $response = $this->connection->query('server_ban_exception.list');

        if (!is_bool($response)) {
            return $response->list;
        }

        throw new Exception('Invalid JSON Response from UnrealIRCd RPC.');
    }

    /**
     * Get a specific ban exceptions.
     *
     * @return stdClass|array|bool
     * @throws Exception
     */
    public function get(string $name): stdClass|array|bool
    {
        $response = $this->connection->query('server_ban_exception.get', [
            'name' => $name,
        ]);

        if (!is_bool($response)) {
            return $response->tkl;
        }

        return false; // didn't exist
    }
}
