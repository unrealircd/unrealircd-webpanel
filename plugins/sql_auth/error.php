<?php

require_once "../../common.php";
require_once "../../header.php";

if ($_GET['errno'] == 1)
{
    Message::Fail("Looks like your SQL tables haven't been set up yet", 
                    "SQL_Auth needs to create tables for users and permissions. Is that okay?<br>",
                    "<form method=\"post\" action=\"index.php\">
                    <button type=\"submit\" id=\"sql_setup\" name=\"sql_setup\" class=\"text-right btn btn-primary\" value=\"add_tables\">Yes, set up tables</button>
                    </form>");
}

else if (!$_GET['errno'])
{
    echo "<h3>Uh oh! Looks like there was an error.</h3><h4>That's all we know.</h4>";
    echo "Here's a cute chihuahua enjoying a chest rub to cheer you up about it<br>";
    echo "<img width=\"250\" height=\"300\" src=\"https://i.ibb.co/WtvNbkC/20220731-160824.jpg\">";
}
?>