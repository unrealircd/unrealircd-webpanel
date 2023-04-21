<?php

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

		/* Assume we have a example config available and copy from that so we leave all the comments n things in there
		 * If we're setting up it's unlikely anyone would have swooped in already and deleted the example config.
		 * Throw an error if not
		*/
		if (!file_exists("../config/config.php.sample"))
		{
			Message::Fail("Could not get sample configuration. We need it to work with.");
			return;
		}
		else {
			$config["base_url"] = BASE_URL;
			$config["unrealircd"] = [
				"rpc_user" => $opts->rpc_user,
				"rpc_password" => $opts->rpc_password,
				"host"=>$opts->rpc_iphost,
				"port"=>$opts->rpc_port,
				"tls_verify_cert"=>isset($opts->rpc_ssl)?true:false,
				];
			$config["plugins"] = Array("$auth_method");
			if ($auth_method == "sql_auth")
			{
				$config["mysql"] = [
					"host" => $opts->sql_host,
					"database" => $opts->sql_db,
					"username" => $opts->sql_user,
					"password" => $opts->sql_password,
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
			Great! Everything has been completely set up for you, and you can now log in.
			<a class="text-center btn btn-primary" href="<?php echo BASE_URL; ?>">Let's go!</a></div>
			<?php
			return;
		}
	}

?>
<style>
	table tr td {
		font-style: italic;
	}
</style>
<div id="page1" class="container">
	<br>
	Welcome to the IRC admin panel setup page. This setup process will guide you through the necessary steps to configure your IRC uplink and choose your preferred authentication method.
	<br><br>
	The first page will ask you for your UnrealIRCd uplink credentials and will test them to ensure that the connection is successful. This step is crucial for the Admin Panel to function properly.
	<br><br>
	Next, you will be asked to choose your preferred authentication method between file-based and SQL. Depending on your choice, additional steps may be required. If you choose SQL, you will be given the option to set up the appropriate tables in the database.
	<br><br>
	After that, we'll take you through a short account creation process where you get to create your first account. Once you're setup and logged in, you'll be able to add more users and choose what they can do on your panel.
	<br><br>
	Finally, the last page will offer additional options that you can customize according to your preferences. Once you have completed all the necessary steps, your IRC admin panel will be fully configured and ready for use.
	<br><br>
	Should you wish to edit your config further, you will find it in the <code>config</code> directory called <code>config.php</code>
	<br><br>
	We recommend that you carefully read each page and fill in all the required information accurately to ensure a seamless setup process. Thank you for choosing UnrealIRCd Admin Panel, and we hope you find it useful for managing your server/network.
		<br><br>
	
	<div id="proceed_div" class="text-center"><?php echo ($writable)
		? '<div id="page1_proceed" class="btn btn-primary">Proceed</div>'
		: 	'Before we begin, you must let the shell user who owns the webpanel have permission to create files.<br>
			 <div id="chmod_help" class="btn btn-sm btn-info">Get info</div>'; ?>
	</div>
</div>

<!-- Form start -->
<form method="post">
<div id="page2" class="container">
	<h5>RPC Uplink Information</h5>
	<br>
	First, let's get you linked with UnrealIRCd.
	<br><br>
	If you don't have your credentials, you will need to create them. This is done in your <code>unrealircd.conf</code> <div id="rpc_instructions" class="ml-4 btn btn-sm btn-info">View instructions</div>
	<br><br>
	<form>
	<div class="form-group">
		<label for="rpc_iphost">Hostname or IP</label>
		<input name="rpc_iphost" type="text" class="revalidation-needed-rpc form-control" id="rpc_iphost" aria-describedby="hostname_help" value="127.0.0.1">
		<small id="hostname_help" class="form-text text-muted">The hostname or IP address of your UnrealIRCd server. You should use <code>127.0.0.1</code> for the same machine.</small>
	</div>
	<div class="form-group">
		<label for="rpc_port">Server Port</label>
		<input name="rpc_port" type="text" class="revalidation-needed-rpc form-control" id="rpc_port" aria-describedby="port_help" value="8600">
		<small id="port_help" class="form-text text-muted">The port which you designated for RPC connections in your <code>unrealircd.conf</code></small>
	</div>
	<div class="form-group form-check">
		<input name="rpc_ssl" type="checkbox" class="revalidation-needed-rpc form-check-input" value="ssl" id="rpc_ssl">
		<label class="form-check-label" for="rpc_ssl">My UnrealIRCd server is on a different machine, verify the TLS connection.</label>
	</div>
	<div class="form-group">
		<label for="rpc_username">Username</label>
		<input name="rpc_user" type="text" class="revalidation-needed-rpc form-control" id="rpc_user" aria-describedby="username_help">
		<small id="username_help" class="form-text text-muted">The name of your <code>rpc-user</code> block as defined in your <code>unrealircd.conf</code></small>
	</div>
	<div class="form-group">
		<label for="rpc_password">Password</label>
		<input name="rpc_password" type="password" class="revalidation-needed-rpc form-control" id="rpc_password">
	</div>
	<div class="text-center">
		<div id="page2_back" class="btn btn-secondary mr-3">Back</div>
		<div id="page2_next" class="btn btn-primary ml-3" style="display: none">Next</div>
		<div id="page2_test_connection" class="btn btn-primary ml-3">Test connection</div>
	</div>
</div>


<div id="page3" class="container">
	<h5>Authentication Method</h5>
	<br>
	Here's where you can choose which type of authentication mechanism you want to use behind the scenes.
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
	</div>
	<div class="text-center">
		<div id="page3_back" class="btn btn-secondary mr-3">Back</div>
		<div id="page3_next" class="btn btn-primary ml-3">Next</div>
		<div id="page3_test_connection" class="btn btn-primary ml-3" style="display: none">Test connection</div>
	</div>
</div>
<div id="page4" class="container" >
	<h5>Create your account</h5>
	<br>
	Great! Everything looks good so far! Just one last thing before we confirm everything and get you set up.<br>
	You need an account! Let's make one.<br><br>
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
		<div id="page4_next" class="btn btn-primary ml-3">Next</div>
	</div>
</div>

<div class="container" id="page5">
	<h5>Confirm details</h5>
	<br>
	<div class="accordion" id="results_accord">
		<div class="card">
			<div class="card-header" id="rpc_heading">
				<h2 class="mb-0">
					<button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
						RPC Uplink Information
					</button>
				</h2>
			</div>

			<div id="collapseOne" class="collapse show" aria-labelledby="rpc_heading" data-parent="#results_accord">
				<div id="results_rpc" class="card-body">

				</div>
			</div>
		</div>
		<div class="card">
			<div class="card-header" id="headingTwo">
				<h2 class="mb-0">
					<button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
						Authentication Plugin
					</button>
				</h2>
			</div>
			<div id="collapseTwo" class="collapse show" aria-labelledby="headingTwo" data-parent="#results_accord">
				<div id="results_auth_plugin" class="card-body">
					
				</div>
			</div>
		</div>
		<div class="card">
			<div class="card-header" id="headingThree">
				<h2 class="mb-0">
					<button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
						Account Information
					</button>
				</h2>
			</div>
			<div id="collapseThree" class="collapse show" aria-labelledby="headingThree" data-parent="#results_accord">
				<div id="results_account" class="card-body">

				</div>
			</div>
		</div>
	</div><br>
	<div class="text-center">
		<div id="page5_back" class="btn btn-secondary mr-3">Back</div>
		<button id="page4_next" type="submit" class="btn btn-primary ml-3">Submit</div>
	</div>
</div>
<!-- Form end -->
</form>
<script>
	let BASE_URL = '<?php echo BASE_URL; ?>';
	let chmod_help = document.getElementById('chmod_help');

	if (chmod_help)
		chmod_help.addEventListener('click', e => {
			window.open("https://www.unrealircd.org/docs/UnrealIRCd_webpanel#Permissions");
		});

	let page1 = document.getElementById('page1');
	let page2 = document.getElementById('page2');
	let page3 = document.getElementById('page3');
	let page4 = document.getElementById('page4');
	let page5_back = document.getElementById('page5');
	let rpc_instructions = document.getElementById('rpc_instructions');
	let setup_start = document.getElementById('page1_proceed');

	let rpc_host = document.getElementById('rpc_iphost');
	let rpc_port = document.getElementById('rpc_port');
	let rpc_user = document.getElementById('rpc_user');
	let rpc_pass = document.getElementById('rpc_password');
	let rpc_tls = document.getElementById('rpc_ssl');
	
	let page2_back = document.getElementById('page2_back');
	let page2_next = document.getElementById('page2_next');
	let test_conn = document.getElementById('page2_test_connection');

	let file_auth_radio = document.getElementById('file_auth_radio');
	let sql_auth_radio = document.getElementById('sql_auth_radio');
	let sql_form = document.getElementById('sql_form');
	let sql_host = document.getElementById('sql_host');
	let sql_db = document.getElementById('sql_db');
	let sql_user = document.getElementById('sql_user');
	let sql_pass = document.getElementById('sql_password');
	let sql_test_conn = document.getElementById('page3_test_connection');
	let page3_back = document.getElementById('page3_back');
	let page3_next = document.getElementById('page3_next');

	let page4_back = document.getElementById('page4_back');
	let page4_next = document.getElementById('page4_next');
	
	page2.style.display = 'none';
	page3.style.display = 'none';
	page3.style.display = 'none';
	page4.style.display = 'none';
	page5.style.display = 'none';

	rpc_instructions.addEventListener('click', e => {
		window.open("https://www.unrealircd.org/docs/UnrealIRCd_webpanel#Configuring_UnrealIRCd");
	});

	setup_start.addEventListener('click', e => {
		page1.style.display = 'none';
		page2.style.display = '';
	});

	page2_back.addEventListener('click', e => {
		page2.style.display = 'none';
		page1.style.display = '';
	});
	page2_next.addEventListener('click', e => {
		page2.style.display = 'none';
		page3.style.display = '';
		sql_form.style.display = 'none';
	});

	revalidate_rpc = document.querySelectorAll('.revalidation-needed-rpc');
	for (let i = 0; i < revalidate_rpc.length; i++)
	{
		revalidate_rpc[i].addEventListener('input', e => {
			page2_next.style.display = 'none';
			test_conn.innerHTML = 'Test connection';
			test_conn.style.display = '';
			test_conn.classList.remove('disabled');
		});
	}

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

	/* The RPC connection tester! */
	test_conn.addEventListener('click', e => {
		test_conn.classList.add('disabled');
		test_conn.innerHTML = "Checking...";
		fetch(BASE_URL + 'api/installation.php?method=rpc&host='+rpc_host.value+'&port='+rpc_port.value+'&user='+rpc_user.value+'&password='+rpc_pass.value+'&tls_verify='+rpc_tls.checked)
		.then(response => response.json())
		.then(data => {
			if (data.success)
			{
				// do something with the JSON data
				test_conn.innerHTML = "Success!";
				setTimeout(function() {
					test_conn.style.display = 'none';
					page2_next.style.display = '';
				}, 2000);
			}
			else
			{
				test_conn.innerHTML = "Failed!";
				setTimeout(function() {
					test_conn.innerHTML = "Test connection";
					test_conn.classList.remove('disabled');
				}, 2000);
			}
		})
		.catch(error => {
			test_conn.innerHTML = "Failed!";
				setTimeout(function() {
					test_conn.innerHTML = "Test connection";
					test_conn.classList.remove('disabled');
				}, 2000);
		});
	});

	page3_back.addEventListener('click', e => {
		page3.style.display = 'none';
		page2.style.display = '';
	});

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
			return;

		/* Start building the next page */
		let result_rpc = document.getElementById('results_rpc');
		let tls = '';
		if (rpc_tls.checked)
			tls = ` <div class="badge rounded-pill badge-success">Using TLS</div>`;
		result_rpc.innerHTML = `<table class="table-sm table-responsive caption-top table-hover">
								<tr><th>IP/Host</th><td>` + rpc_host.value + `</td></tr>
								<tr><th>Port</th><td>` + rpc_port.value + tls + `</div>
								<tr><th>Username</th><td>` + rpc_user.value + `</td></tr>
								<tr><th>Password</th><td>(Hidden)</td></tr>
								</table>`;

		let result_auth = document.getElementById('results_auth_plugin');
		if (file_auth_radio.checked)
		{
			result_auth.innerHTML = `<table class="table-sm table-responsive caption-top table-hover">
									<tr><th>Authentication Plugin</th><td>File Auth</td></tr>
									</table>`;
		}
		else if (!file_auth_radio.checked)
		{
			result_auth.innerHTML = `<table class="table-sm table-responsive caption-top table-hover">
									<tr><th>Authentication Plugin</th><td>SQL Auth</td></tr>
									
									<tr><th>IP/Host</th><td>` + sql_host.value + `</td></tr>
									<tr><th>Database</th><td>` + sql_db.value + `</div>
									<tr><th>Username</th><td>` + sql_user.value + `</td></tr>
									<tr><th>Password</th><td>(Hidden)</td></tr>
									</table>`;
		}


		let result_account = document.getElementById('results_account');
		result_account.innerHTML = `<table class="table-sm table-responsive caption-top table-hover">
							<tr><th>Username</th><td>` + user_name.value + `</td></tr>
							<tr><th>Email</th><td>` + user_email.value + `</div>
							<tr><th>Password</th><td>(Hidden)</td></tr>
							<tr><th>First Name</th><td>` + user_fname.value + `</td></tr>
							<tr><th>Last Name</th><td>` + user_lname.value + `</td></tr>
							<tr><th>Bio</th><td>` + user_bio.value + `</td></tr>
							</table>`;

		page4.style.display = 'none';
		page5.style.display = '';
	});



</script>