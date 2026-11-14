<?php
define('PANEL_PAGE_LOGIN', 1);
require_once "../inc/common.php";
require_once "../misc/pwa-manifest.php";

$logout = false;

$redirect = panel_safe_redirect($_GET['redirect'] ?? "", get_config("base_url"));

if (!empty($_GET['logout']))
{
	/* The logout link carries the CSRF token, so another website
	 * cannot log the user out with a plain link or image.
	 */
	$tok = is_string($_GET['logout']) ? rtrim($_GET['logout'], "/") : "";
	if (panel_session_uid() === NULL)
		$failmsg = "Nothing to logout from";
	else if (!hash_equals(panel_csrf_token(), $tok)) {
		$failmsg = "Could not log you out: the logout link has expired. Use the Logout item in the menu.";
	} else {
		panel_session_logout();
		$logout = true;
	}
}
if (!empty($_GET['timeout']) && (panel_session_uid() !== NULL) && ($_SERVER['REQUEST_METHOD'] === 'GET'))
{
	/* Still (or again) logged in: nothing timed out. Never log out
	 * because of this parameter, just go on to the page.
	 */
	header('Location: ' . $redirect);
	die();
}
if (!empty($_SESSION['timed_out']) || (!empty($_GET['timeout']) && (panel_session_uid() === NULL)))
{
	/* The server already logged out when the session really timed out,
	 * so only show the message. The session keeps its CSRF token, so
	 * login forms that are open in other tabs keep working.
	 */
	$failmsg = "Your session has timed out. Please login again to continue";
	unset($_SESSION['timed_out']);
}
if ($panel_csrf_failed)
	$failmsg = "Your login form had expired. Please try again.";

if (!empty($_POST))
{
	if (isset($_POST['username']) && isset($_POST['password']) &&
	    is_string($_POST['username']) && is_string($_POST['password']) &&
	    strlen($_POST['username']) && strlen($_POST['password']))
	{
		$user = new PanelUser($_POST['username']);
		/* not being too informative with the login error in case of attackers */
		$hash_needs_updating = false;
		/* Spend the same time on unknown usernames as on a password check,
		 * so the response time does not show which usernames exist.
		 */
		if (!isset($user->id))
			PanelUser::password_hash($_POST['password']);
		if (isset($user->id) && ($_POST['username'] === "default") && ($_POST['password'] === "testing"))
		{
			/* The old default account (early 2023 sample config). Never
			 * allow it with its well-known password. An admin has to
			 * change its password or delete it.
			 */
			$fail = [
				"login" => h($_POST['username']), /* escaped, as before: plugins may show it */
				"IP" => $_SERVER['REMOTE_ADDR']
			];
			Hook::run(HOOKTYPE_USER_LOGIN_FAIL, $fail);
			$failmsg = "Incorrect login";
		}
		else if (isset($user->id) && $user->password_verify($_POST['password'], $hash_needs_updating))
		{
			/* SUCCESSFUL LOGIN */
			if ($hash_needs_updating)
			{
				/* Set password again so it is freshly hashed */
				$hash = PanelUser::password_hash($_POST['password']);
				$ar = ["update_pass_conf"=>$hash];
				$user->update_core_info($ar);
				unset($ar);
				unset($hash);
			}
			/* Logs in with a new session ID (no session fixation) */
			panel_start_session($user);
			$user->add_meta("last_login", date("Y-m-d H:i:s"));
			Hook::run(HOOKTYPE_USER_LOGIN, $user);

			// ensure we have a manifest file for installing a PWA
			if (!file_exists(UPATH."/manifest.json"))
				create_pwa_manifest();

			/* Middle of install? Override redirect: */
			if (!isset($config['unrealircd']))
				$redirect = get_config("base_url")."settings/rpc-servers.php";
			header('Location: ' . $redirect);
			die();
		}
		else
		{
			/* LOGIN FAILED */
			$fail = [
				"login" => h($_POST['username']), /* escaped, as before: plugins may show it */
				"IP" => $_SERVER['REMOTE_ADDR']
			];
			Hook::run(HOOKTYPE_USER_LOGIN_FAIL, $fail);
			$failmsg = "Incorrect login";
		}

	}
	else
	$failmsg = "Couldn't log you in: Missing credentials";
}

