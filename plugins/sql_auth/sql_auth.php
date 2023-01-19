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
		self::create_tables();
		Hook::func(HOOKTYPE_NAVBAR, 'sql_auth::add_navbar');
		Hook::func(HOOKTYPE_PRE_HEADER, 'sql_auth::session_start');
		Hook::func(HOOKTYPE_OVERVIEW_CARD, 'sql_auth::add_overview_card');

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
		
		$pages["Panel Access"] = "plugins/sql_auth/";
		if (isset($_SESSION['id']))
		{
			$pages["Logout"] = "plugins/sql_auth/login.php?logout=true";
		}
	}

	public static function session_start($n)
	{
		if (!isset($_SESSION['id']))
		{
			header("Location: ".BASE_URL."plugins/sql_auth/login.php");
		}
		else
		{
			$user = new SQLA_User(NULL, $_SESSION['id']);
			if (!$user->id)
			{
				session_destroy();
				header("Location: ".BASE_URL."plugins/sql_auth/login.php");
			}
		}
	}

	public static function create_tables()
	{
		$conn = sqlnew();
		$conn->query("CREATE TABLE IF NOT EXISTS " . SQL_PREFIX . "users (
			user_id int AUTO_INCREMENT NOT NULL,
			user_name VARCHAR(255) NOT NULL,
			user_pass VARCHAR(255) NOT NULL,
			
			user_fname VARCHAR(255),
			user_lname VARCHAR(255),
			user_bio VARCHAR(255),
			created VARCHAR(255),
			PRIMARY KEY (user_id)
		)");
		$conn->query("CREATE TABLE IF NOT EXISTS " . SQL_PREFIX . "user_meta (
			meta_id int AUTO_INCREMENT NOT NULL,
			user_id int NOT NULL,
			meta_key VARCHAR(255) NOT NULL,
			meta_value VARCHAR(255),
			PRIMARY KEY (meta_id)
		)");
	}

	public static function add_overview_card(&$stats)
	{
		$num_of_panel_admins = sqlnew()->query("SELECT COUNT(*) FROM " . SQL_PREFIX . "users")->fetchColumn();
		?>

		<div class="container mt-5">

			<div class="row">
				<div class="col-sm-3">
					<div class="card text-center">
						<div class="card-header bg-success text-white">
							<div class="row">
								<div class="col">
									<i class="fa fa-lock-open fa-3x"></i>
								</div>
								<div class="col">
									<h3 class="display-4"><?php echo $num_of_panel_admins; ?></h3>
								</div>
							</div>
						</div>
						<div class="card-body">
							<div class="row">
								<div class="col">
									<h6>Panel Users</h6>
								</div>
								<div class="col"> <a class="btn btn-primary" href="<?php echo BASE_URL; ?>plugins/sql_auth/">View</a></div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>		
		<?php
	}

}