<?php
/* unreal_irc_channels */

$channels = $rpc->channel()->getAll();

try {
    $result = $pdo->query("SELECT 1 FROM " . get_config("mysql::table_prefix") . "irc_channels LIMIT 1");
} catch (\PDOException $e) {
    // We got an exception (table not found)

    $line = "";
    foreach ($channels as $channel) {
        // creating the table with the correct columns
        foreach ($channel as $key => $value) {
            $line .= ",";
            $line .= "`$key` TEXT NOT NULL";
        }
        break;

    }
    $statements = [
        'CREATE TABLE `unreal_irc_channels` (
            `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY
            '.$line.'
          ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;'
    ];

    foreach ($statements as $statement) {
        $pdo->exec($statement);
    }
}


/*
print_r($channels);
exit;
*/

$stmt = $pdo->prepare("TRUNCATE TABLE " . get_config("mysql::table_prefix") . "irc_channels");
$stmt->execute();


foreach ($channels as $channel) {
    $array1 = array();
    $array2 = array();
    $array3 = array();
    $array3["id"] = "";
    // creating the table with the correct columns
    foreach ($channel as $key => $value) {
        array_push($array1, $key);
        array_push($array2, ":".$key);
        $array3[$key] = "$value";
    }
    $prep = $pdo->prepare("INSERT INTO " . get_config("mysql::table_prefix") . "irc_channels (id, ".implode(", ",$array1).") 
    VALUES (:id, ".implode(", ",$array2).")");
    $prep->execute($array3);
}
