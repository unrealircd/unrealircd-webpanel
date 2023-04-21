<?php
/* Log the user out if it was logged in.
 * This is mostly for devs running the install screen and
 * fater succeeding the first screen suddenly being logged in
 * with old credentials/uid weirdness.
 * Code from example #1 at https://www.php.net/manual/en/function.session-destroy.php
 */
session_start();
$_SESSION = Array();
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();

require_once "../common.php";

/* Get the base url */
$uri = $_SERVER['REQUEST_URI'];
$tok = split($uri, "/");
$base_url = "";
for ($i=0; isset($tok[$i]); $i++)
{
	if ($tok[$i] == "settings" && strstr($tok[$i + 1], "install.php"))
	{
		if ($i)
		{
			for($j=0; $j < $i; $j++)
			{
				strcat($base_url,$tok[$j]);
				strcat($base_url,"/");
			}
		}
	}
}
if (!strlen($base_url))
	$base_url = "/";
define('BASE_URL', $base_url);

$writable = (is_writable("../config/")) ? true: false;
?>
<!DOCTYPE html>
<head>
<div class="media">
<div class="media-body">

	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="HandheldFriendly" content="true">



 <!-- Latest compiled and minified CSS -->
 <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

<!-- Font Awesome JS -->
<script defer src="https://use.fontawesome.com/releases/v6.2.1/js/solid.js" integrity="sha384-tzzSw1/Vo+0N5UhStP3bvwWPq+uvzCMfrN1fEFe+xBmv1C/AtVX5K0uZtmcHitFZ" crossorigin="anonymous"></script>
<script defer src="https://use.fontawesome.com/releases/v6.2.1/js/fontawesome.js" integrity="sha384-6OIrr52G08NpOFSZdxxz1xdNSndlD4vdcf/q2myIUVO0VsqaGHJsB0RaBE01VTOY" crossorigin="anonymous"></script>

<!-- Font Awesome icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css">
<script src="../js/unrealircd-admin.js"></script>
<title>UnrealIRCd Panel</title>
<link rel="icon" type="image/x-icon" href="<?php echo get_config("base_url"); ?>img/favicon.ico">

<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<!-- Popper.JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js" integrity="sha384-cs/chFZiN24E4KMATLdqdvsezGxaGsi4hLGOzlXwp5UZB1LY//20VyM2taTB4QvJ" crossorigin="anonymous"></script>
<!-- Bootstrap JS -->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/js/bootstrap.min.js" integrity="sha384-uefMccjFJAIv6A+rW+L4AHf99KvxDjWSu1z9VI8SKNVmz4sk7buKt/6v9KI65qnm" crossorigin="anonymous"></script>
</div></div>
</head>

<body role="document">

		<div class="container mt-4"><div class="row justify-content-center"><img src="../img/unreal.jpg" width="35px" height="35px" style="margin-right: 15px"><h3>UnrealIRCd Admin Panel Configuration and Setup</h3></div></div>
