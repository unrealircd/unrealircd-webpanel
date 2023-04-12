<?php
/*
    A file named backup_sql.php is used to backup the list of connected users in UnrealIRCd into a MySQL table nommed "unreal_irc_users" which is created automatically if it does not exist.
    This backup is more detailed than the one created by 'anope_user'. 

    To set up the backup, add something like this to your crontab:
    php /home/<account>/unrealircd-webpanel/backup.php
    and run this command every 5 minutes or 1 minute.

    In this file I also wanted to make that creates the following new tables:
    - unreal_irc_top_countries
    - unreal_irc_spamfilter
    - unreal_irc_servers
    - unreal_irc_channels
    - unreal_irc_name_bans
*/

require_once "common.php";
require_once "connection.php";

/* connection sql */
$host = get_config("mysql::host");
$user = get_config("mysql::username");
$pass = get_config("mysql::password");
$db   = get_config("mysql::database");
$charset = 'utf8mb4';

if ($host[0] == "/")
    $host_type = "unix_socket";
else
    $host_type = "host";
$dsn = "mysql:$host_type=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];
try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

try {
    $result = $pdo->query("SELECT 1 FROM " . get_config("mysql::table_prefix") . "irc_users LIMIT 1");
} catch (\PDOException $e) {
    // We got an exception (table not found)
    $statements = [
        'CREATE TABLE `unreal_irc_users` (
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
          ) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;'
    ];

    foreach ($statements as $statement) {
        $pdo->exec($statement);
    }
}


/* unreal_irc_users */
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