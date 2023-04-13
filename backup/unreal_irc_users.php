<?php
/* unreal_irc_users */
try {
    $result = $pdo->query("SELECT 1 FROM " . get_config("mysql::table_prefix") . "irc_users LIMIT 1");
} catch (\PDOException $e) {
    // We got an exception (table not found)
    $statements = [
        'CREATE TABLE `' . get_config("mysql::table_prefix") . 'irc_users` (
            `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `id_user` varchar(255) NOT NULL,
            `name` varchar(255) NOT NULL,
            `username` varchar(255) NOT NULL,
            `realname` varchar(255) NOT NULL,
            `vhost` varchar(255) NOT NULL,
            `account` varchar(255) NOT NULL,
            `reputation` varchar(255) NOT NULL,
            `hostname` varchar(255) NOT NULL,
            `ip` varchar(255) NOT NULL,
            `country_code` varchar(2) NOT NULL,
            `connected_since` varchar(255) NOT NULL,
            `idle_since` varchar(255) NOT NULL,
            `idle` varchar(255) NOT NULL,
            `modes` varchar(255) NOT NULL
          ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;'
    ];

    foreach ($statements as $statement) {
        $pdo->exec($statement);
    }
}

$users = $rpc->user()->getAll();

/*
print_r($users);
exit;
*/

$stmt = $pdo->prepare("TRUNCATE TABLE " . get_config("mysql::table_prefix") . "irc_users");
$stmt->execute();

foreach ($users as $user) {
    //echo "Name : " . $user->name . "<br>";
    $name               = $user->name ?? '';
    $id                 = $user->id ?? '';
    $username           = $user->user->username ?? '';
    $realname           = $user->user->realname ?? '';
    $vhost              = $user->user->vhost ?? '';
    $account            = $user->user->account ?? '';
    $reputation         = $user->user->reputation ?? '';
    $hostname           = $user->hostname ?? '';
    $ip                 = $user->ip ?? '';
    $country_code       = $user->geoip->country_code ?? '';
    $connected_since    = $user->connected_since ?? '';
    $idle_since         = $user->idle_since ?? '';
    $idle               = abs(strtotime($connected_since) - strtotime($idle_since));
    $modes              = $user->user->modes ?? '';
    $prep = $pdo->prepare("INSERT INTO " . get_config("mysql::table_prefix") . "irc_users (id, id_user, name, username, realname, vhost, account, reputation, hostname, ip, country_code, connected_since, idle_since, idle, modes) 
    VALUES (:id, :id_user, :name, :username, :realname, :vhost, :account, :reputation, :hostname, :ip, :country_code, :connected_since, :idle_since, :idle, :modes)");
    $prep->execute([
        "id" => '',
        "id_user" => $id,
        "name" => $name, 
        "username" => $username, 
        "realname" => $realname, 
        "vhost" => $vhost, 
        "account" => $account, 
        "reputation" => $reputation, 
        "hostname" => $hostname, 
        "ip" => $ip, 
        "country_code" => $country_code, 
        "connected_since" => $connected_since, 
        "idle_since" => $idle_since,
        "idle" => $idle,
        "modes" => $modes
    ]);
  }
