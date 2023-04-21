<?php


class SQLAuthSettings {

    public $list = [];

    function __construct()
    {
        $conn = sqlnew();
        $query = "SELECT * FROM " . get_config("mysql::table_prefix") . "auth_settings";
        $result = $conn->query($query);
        while ($row = $result->fetch())
        {
            $this->list[$row['setting_key']] = $row['setting_value'];
        }
    }
    function set($key, $val) : int
    {
        $conn = sqlnew();
        $stmt = $conn->prepare("SELECT * FROM " . get_config("mysql::table_prefix") . "auth_settings WHERE option_name = :name LIMIT 1");
        $stmt->execute(["name" => $key]);
        if ($stmt->rowCount()) // if it already exists update it
            $stmt = $conn->prepare("UPDATE " . get_config("mysql::table_prefix") . "auth_settings SET option_value = :value WHERE option_name = :name");
            
        else // otherwise create it
            $stmt = $conn->prepare("INSERT INTO " . get_config("mysql::table_prefix") . "auth_settings (option_name, option_value) VALUES (:name, :value)");

        // make sure it's there/correct
        $stmt->execute(["name" => $key, "value" => $val]);
        $stmt = $conn->prepare("SELECT * FROM " . get_config("mysql::table_prefix") . "auth_settings WHERE option_name = :name LIMIT 1");
        $stmt->execute(["name" => $key]);
        return $stmt->rowCount(); // return 1 or 0 bool-like int
    }
}