<?php

	if (file_exists("../config/config.php"))
	{
		?><br><div class="container"><?php Message::Fail("You're already configured!"); ?>
			<br>
			<a class="text-center btn btn-primary" href="<?php echo BASE_URL; ?>">Take me home!</a>
		</div>
		<?php
		return;
	}
	elseif (isset($_POST) && !empty($_POST))
	{
		?><br><div class="container"><?php 
		$opts = (object)$_POST;
		/* pre-load the appropriate auth plugin */
		$auth_method = (isset($opts->auth_method)) ? $opts->auth_method : NULL;
		$auth_method_name = NULL;
		switch($auth_method)
		{
			case "sql_auth":
				$auth_method_name = "SQLAuth";
				break;
			case "file_auth":
				$auth_method_name = "FileAuth";
				break;
		}
		if ($auth_method)
			$am = new Plugin($auth_method);
		else
		{
			Message::Fail("Invalid parameters");
			return;
		}
		if ($am->error)
		{
			Message::Fail("Couldn't load plugin \"$auth_method\": $am->error");
			return;
		}

		$config["base_url"] = BASE_URL;
		$config["plugins"] = Array("$auth_method");
		if ($auth_method == "sql_auth")
		{
			$config["mysql"] = [
				"host" => $opts->sql_host,
				"database" => $opts->sql_db,
				"username" => $opts->sql_user,
				"password" => $opts->sql_password,
				"table_prefix" => $opts->sql_table_prefix,
				];
		}

		/* First, write only the config file */
		write_config_file();

		if ($auth_method == "sql_auth")
			if (!sql_auth::create_tables())
				Message::Fail("Could not create SQL tables");

		$user = [
			"user_name" => $opts->account_user,
			"user_pass" => $opts->account_password,
			"fname" => $opts->account_fname,
			"lname" => $opts->account_lname,
			"user_bio" => $opts->account_bio,
			"email" => $opts->account_email
		];

		create_new_user($user);
		$lkup = new PanelUser($opts->account_user);
		if (!$lkup->id)
		{
			Message::Fail("Could not create user");
			return;
		}
		$lkup->add_permission(PERMISSION_MANAGE_USERS);

		/* Now, write all the config (config.php + settings in DB) */
		write_config();
		?>
		<br>
		The configuration file has been written. Now, log in to the panel to proceed with the rest of the installation.<br><br>
		<a class="text-center btn btn-primary" href="<?php echo BASE_URL; ?>">Let's go!</a></div>
		<?php
		return;
	}

?>
<style>
	table tr td {
		font-style: italic;
	}
</style>
<?php	if (!$writable) { ?>
<div id="page1" class="container">
	<br>
	The admin panel needs to be able to write the config file.<br>
	Please run: <code>sudo chown <?php echo get_current_user(); ?> <?php echo UPATH; ?> -R</code><br>
	And after that, refresh this webpage.<br><br>
	If you have any questions about this, read <a href="https://www.unrealircd.org/docs/UnrealIRCd_webpanel#Permissions" target="_blank">the installation manual on permissions</a>.
</div>
<?php
	die;
} ?>

<!-- Form start -->
<form method="post">
<div id="page3" class="container">
	<h5>Authentication Backend</h5>
	<br>
	Which authentication backend would you like to use?
	<br><br>
	Please choose from the available options:
	<div class="form-group">
		<div class="form-check">
			<input class="form-check-input" type="radio" name="auth_method" id="file_auth_radio" value="file_auth">
			<label class="form-check-label" for="file_auth_radio">
				File-based Authentication (Uses local files as a database, no setup needed)
			</label>
		</div>
		<div class="form-check">
			<input class="form-check-input" type="radio" name="auth_method" id="sql_auth_radio" value="sql_auth">
			<label class="form-check-label" for="sql_auth_radio">
				SQL Authentication (Requires an SQL database)
			</label>
		</div>
	</div>
	<br>
	<div id="sql_form" style="display:none">
		Please enter your SQL information. <div id="sql_instructions" class="ml-4 btn btn-sm btn-info">View instructions</div>
		<div class="form-group">
			<label for="sql_host">Hostname or IP</label>
			<input name="sql_host" type="text" class="revalidation-needed-sql form-control" id="sql_host" aria-describedby="hostname_help" value="127.0.0.1">
			<small id="hostname_help" class="form-text text-muted">The hostname or IP address of your SQL server. You should use <code>127.0.0.1</code> for the same machine.</small>
		</div>
		<div class="form-group">
			<label for="sql_db">Database name</label>
			<input name="sql_db" type="text" class="revalidation-needed-sql form-control" id="sql_db" aria-describedby="port_help">
			<small id="port_help" class="form-text text-muted">The name of the SQL database to write to and read from.</small>
		</div>
		<div class="form-group">
			<label for="sql_username">Username</label>
			<input name="sql_user" type="text" class="revalidation-needed-sql form-control" id="sql_user" aria-describedby="username_help">
			<small id="username_help" class="form-text text-muted">The name of SQL user</small>
		</div>
		<div class="form-group">
			<label for="sql_password">Password</label>
			<input name="sql_password" type="password" class="revalidation-needed-sql form-control" id="sql_password">
		</div>
		<div class="form-group">
			<label for="sql_table_prefix">Table prefix</label>
			<input name="sql_table_prefix" type="text" class="revalidation-needed-sql form-control" id="sql_table_prefix" aria-describedby="sql_table_prefix_help" value="unreal_">
			<small id="sql_table_prefix_help" class="form-text text-muted">The prefix for table names (leave blank for none)</small>
		</div>
	</div>
	<div class="text-center">
		<div id="page3_next" class="btn btn-primary ml-3">Next</div>
		<div id="page3_test_connection" class="btn btn-primary ml-3" style="display: none">Test connection</div>
	</div>