?><!DOCTYPE html>
<head>
<link rel="manifest" href="<?php echo h(get_config("base_url")); ?>manifest.json">
<link href="<?php echo h(get_config("base_url")); ?>css/unrealircd-admin.css" rel="stylesheet">
<script src="<?php echo h(get_config("base_url")); ?>js/unrealircd-admin.js"></script>

<script> const BASE_URL = <?php echo js_value(get_config("base_url")); ?>; </script>

 <!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

<!-- jQuery library -->
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.1/dist/jquery.slim.min.js"></script>

<!-- Popper JS -->
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>

<!-- Latest compiled JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Font Awesome icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css">

<link rel="icon" type="image/png" href="<?php echo h(get_config("base_url")); ?>img/unreal.png">

<script>
		console.log("Attempting to add service worker...");
		if ("serviceWorker" in navigator) {
			navigator.serviceWorker.register(BASE_URL+"js/service-worker.js")
			.then((registration) => {
			console.log("Service Worker registered:", registration);
			})
		.catch((error) => {
			console.log("Service Worker registration failed:", error);
			});
		}
</script>

<title>UnrealIRCd Panel</title>
</head>
<style>
	
	.login-h3 {
		font-size: default;
	}
	.login-img {
		height: 64px;
	}
	.login-input {
		font-size: default;
	}
	.login-btn {
		font-size: default;
	}
	.login-icon {
		font-size: default;
	}
	.login-favicon {
		width: 32px;
		height: 32px;
	}

	@media (orientation: portrait) {
		.login-h3 {
			font-size: 50px;
		}
		.login-input {
			font-size: 30px;
		}
		.login-btn {
			font-size: 30px;
		}
		.login-icon {
			font-size: 30px;
		}
	}



</style>
<section class="vh-100">
  <div id="ctr" class="container-xxl py-5 h-10">
	<div class="row d-flex justify-content-center align-items-center">
	  <div class="col-12 col-md-8 col-lg-6 col-xl-5">
		<div class="card shadow-2-strong" style="border-radius: 1rem;">
		  <div class="card-body p-5 text-center">
			<form id="login" method="post" action="index.php?redirect=<?php echo h(urlencode($redirect)); ?>">
				<?php echo panel_csrf_field(); ?>
				<h3 class="login-h3"><img class="login-img mb-4" src="<?php echo h(get_config("base_url")); ?>img/unreal.jpg">	Log in to use Admin Panel</h3>
				
					<?php 
					if (isset($failmsg)) Message::Fail($failmsg);
					if ($logout)
						Message::Success("You have been logged out");
					?>
					<div class="input-group">
					<div id="username" class="input-group mb-3">
						<div class="input-group-prepend">
							<span class="input-group-text" id="basic-addon1"><i class="p-1 fa-solid fa-user login-icon"></i></span>
						</div><input type="text" id="userinp" class="form-control login-input" name="username" placeholder="Username" aria-label="Username" aria-describedby="basic-addon1">
						<div style="font-size:20px" id="user_inv" class="invalid-feedback">
							Username cannot be empty.
						</div>

					</div>
					<div id="password" class="input-group mb-3">
						<div class="input-group-prepend">
							<span class="input-group-text" id="basic-addon1"><i class="p-1 fa-solid fa-key login-icon"></i></span>
						</div><input type="password" id="passinp" class="form-control login-input" name="password" placeholder="Password">
						<div style="font-size:20px" id="pass_inv" class="invalid-feedback">
						Password cannot be empty.
						</div>

					</div>

				</div>
				<button type="submit" class="btn btn-primary btn-block login-btn">Log-In</button>
			</form>
			</div>
		</div>
	</div>
</div>
</div></section>
<style>

body {
		background-image: url('https://cdn.wallpapersafari.com/34/98/yznZmQ.jpg');
		background-size: cover;
	}
</style>
<script>
	var form = document.getElementById('login');
	var pinp = document.getElementById('passinp');
	var uinp = document.getElementById('userinp');

	window.onload = () => uinp.focus();
	
	form.addEventListener('submit', (event) =>
	{
		event.preventDefault();
		var err = 0;
		if (uinp.value.length == 0)
		{
			$('#user_inv').show();
			err++;
		}
		if (pinp.value.length == 0)
		{
			$('#pass_inv').show();
			err++;
		}
		if (err)
			return;
		else
			form.submit();
	});
</script>

