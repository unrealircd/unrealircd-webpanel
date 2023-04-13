<?php
/* unreal_irc_servers */

$servers = $rpc->server()->getAll();

try {
    $result = $pdo->query("SELECT 1 FROM " . get_config("mysql::table_prefix") . "irc_servers LIMIT 1");
} catch (\PDOException $e) {
    // We got an exception (table not found)
    $line = "";
    foreach ($servers as $server) {
        // creating the table with the correct columns
        foreach ($server as $key => $value) {
            //$line .= ",";
            if ($key!="server")
            $line .= "`$key` TEXT NOT NULL,";
        }
        break;
    }
    
    $statements = [
        'CREATE TABLE `' . get_config("mysql::table_prefix") . 'irc_servers` (
            '.rtrim($line, ",").'
          ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;'
    ];

    foreach ($statements as $statement) {
        $pdo->exec($statement);
    }
}


/*
print_r($servers);
exit;
*/

$stmt = $pdo->prepare("TRUNCATE TABLE " . get_config("mysql::table_prefix") . "irc_servers");
$stmt->execute();


foreach ($servers as $server) {
    $array1 = array();
    $array2 = array();
    $array3 = array();
    // creating the table with the correct columns
    foreach ($server as $key => $value) {
        if ($key!="server") {
            array_push($array1, $key);
            array_push($array2, ":".$key);
            $array3[$key] = "$value";
        }
    }
    $prep = $pdo->prepare("INSERT INTO " . get_config("mysql::table_prefix") . "irc_servers (".ltrim(implode(", ",$array1), ",").") 
    VALUES (".ltrim(implode(",",$array2), ",").")");
    $prep->execute($array3);
}
