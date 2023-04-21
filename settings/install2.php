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

	if (isset($config['unrealircd']) && empty($config['unrealircd']['host']))
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

		$config["unrealircd"] = [
			"rpc_user" => $opts->rpc_user,
			"rpc_password" => $opts->rpc_password,
			"host"=>$opts->rpc_iphost,
			"port"=>$opts->rpc_port,
			"tls_verify_cert"=>isset($opts->rpc_ssl)?true:false,
			];

		/* And write the new config */
		write_config();
		?>
		<br>
		Great! Everything has been completely set up for you. You can now browse the admin panel.<br><br>
		<a class="text-center btn btn-primary" href="<?php echo get_config("base_url"); ?>">Let's go!</a></div>
		<?php
		return;
	}

?>
<style>
	table tr td {
		font-style: italic;
	}
</style>
<!-- Form start -->
<form method="post">
<div id="page2" class="container">
	<h5>RPC Uplink Information</h5>
	<br>
	Let's get you linked with UnrealIRCd.
	<br><br>
	If you don't have your credentials, you will need to create them. This is done in your <code>unrealircd.conf</code> <div id="rpc_instructions" class="ml-4 btn btn-sm btn-info">View instructions</div>
	<br><br>
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
		<button id="page2_next" type="submit" class="btn btn-primary ml-3">Submit</div>
		<div id="page2_test_connection" class="btn btn-primary ml-3">Test connection</div>
	</div>
</div>
<!-- Form end -->
</form>

<script>
	let BASE_URL = '<?php echo get_config("base_url"); ?>';

	let page2 = document.getElementById('page2');
	let rpc_instructions = document.getElementById('rpc_instructions');

	let rpc_host = document.getElementById('rpc_iphost');
	let rpc_port = document.getElementById('rpc_port');
	let rpc_user = document.getElementById('rpc_user');
	let rpc_pass = document.getElementById('rpc_password');
	let rpc_tls = document.getElementById('rpc_ssl');
	
	let page2_back = document.getElementById('page2_back');
	let page2_next = document.getElementById('page2_next');
	let test_conn = document.getElementById('page2_test_connection');

	rpc_instructions.addEventListener('click', e => {
		window.open("https://www.unrealircd.org/docs/UnrealIRCd_webpanel#Configuring_UnrealIRCd");
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

	/* The RPC connection tester! */
	test_conn.addEventListener('click', e => {
		test_conn.classList.add('disabled');
		test_conn.innerHTML = "Checking...";
		fetch(BASE_URL + 'api/installation2.php?method=rpc&host='+rpc_host.value+'&port='+rpc_port.value+'&user='+rpc_user.value+'&password='+rpc_pass.value+'&tls_verify='+rpc_tls.checked)
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
</script>