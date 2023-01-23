
<?php
require_once "../../common.php";
require_once "SQL/user.php";

$logout = false;
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
	$user = new SQLA_User($_POST['username']);
	
	/* not being too informative with the login error in case of attackers */
	if (!$user->id)
	{
		$failmsg = "Incorrect login";
	}
	else if ($user->password_verify($_POST['password']))
	{
		$_SESSION['id'] = $user->id;
		header('Location: ' . BASE_URL);
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
</head><div class="text-center">
<a href="<?php echo BASE_URL; ?>plugins/sql_auth/login.php"><button type="button" style="margin:0; top:50%; position: absolute;" class="btn  btn-primary" data-bs-toggle="modal" data-bs-target="#loginModaltitle">
  Login to continue
</button></a>
</div>
<script>
	$(document).ready(function(){
		$("#loginModal").modal('show');
	});
</script>
<body role="document">
<div class="container-fluid">
<form method="post" action="login.php">
  <div class="modal" id="loginModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="loginModal" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
	  <div class="modal-content">
		<div class="modal-header" style="margin: 0 auto;">
		  <h3 class="modal-title" id="loginModaltitle"><img src="<?php echo BASE_URL; ?>img/favicon.ico">  Log in to use Admin Panel</h3>
		</div>
		<div class="modal-body">
			<div class="form-group">
			  <?php 
				if (isset($failmsg)) Message::Fail($failmsg);
				if ($logout)
				  Message::Success("You have been logged out");
			  ?>
			  <label for="username">Username / Nick:</label>
			  <input style="width:90%;" type="text" class="form-control" name="username" id="username" >
			</div>
			<div class="form-group">
			  <label for="password">Password:</label>
			  <input style="width:90%;" type="password" class="form-control" name="password" id="password">
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
<?php require_once "../../footer.php";