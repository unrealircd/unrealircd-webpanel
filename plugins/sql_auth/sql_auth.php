<?php

require_once "SQL/sql.php";
require_once "SQL/user.php";

class sql_auth
{
	public $name = "SQLAuth";
	public $author = "Valware";
	public $version = "1.0";
	public $description = "Provides a User Auth and Management Panel with an SQL backend";

	function __construct()
	{
		Hook::func(HOOKTYPE_NAVBAR, 'sql_auth::add_navbar');
		Hook::func(HOOKTYPE_PRE_HEADER, 'sql_auth::session_start');

		if (defined('SQL_DEFAULT_USER')) // we've got a default account
		{
			$lkup = new SQLA_User(SQL_DEFAULT_USER['username']);

			if (!$lkup->id) // doesn't exist, add it with full privileges
			{
				create_new_user(["user_name" => SQL_DEFAULT_USER['username'], "user_pass" => SQL_DEFAULT_USER['password']]);
			}
		}
	}

	public static function add_navbar(&$pages)
	{
		session_start();
		$query = "SELECT * FROM INFORMATION_SCHEMA.TABLES
		WHERE TABLE_TYPE = 'BASE TABLE'
		AND TABLE_NAME = '".SQL_PREFIX."users'";

		$conn = sqlnew();
		$result = $conn->query($query);
		$notifs = 0;
		$link = "";
		if (!$result || !$result->fetchColumn())
		{
			++$notifs;
			$link = "error.php?errno=1";
		}
		$label = ($notifs) ? "<span class=\"position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger\">$notifs</span>" : "";
		$pages["Panel Access$label"] = "plugins/sql_auth/$link";
		if ($_SESSION['id'])
		{
			$pages["Logout"] = "plugins/sql_auth/login.php?logout=true";
		}
	}

	public static function session_start($n)
	{
		session_start();
		if (!isset($_SESSION['id']))
		{
			header("Location: ".BASE_URL."plugins/sql_auth/login.php");
		}
	}

}