
<?php
require_once "../common.php";

$logout = false;
$redirect = (isset($_GET['redirect'])) ? $_GET['redirect'] : BASE_URL;

if (!empty($_GET['logout']))
{
	if (!isset($_SESSION['id']))
		$failmsg = "Nothing to logout from";
	else {
		session_destroy();
		$logout = true;
	}
}
if (!empty($_POST))
{
	if ($_POST['username'] && $_POST['password'])
	{
		
		/* securitah */
		security_check();
		$user = new PanelUser($_POST['username']);
		
		/* not being too informative with the login error in case of attackers */
		if (!$user->id)
		{
			$failmsg = "Incorrect login";
		}
		else if ($user->password_verify($_POST['password']))
		{
			$_SESSION['id'] = $user->id;
			header('Location: ' . $redirect);
			$user->add_meta("last_login", date("Y-m-d m:i:s"));
		}
		else
		{
			$failmsg = "Incorrect login";
		}

	}
	else
	$failmsg = "Couldn't log you in: Missing credentials";
}

?><!DOCTYPE html>
<head>
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

<script src="<?php echo BASE_URL; ?>js/unrealircd-admin.js"></script>
<title>UnrealIRCd Panel</title>
<link rel="icon" type="image/x-icon" href="<?php echo BASE_URL; ?>img/favicon.ico">
<link href="<?php echo BASE_URL; ?>css/unrealircd-admin.css" rel="stylesheet">
</head>
<script>
	$(document).ready(function(){
		$("#loginModal").modal({backdrop: 'static', keyboard: false}, 'show');
	});

</script>
<body role="document">
<div class="container-fluid">
<form method="post" action="index.php?redirect=<?php echo $redirect; ?>">
	<div class="modal" id="loginModal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="loginModal" aria-hidden="false"></a>
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
		<div class="modal-header" style="margin: 0 auto;">
			<h3 class="modal-title" id="loginModaltitle"><img src="<?php echo BASE_URL; ?>img/favicon.ico">	Log in to use Admin Panel</h3>
		</div>
		<div class="modal-body">
			<div class="form-group">
				<?php 
				if (isset($failmsg)) Message::Fail($failmsg);
				if ($logout)
					Message::Success("You have been logged out");
				?>
				<br>
				<div class="input-group mb-3">
					<div class="input-group-prepend">
						<span class="input-group-text" id="basic-addon1"><i class="fa-solid fa-user"></i></span>
					</div><input type="text" class="form-control" name="username" id="username" placeholder="Username" aria-label="Username" aria-describedby="basic-addon1">
				</div>
				
			</div>
			<div class="form-group">
				<div class="input-group mb-3">
					<div class="input-group-prepend">
						<span class="input-group-text" id="basic-addon1"><i class="fa-solid fa-key"></i></span>
					</div><input type="password" class="form-control" name="password" id="password" placeholder="Password">
				</div>

			</div>
		</div>
		<div class="modal-footer">
			<a class="btn btn-secondary" href="#">Cancel</a>
			<button type="submit" class="btn btn-primary">Log-In</button>
		</div>
		</div>
	</div>
	</div>
</form>
<?php require_once "../footer.php";