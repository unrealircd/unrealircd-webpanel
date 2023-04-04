<?php

require_once "../../common.php";
require_once "../../header.php";

if (!isset($_POST) || empty($_POST))
{
    ?>

    <h4>SQL Setup</h4>
    In order to use SQL Auth, the relevant SQL tables must be created.<br>
    If, for some reason, that's not what you want, please remove <code>"sql_auth"</code> from the plugins option in your webpanel configuration.<br><br>
    Thanks for using SQL Auth plugin!<br><br>

    <form method="post">
    <button id="createbtn" name="createbtn" class="btn btn-primary" type="submit">Create the tables!</button>
    </form>
    <?php
}
elseif (isset($_POST['createbtn']))
{
    $conn = sqlnew();
    $conn->query("CREATE TABLE IF NOT EXISTS " . SQL_PREFIX . "users (
        user_id int AUTO_INCREMENT NOT NULL,
        user_name VARCHAR(255) NOT NULL,
        user_pass VARCHAR(255) NOT NULL,
        user_email VARCHAR(255),
        user_fname VARCHAR(255),
        user_lname VARCHAR(255),
        user_bio VARCHAR(255),
        created VARCHAR(255),
        PRIMARY KEY (user_id)
    )");

    /**
     * Patch for beta users
     * This adds the email column to existing tables without it
    */
    $columns = $conn->query("SHOW COLUMNS FROM " . SQL_PREFIX . "users");
    $column_names = array();
    $c = $columns->fetchAll();

    foreach($c as $column) {
        $column_names[] = $column['Field'];
    }
    $column_exists = in_array("user_email", $column_names);
    if (!$column_exists) {
        $conn->query("ALTER TABLE " . SQL_PREFIX . "users ADD COLUMN user_email varchar(255)");
    }

    /**
     * Another patch for beta users
     * This changes the size of the meta_value so we can store more
     */
    
    $conn->query("CREATE TABLE IF NOT EXISTS " . SQL_PREFIX . "user_meta (
        meta_id int AUTO_INCREMENT NOT NULL,
        user_id int NOT NULL,
        meta_key VARCHAR(255) NOT NULL,
        meta_value VARCHAR(255),
        PRIMARY KEY (meta_id)
    )");
    $conn->query("CREATE TABLE IF NOT EXISTS " . SQL_PREFIX . "auth_settings (
        id int AUTO_INCREMENT NOT NULL,
        setting_key VARCHAR(255) NOT NULL,
        setting_value VARCHAR(255),
        PRIMARY KEY (id)
    )");
    $conn->query("CREATE TABLE IF NOT EXISTS " . SQL_PREFIX . "fail2ban (
        id int AUTO_INCREMENT NOT NULL,
        ip VARCHAR(255) NOT NULL,
        count VARCHAR(255),
        PRIMARY KEY (id)
    )");
    $c = [];
    if (($columns = $conn->query("SHOW COLUMNS FROM ".SQL_PREFIX."user_meta")));
        $c = $columns->fetchAll();
    if (!empty($c))
        $conn->query("ALTER TABLE `".SQL_PREFIX."user_meta` CHANGE `meta_value` `meta_value` VARCHAR(5000) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NULL DEFAULT NULL");


    new AuthSettings();

    Message::Success("Congratulations, you're all ready to go!");
    ?>
    <a class="btn btn-primary" href="<?php echo BASE_URL; ?>">Take me home!</a>
    <a class="btn btn-warning" href="<?php echo BASE_URL."settings"; ?>">Settings</a>
    <?php
}
require_once "../../footer.php";