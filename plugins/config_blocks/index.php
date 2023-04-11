<?php

require_once "../../common.php";
if (!current_user_can(PERMISSION_CONFIG_BLOCKS))
{
    header("Location: ".get_config("base_url"));
    die();
}
require_once "../../header.php";

?>

<h4>Configuration Blocks</h4>


<?php require_once("../footer.php");