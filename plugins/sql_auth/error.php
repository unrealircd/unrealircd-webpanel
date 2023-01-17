<?php

require_once "../../common.php";
require_once "../../header.php";

if (!isset($_GET) || !isset($_GET['errno']))
{
    Message::Fail("Uh oh! Something went wrong. We don't know anything else.");
}
elseif ($_GET['errno'] == 1)
{
    Message::Fail("Looks like your SQL tables haven't been set up yet", 
                    "SQL_Auth needs to create tables for users and permissions. Is that okay?<br>",
                    "<form method=\"post\" action=\"index.php\">
                    <button type=\"submit\" id=\"sql_setup\" name=\"sql_setup\" class=\"text-right btn btn-primary\" value=\"add_tables\">Yes, set up tables</button>
                    </form>");
    ?>
    
    <?php
}
?>