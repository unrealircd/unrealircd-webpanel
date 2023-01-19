<?php

class AuthSettings {

    public $list = [];

    function __construct()
    {
        $conn = sqlnew();
        $query = "SELECT * FROM " . SQL_PREFIX . "auth_settings";
        $result = $conn->query($query);
        while ($row = $result->fetch())
        {
            $this->list[$row['setting_key']] = $row['setting_value'];
        }
    }
}