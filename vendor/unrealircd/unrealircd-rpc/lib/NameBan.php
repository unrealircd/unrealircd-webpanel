<?php

namespace UnrealIRCd;

use Exception;
use stdClass;

class NameBan
{

    public Connection $connection;

    public function __construct(Connection $conn)
    {
        $this->connection = $conn;
    }

    /**
     * Add a name ban (QLine).
     *
     * @param string  $name
     * @param string $reason
     * @param string $duration Optional
     * @param string $set_by Optional
     * @return stdClass|array|bool
     * @throws Exception
     */
    public function add(string $name, string $reason, string $duration = NULL, $set_by = NULL): stdClass|array|bool
    {
        $query = [
            'name' => $name,
            'reason' => $reason,
            'duration_string' => $duration ?? '0',
        ];

        if ($set_by)
            $query['set_by'] = $set_by;

        $response = $this->connection->query('name_ban.add', $query);
        if (property_exists($response, 'tkl'))
            return $response->tkl;
        return FALSE;
    }

    /**
     * Delete a ban.
     *
     * @param  string  $name
     * @return stdClass|array|bool
     * @throws Exception
     */
    public function delete(string $name): stdClass|array|bool
    {
        $response = $this->connection->query('name_ban.del', [
            'name' => $name,
        ]);
        if (property_exists($response, 'tkl'))
            return $response->tkl;
        return FALSE;
    }

    /**
     * Return a list of all bans.
     *
     * @return stdClass|array|bool
     * @throws Exception
     */
    public function getAll(): stdClass|array|bool
    {
        $response = $this->connection->query('name_ban.list');

        if (!is_bool($response)) {
            return $response->list;
        }

        throw new Exception('Invalid JSON Response from UnrealIRCd RPC.');
    }

    /**
     * Get a specific ban.
     *
     * @param string $name
     * @return stdClass|array|bool
     * @throws Exception
     */
    public function get(string $name): stdClass|array|bool
    {
        $response = $this->connection->query('name_ban.get', [
            'name' => $name,
        ]);

        if (!is_bool($response)) {
            return $response->tkl;
        }

        throw new Exception('Invalid JSON Response from UnrealIRCd RPC.');
    }
}
