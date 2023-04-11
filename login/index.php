
<?php
require_once "../common.php";

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
		$failmsg = "Nothing to logout from";
	else {
		$_SESSION = NULL;
		session_destroy();
		$logout = true;
	}
}
if (!empty($_GET['timeout']))
{
	$failmsg = "Your session has timed out. Please login again to continue";
	$_SESSION = NULL;
	session_destroy();
}
if (!empty($_POST))
{
	if ($_POST['username'] && $_POST['password'])
	{
		/* securitah */
		$user = new PanelUser($_POST['username']);
		/* not being too informative with the login error in case of attackers */
		if (isset($user->id) && $user->password_verify($_POST['password']))
		{
			$_SESSION['id'] = $user->id;
			header('Location: ' . $redirect);
			$user->add_meta("last_login", date("Y-m-d H:i:s"));
			Hook::run(HOOKTYPE_USER_LOGIN, $user);
			die();
		}
		else
		{
			$fail = [
				"login" => htmlspecialchars($_POST['username']),
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
<link href="<?php echo get_config("base_url"); ?>css/unrealircd-admin.css" rel="stylesheet">
<script src="<?php echo get_config("base_url"); ?>js/unrealircd-admin.js"></script>
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

<link rel="icon" type="image/x-icon" href="<?php echo get_config("base_url"); ?>img/favicon.ico">
<title>UnrealIRCd Panel</title>
</head>
<section class="vh-100">
  <div class="container py-5 h-10">
	<div class="row d-flex justify-content-center align-items-center h-100">
	  <div class="col-12 col-md-8 col-lg-6 col-xl-5">
		<div class="card shadow-2-strong" style="border-radius: 1rem;">
		  <div class="card-body p-5 text-center">
			<form id="login" method="post" action="index.php?redirect=<?php echo $redirect; ?>">
				<h3><img src="<?php echo get_config("base_url"); ?>img/favicon.ico">	Log in to use Admin Panel</h3>
				
					<?php 
					if (isset($failmsg)) Message::Fail($failmsg);
					if ($logout)
						Message::Success("You have been logged out");
					?>
					<div class="input-group">
					<div id="username" class="input-group mb-3">
						<div class="input-group-prepend">
							<span class="input-group-text" id="basic-addon1"><i class="fa-solid fa-user"></i></span>
						</div><input type="text" id="userinp" class="form-control" name="username" placeholder="Username" aria-label="Username" aria-describedby="basic-addon1">
						<div id="user_inv" class="invalid-feedback">
							Username cannot be empty.
						</div>

					</div>
					<div id="password" class="input-group mb-3">
						<div class="input-group-prepend">
							<span class="input-group-text" id="basic-addon1"><i class="fa-solid fa-key"></i></span>
						</div><input type="password" id="passinp" class="form-control" name="password" placeholder="Password">
						<div id="pass_inv" class="invalid-feedback">
						Password cannot be empty.
						</div>

					</div>

				</div>
				<button type="submit" class="btn btn-primary btn-block">Log-In</button>
			</form>
			</div>
		</div>
	</div>
</div>
</div></section>

<script>
	var form = document.getElementById('login');
	var pinp = document.getElementById('passinp');
	var uinp = document.getElementById('userinp');
	
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

<?php require_once "../footer.php";