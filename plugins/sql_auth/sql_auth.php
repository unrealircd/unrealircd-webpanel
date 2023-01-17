<?php

require_once "SQL/sql.php";
class sql_auth
{
	public $name = "SQL_Auth";
	public $author = "Valware";
	public $version = "1.0";
	public $description = "Provides a User Auth and Management Panel with an SQL backend";

	function __construct()
	{
		Hook::func(HOOKTYPE_NAVBAR, 'sql_auth::add_navbar'); 
	}

	public static function add_navbar(&$pages)
	{
		$query = "SELECT * FROM INFORMATION_SCHEMA.TABLES
		WHERE TABLE_TYPE = 'BASE TABLE'
		AND TABLE_NAME = '".SQL_PREFIX."users'";

		$conn = sqlnew();
		$result = $conn->query($query);
		$notifs = 0;
		if (!$result || !$result->fetchColumn())
		{
			++$notifs;
			$link = "error.php?errno=1";
		}
		$label = ($notifs) ? "<span class=\"position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger\">$notifs</span>" : "";
		$pages["SQL Auth$label"] = "plugins/sql_auth/$link";
	}


}