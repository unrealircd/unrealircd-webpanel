<?php $arr = []; Hook::run(HOOKTYPE_PRE_HEADER, $arr); ?>
<!DOCTYPE html>
<head>
<div class="media">
<div class="media-body">

	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="HandheldFriendly" content="true">

<link href="<?php echo BASE_URL; ?>css/unrealircd-admin.css" rel="stylesheet">


<!-- Font Awesome JS -->
<script defer src="https://use.fontawesome.com/releases/v6.2.1/js/solid.js" integrity="sha384-tzzSw1/Vo+0N5UhStP3bvwWPq+uvzCMfrN1fEFe+xBmv1C/AtVX5K0uZtmcHitFZ" crossorigin="anonymous"></script>
<script defer src="https://use.fontawesome.com/releases/v6.2.1/js/fontawesome.js" integrity="sha384-6OIrr52G08NpOFSZdxxz1xdNSndlD4vdcf/q2myIUVO0VsqaGHJsB0RaBE01VTOY" crossorigin="anonymous"></script>

 <!-- Latest compiled and minified CSS -->
 <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

<!-- Font Awesome icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css">
<script src="<?php echo BASE_URL; ?>js/unrealircd-admin.js"></script>
<title>UnrealIRCd Panel</title>
<link rel="icon" type="image/x-icon" href="<?php echo BASE_URL; ?>img/favicon.ico">
</head>
<body role="document">

<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<!-- Popper.JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js" integrity="sha384-cs/chFZiN24E4KMATLdqdvsezGxaGsi4hLGOzlXwp5UZB1LY//20VyM2taTB4QvJ" crossorigin="anonymous"></script>
<!-- Bootstrap JS -->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/js/bootstrap.min.js" integrity="sha384-uefMccjFJAIv6A+rW+L4AHf99KvxDjWSu1z9VI8SKNVmz4sk7buKt/6v9KI65qnm" crossorigin="anonymous"></script>

<div class="container-fluid">
	
	<!-- Fixed navbar -->
	<nav class="navbar navbar-expand-sm navbar-dark bg-dark fixed-top z-index padding-top"><a class="navbar-brand" href="<?php echo BASE_URL; ?>"><img src="<?php echo BASE_URL; ?>img/favicon.ico" height="25" width="25"> UnrealIRCd Admin Panel</a>
		<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#collapsibleNavbar">
			<span class="navbar-toggler-icon"></span>
		</button>
		<div class="collapse navbar-collapse" id="collapsibleNavbar">
			<ul class="navbar-nav mr-auto">
				
<?php

$active_page = NULL;

foreach ($pages as $name => $page)
{
	$script = $_SERVER['SCRIPT_FILENAME'];
	$tok = split($script, "/");
	if (is_array($page))
		continue;
	if (is_string($page) && strlen($page) == 0) {
		$active_page = "";
	}
	else if (str_ends_with($script, BASE_URL . "index.php") && BASE_URL != "/" && !strlen($tok[0]))
	{
		$active_page = $tok[0];
	}
	else if (!str_ends_with($page, ".php"))
	{
		$script2 = rtrim($script, "/index.php");
		if (str_ends_with($script2, $page))
			$active_page = $page;
	}
	else if (str_ends_with($script, $page))
	{
		$active_page = $page;
	} elseif (!$active_page)
		$active_page = false;
}
foreach($pages as $name=>$page)
{
	$class = "class=\"nav-item\"";
	if (is_string($active_page) && $page == $active_page)
		$class = str_replace("\"nav-item\"", "\"nav-item active\"", $class);
	
	if (is_string($page))
		echo "<li $class><a class=\"nav-link\" href=\"".BASE_URL.$page."\">$name</a></li>\n";

	elseif (is_array($page))
	{
		foreach ($page as $k => $v)
		{
			$first_page = $v;
			break;
		}
		?>
		<li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          <?php echo $name; ?>
        </a>
        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
			<?php foreach($page as $k => $p)
			{
				?>
					<a class="dropdown-item" href="<?php echo BASE_URL.$p;?>"><?php echo $k; ?></a>
				<?php
			} ?>
        </div>
      </li>
	  <?php
		
	}

}
$ToD = time_of_day();
$user = unreal_get_current_user();
if ($user)
{
	$name = (strlen($user->first_name)) ? $user->first_name : $user->username; // address them by first name, else username
}
?>
	
		</ul>
		
		
		<?php if ($user) { ?>
			<div class="nav-item form-inline my-2 my-lg-0 mr-sm-2">
				<div class="collapse navbar-collapse" id="collapsibleNavbar">
					<ul class="navbar-nav mr-auto">
						<li class="nav-item dropdown">
							<h6 style="color:white;">Good <?php echo "$ToD, $name!"; ?></h6>
						</li>
					</ul>
				</div>
			</div>
		<?php } ?>
	</nav><br>
</div>

<div class="container-fluid" role="main">
