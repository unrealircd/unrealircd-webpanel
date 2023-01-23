<?php

require_once "SQL/sql.php";
require_once "SQL/user.php";
require_once "SQL/settings.php";

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
		Hook::func(HOOKTYPE_FOOTER, 'sql_auth::add_footer_info');

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
		if (!unreal_get_current_user()->id)
		{
			$pages = NULL;
			return;
		}
		$pages["Panel Access"] = "plugins/sql_auth/";
		if (isset($_SESSION['id']))
		{
			$pages["Logout"] = "plugins/sql_auth/login.php?logout=true";
		}
	}

	public static function add_footer_info($empty)
	{
		if (!($user = unreal_get_current_user()))
			return;

		else {
			echo "<code>Admin Panel v" . WEBPANEL_VERSION . "</code>";
		}
	}

	public static function session_start($n)
	{
		do_log($_SESSION);
		if (!isset($_SESSION['id']) || empty($_SESSION))
		{
			$tok = split($_SERVER['SCRIPT_FILENAME'], "/");
			if ($check = security_check() && $tok[count($tok) - 1] !== "error.php") {
				header("Location: " . BASE_URL . "plugins/sql_auth/error.php");
				die();
			}
			session_destroy();
			header("Location: ".BASE_URL."plugins/sql_auth/login.php");
			die();
		}
		else
		{
			if (!unreal_get_current_user()->id) // user no longer exists
			{
				session_destroy();
				header("Location: ".BASE_URL."plugins/sql_auth/login.php");
				die();
			}
			// you'll be automatically logged out after one hour of inactivity
			session_set_cookie_params(3600);
			session_start();
		}
	}

	/**
	 * Create the tables we'll be using in the SQLdb
	 * @return void
	 */
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
		new AuthSettings();
	}

	/**
	 * Summary of add_overview_card
	 * @param mixed $stats
	 * @return void
	 */
	public static function add_overview_card(object &$stats) : void
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


function security_check()
{
	$ip = $_SERVER['REMOTE_ADDR'];
	if (dnsbl_check($ip))
		return true;

	else if (fail2ban_check($ip))
	{

	}
}

function dnsbl_check($ip)
{
	$dnsbl_lookup = DNSBL;

	// clear variable just in case
	$listed = NULL;

	// if the IP was not given because you're an idiot, stop processing
	if (!$ip) { return; }
	
	// get the first two segments of the IPv4	
	$because = split($ip, ".");   // why you
	$you = $because[1]; 		  // gotta play
	$want = $because[2];		 // that song
	$to = $you.".".$want.".";	// so loud?
	
	// exempt local connections because sometimes they get a false positive
	if ($to == "192.168." || $to == "127.0.") { return NULL; }
	
	// you spin my IP right round, right round, to check the records baby, right round-round-round
	$reverse_ip = glue(array_reverse(split($ip, ".")), ".");
	
	// checkem
	foreach ($dnsbl_lookup as $host) {
		
		//if it was listed
		if (checkdnsrr($reverse_ip . "." . $host . ".", "A")) {
			
			//take note
			$listed = $host;
		}
	}

	// if it was safe, return NOTHING
	if (!$listed) {
		return NULL;
	}
	
	// else, you guessed it, return where it was listed
	else {
		return $listed;
	}
}

function fail2ban_check($ip)
{

}