</div>
<div id="page4" class="container" >
	<h5>Create your account</h5>
	<br>
	You need an account, let's make one.<br><br>
	<div class="form-group">
		<label for="account_user" id="userlabel">Pick a username</label>
		<input name="account_user" type="text" class="form-control" id="account_user" aria-describedby="username_help">
		<small id="username_help" class="form-text text-muted">Pick a username! Please make sure it's at least 3 characters long, contains no spaces, and is made of only letters and numbers.</small>
	</div>
	<div class="form-group">
		<label for="account_password" id="passlabel">Password</label>
		<input name="account_password" type="password" class="form-control" id="account_password" aria-describedby="password_help">
		<small id="password_help" class="form-text text-muted">Please choose a password that at least 8 characters long, contains at least one uppercase letter, one lowercase letter, one number and one symbol.</small>
	</div>
	<div class="form-group">
		<label for="account_password_conf" id="passconflabel">Confirm password</label>
		<input name="account_password_conf" type="password" class="form-control" id="account_password_conf">
	</div>
	<div class="form-group">
		<label for="account_email" id="emaillabel">Email address</label>
		<input name="account_email" type="text" class="form-control" id="account_email" aria-describedby="email_help">
	</div>
	<div class="form-group">
		<label for="account_fname" id="fnamelabel">First name</label>
		<input name="account_fname" type="text" class="form-control" id="account_fname">
	</div>
	<div class="form-group">
		<label for="account_lname" id="lnamelabel">Last name</label>
		<input name="account_lname" type="text" class="form-control" id="account_lname">
	</div>
	<div class="form-group">
		<label for="account_bio" id="biolabel">Bio</label>
		<textarea name="account_bio" type="text" class="form-control" id="account_bio"></textarea>
	</div>
	<div class="text-center">
		<div id="page4_back" class="btn btn-secondary mr-3">Back</div>
		<button id="page4_next" type="submit" class="btn btn-primary ml-3">Submit</div>
	</div>
