<?php

require_once "../../common.php";
?>
<!DOCTYPE html>
<head>
<div class="media">
<div class="media-body">

	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="HandheldFriendly" content="true">

<link href="<?php echo get_config("base_url"); ?>css/unrealircd-admin.css" rel="stylesheet">


 <!-- Latest compiled and minified CSS -->
 <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

<!-- Font Awesome JS -->
<script defer src="https://use.fontawesome.com/releases/v6.2.1/js/solid.js" integrity="sha384-tzzSw1/Vo+0N5UhStP3bvwWPq+uvzCMfrN1fEFe+xBmv1C/AtVX5K0uZtmcHitFZ" crossorigin="anonymous"></script>
<script defer src="https://use.fontawesome.com/releases/v6.2.1/js/fontawesome.js" integrity="sha384-6OIrr52G08NpOFSZdxxz1xdNSndlD4vdcf/q2myIUVO0VsqaGHJsB0RaBE01VTOY" crossorigin="anonymous"></script>

<!-- Font Awesome icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css">
<script src="<?php echo get_config("base_url"); ?>js/unrealircd-admin.js"></script>
<title>UnrealIRCd Panel</title>
<link rel="icon" type="image/x-icon" href="<?php echo get_config("base_url"); ?>img/favicon.ico">
</head>
<body role="document">

<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<!-- Popper.JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js" integrity="sha384-cs/chFZiN24E4KMATLdqdvsezGxaGsi4hLGOzlXwp5UZB1LY//20VyM2taTB4QvJ" crossorigin="anonymous"></script>
<!-- Bootstrap JS -->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/js/bootstrap.min.js" integrity="sha384-uefMccjFJAIv6A+rW+L4AHf99KvxDjWSu1z9VI8SKNVmz4sk7buKt/6v9KI65qnm" crossorigin="anonymous"></script>


<?php
if (!isset($_POST) || empty($_POST))
{
    ?>
    <div class="container">
    <h4>SQL Setup</h4>
    In order to use SQL Auth, the relevant SQL tables must be created.<br>
    If, for some reason, that's not what you want, please remove <code>"sql_auth"</code> from the plugins option in your webpanel configuration.<br><br>
    Thanks for using SQL Auth plugin!<br><br>

    <form method="post">
    <button id="createbtn" name="createbtn" class="btn btn-primary" type="submit">Create the tables!</button>
    </form>
    </div>
    <?php
}
elseif (isset($_POST['createbtn']))
{
    $conn = sqlnew();
    $conn->query("CREATE TABLE IF NOT EXISTS " . get_config("mysql::table_prefix") . "users (
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
    $columns = $conn->query("SHOW COLUMNS FROM " . get_config("mysql::table_prefix") . "users");
    $column_names = array();
    $c = $columns->fetchAll();

    foreach($c as $column) {
        $column_names[] = $column['Field'];
    }
    $column_exists = in_array("user_email", $column_names);
    if (!$column_exists) {
        $conn->query("ALTER TABLE " . get_config("mysql::table_prefix") . "users ADD COLUMN user_email varchar(255)");
    }

    /**
     * Another patch for beta users
     * This changes the size of the meta_value so we can store more
     */
    
    $conn->query("CREATE TABLE IF NOT EXISTS " . get_config("mysql::table_prefix") . "user_meta (
        meta_id int AUTO_INCREMENT NOT NULL,
        user_id int NOT NULL,
        meta_key VARCHAR(255) NOT NULL,
        meta_value VARCHAR(255),
        PRIMARY KEY (meta_id)
    )");
    $conn->query("CREATE TABLE IF NOT EXISTS " . get_config("mysql::table_prefix") . "auth_settings (
        id int AUTO_INCREMENT NOT NULL,
        setting_key VARCHAR(255) NOT NULL,
        setting_value VARCHAR(255),
        PRIMARY KEY (id)
    )");
    $conn->query("CREATE TABLE IF NOT EXISTS " . get_config("mysql::table_prefix") . "fail2ban (
        id int AUTO_INCREMENT NOT NULL,
        ip VARCHAR(255) NOT NULL,
        count VARCHAR(255),
        PRIMARY KEY (id)
    )");
    $c = [];
    if (($columns = $conn->query("SHOW COLUMNS FROM ".get_config("mysql::table_prefix")."user_meta")));
        $c = $columns->fetchAll();
    if (!empty($c))
        $conn->query("ALTER TABLE `".get_config("mysql::table_prefix")."user_meta` CHANGE `meta_value` `meta_value` VARCHAR(5000) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NULL DEFAULT NULL");


    new AuthSettings();

    Message::Success("Congratulations, you're all ready to go!");
    ?>
    <a class="btn btn-primary" href="<?php echo get_config("base_url"); ?>">Take me home!</a>
    <a class="btn btn-warning" href="<?php echo get_config("base_url")."settings"; ?>">Settings</a>
    <?php
}
require_once "../../footer.php";