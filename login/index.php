<?php
require_once "../inc/common.php";
require_once "../inc/languages.php";
require_once "../misc/pwa-manifest.php";

$logout = false;

$redirect = get_config("base_url");
if (!empty($_GET['redirect']))
{
	$str = urldecode($_GET['redirect']);
	if (str_starts_with($str, get_config("base_url"))) // prevent redirects to like https://othersite/
		$redirect = $_GET['redirect'];
}

$redirect = (isset($_GET['redirect'])) ? $_GET['redirect'] : get_config("base_url");
if (!empty($_GET['logout']))
{
	if (!isset($_SESSION['id']))
	    $failmsg = __('user_login_no_id');
	else {
		$_SESSION = NULL;
		session_destroy();
		$logout = true;
	}
}
if (!empty($_GET['timeout']))
{
	$failmsg = __('user_login_timeout');
	$_SESSION = NULL;
	session_destroy();
}
if (!empty($_POST))
{
	if ($_POST['username'] && $_POST['password'])
	{
		$user = new PanelUser($_POST['username']);
		/* not being too informative with the login error in case of attackers */
		$hash_needs_updating = false;
		if (isset($user->id) && $user->password_verify($_POST['password'], $hash_needs_updating))
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
			panel_start_session($user);
			$_SESSION['id'] = $user->id;
			$user->add_meta("last_login", date("Y-m-d H:i:s"));
			Hook::run(HOOKTYPE_USER_LOGIN, $user);

			// ensure we have a manifest file for installing a PWA
			if (!file_exists("../manifest.json"))
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
				"login" => htmlspecialchars($_POST['username']),
				"IP" => $_SERVER['REMOTE_ADDR']
			];
			Hook::run(HOOKTYPE_USER_LOGIN_FAIL, $fail);
			$failmsg = __('user_login_fail');
		}

	}
	else
	$failmsg = __('user_login_missing');
}

?><!DOCTYPE html>
<head>
<link rel="manifest" href="<?php echo get_config("base_url"); ?>manifest.json">	
<link href="<?php echo get_config("base_url"); ?>css/unrealircd-admin.css" rel="stylesheet">
<script src="<?php echo get_config("base_url"); ?>js/unrealircd-admin.js"></script>
<script src="<?php echo get_config("base_url"); ?>js/togglepassword.js"></script> 

<script> const BASE_URL = "<?php echo get_config("base_url"); ?>"; </script>

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

<link rel="icon" type="image/png" href="<?php echo get_config("base_url"); ?>img/unreal.png">

<link rel="manifest" href="<?php echo get_config("base_url"); ?>manifest.json">	

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
			<form id="login" method="post" action="index.php?redirect=<?php echo $redirect; ?>">
				<h3 class="login-h3"><img class="login-img mb-4" src="<?php echo get_config("base_url"); ?>img/unreal.jpg">	<?php echo __('login_title'); ?></h3>
				
					<?php 
					if (isset($failmsg)) Message::Fail($failmsg);
					if ($logout)
						Message::Success(__('user_login_logged'));
					?>
					<div class="input-group">
					<div id="username" class="input-group mb-3">
						<div class="input-group-prepend">
							<span class="input-group-text" id="basic-addon1"><i class="p-1 fa-solid fa-user login-icon"></i></span>
						</div><input type="text" id="userinp" class="form-control login-input" name="username" placeholder="Username" aria-label="Username" aria-describedby="basic-addon1">
						<div style="font-size:20px" id="user_inv" class="invalid-feedback">
							<?php echo __('username_empty'); ?>
						</div>

					</div>
				<div id="password" class="input-group mb-3">
   						 <div class="input-group-prepend">
       						 <span class="input-group-text" id="basic-addon1">
           						 <i class="p-1 fa-solid fa-key login-icon"></i>
       					 </span>
  					  </div>
    			<input type="password" id="passinp" class="form-control login-input" name="password" placeholder="Password">
   			 <button class="btn btn-outline-secondary" type="button" id="togglePass" onclick="togglePassword()">
       				 <i class="fa-solid fa-eye" id="toggleIcon"></i>
   					 </button>

   			 <div style="font-size:20px" id="pass_inv" class="invalid-feedback">
     			   <?php echo __('password_empty'); ?>
   			 </div>
				</div>
				<button type="submit" class="btn btn-primary btn-block login-btn"><?php echo __('login_button'); ?></button>
			</form>
			</div>
			<div style="text-align: right; margin-bottom: 1.5rem; padding-right: 15px; max-width: 250px; margin-left: auto; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
  <select name="lang" id="lang" onchange="changeLanguage(this.value)"
          style="padding: 6px 12px; font-size: 1rem; border-radius: 6px; border: 1.5px solid #ccc; 
                 background-color: #f9f9f9; cursor: pointer; vertical-align: middle; 
                 transition: border-color 0.3s ease, box-shadow 0.3s ease; width: 130px;">
    <?php
      $languages = get_available_languages();
      $current = $_SESSION['lang'] ?? 'en-US';
      foreach ($languages as $code => $name) {
          $selected = ($code === $current) ? 'selected' : '';
          echo "<option value=\"$code\" $selected>$name</option>";
      }
    ?>
  </select>
</div>

<script>
function changeLanguage(lang) {
  const url = new URL(window.location);
  url.searchParams.set('lang', lang);
  window.location.href = url.toString();
}
</script>

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