</div>
</form>
<script>
	let BASE_URL = '<?php echo BASE_URL; ?>';
	let chmod_help = document.getElementById('chmod_help');

	if (chmod_help)
		chmod_help.addEventListener('click', e => {
			window.open("https://www.unrealircd.org/docs/UnrealIRCd_webpanel#Permissions");
		});

	let page3 = document.getElementById('page3');
	let page4 = document.getElementById('page4');

	let file_auth_radio = document.getElementById('file_auth_radio');
	let sql_auth_radio = document.getElementById('sql_auth_radio');
	let sql_form = document.getElementById('sql_form');
	let sql_host = document.getElementById('sql_host');
	let sql_db = document.getElementById('sql_db');
	let sql_user = document.getElementById('sql_user');
	let sql_pass = document.getElementById('sql_password');
	let sql_test_conn = document.getElementById('page3_test_connection');
	let page3_next = document.getElementById('page3_next');

	let page4_back = document.getElementById('page4_back');
	let page4_next = document.getElementById('page4_next');

	page4.style.display = 'none';

	revalidate_sql = document.querySelectorAll('.revalidation-needed-sql');
	for (let i = 0; i < revalidate_sql.length; i++)
	{
		revalidate_sql[i].addEventListener('input', e => {
			page3_next.style.display = 'none';
			sql_test_conn.innerHTML = 'Test connection';
			sql_test_conn.style.display = '';
			sql_test_conn.classList.remove('disabled');
		});
	}

	page3_next.addEventListener('click', e => {
		page3.style.display = 'none';
		page4.style.display = '';
	});

	file_auth_radio.addEventListener('click', e => {
		if (file_auth_radio.checked){
			sql_form.style.display = 'none';
			sql_test_conn.style.display = 'none';
			page3_next.style.display = '';
		}
	});

	sql_auth_radio.addEventListener('click', e => {
		if (!file_auth_radio.checked){
			sql_form.style.display = '';
			sql_test_conn.style.display = '';
			page3_next.style.display = 'none';
		}
	});

	sql_instructions.addEventListener('click', e => {
		window.open("https://www.unrealircd.org/docs/UnrealIRCd_webpanel#SQL_Authentication");
	});

	sql_test_conn.addEventListener('click', e => {
		sql_test_conn.classList.add('disabled');
		sql_test_conn.innerHTML = "Checking...";
		fetch(BASE_URL + 'api/installation.php?method=sql&host='+sql_host.value+'&database='+sql_db.value+'&user='+sql_user.value+'&password='+sql_pass.value)
		.then(response => response.json())
		.then(data => {
			if (data.success)
			{
				// do something with the JSON data
				sql_test_conn.innerHTML = "Success!";
				setTimeout(function() {
					sql_test_conn.style.display = 'none';
					page3_next.style.display = '';
				}, 2000);
			}
			else
			{
				sql_test_conn.innerHTML = "Failed!";
				setTimeout(function() {
					sql_test_conn.innerHTML = "Test connection";
					sql_test_conn.classList.remove('disabled');
				}, 2000);
			}
		})
		.catch(error => {
			sql_test_conn.innerHTML = "Failed!";
				setTimeout(function() {
					sql_test_conn.innerHTML = "Test connection";
					sql_test_conn.classList.remove('disabled');
				}, 2000);
		});
	});

	user_name_label = document.getElementById('userlabel');
	user_name = document.getElementById('account_user');
	user_pass_label = document.getElementById('passlabel');
	user_pass = document.getElementById('account_password');
	user_pass2_label = document.getElementById('passconflabel');
	user_pass2 = document.getElementById('account_password_conf');
	user_email_label = document.getElementById('emaillabel');
	user_email = document.getElementById('account_email');
	user_fname = document.getElementById('account_fname');
	user_lname = document.getElementById('account_lname');
	user_bio = document.getElementById('account_bio');
	
	page4_back.addEventListener('click', e => {
		page4.style.display = 'none';
		page3.style.display = '';
	});

	page4_next.addEventListener('click', e => {

		/* Form validation */
		let req_not_met = ' <small style="color:red">Does not meet requirements</small>';
		let errs = 0;
		const regex_username = /^[a-zA-Z\d]{3,}$/;
		if (!regex_username.test(user_name.value))
		{
			user_name_label.innerHTML = 'Pick a username!' + req_not_met;
			errs++;
		} else
			user_name_label.innerHTML = 'Pick a username!';

		let regex_pass = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]).{8,}$/;
		if (!regex_pass.test(user_pass.value))
		{
			user_pass_label.innerHTML = 'Password' + req_not_met;
			errs++;
		} else
			user_pass_label.innerHTML = 'Password';

		if (user_pass2.value !== user_pass.value)
		{
			user_pass2_label.innerHTML = 'Confirm password <small style="color:red">Passwords do not match</small>';
			errs++;
		} else
			user_pass2_label.innerHTML = 'Confirm password';

		const regex_email = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
		if (!regex_email.test(user_email.value))
		{
			user_email_label.innerHTML = 'Email address' + req_not_met;
			errs++;
		}
		else
			user_email_label.innerHTML = 'Email address';

		if (errs)
		{
			e.preventDefault();
			return false;
		}

		page4.style.display = 'none';
	});
</script>