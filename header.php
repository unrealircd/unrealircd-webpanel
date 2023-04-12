<?php
if (is_auth_provided() && !str_ends_with($_SERVER['SCRIPT_FILENAME'], "setup.php"))
{?>
	<script>
		var BASE_URL = "<?php echo get_config("base_url"); ?>";
		function timeoutCheck() {
			var xhttp = new XMLHttpRequest();
			xhttp.onreadystatechange = function() {
				if (this.readyState == 4 && this.status == 200) {
					var data = JSON.parse(this.responseText);
					if (data.session == 'none')
						window.location = get_config("base_url") + 'login/?timeout=1&redirect=' + encodeURIComponent(window.location.pathname);
				}
			};
			xhttp.open("GET", BASE_URL + "api/timeout.php", true);
			xhttp.send();
		}
		timeoutCheck();
		setInterval(timeoutCheck, 15000);
	</script>
<?php }
$arr = []; Hook::run(HOOKTYPE_PRE_HEADER, $arr); ?>
<!DOCTYPE html>
<head>
<div class="media">
<div class="media-body">

	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="HandheldFriendly" content="true">

<link href="<?php echo get_config("base_url"); ?>css/unrealircd-admin.css" rel="stylesheet">


 <!-- Latest compiled and minified CSS -->
 <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

<!-- Font Awesome JS -->
<script defer src="https://use.fontawesome.com/releases/v6.2.1/js/solid.js" integrity="sha384-tzzSw1/Vo+0N5UhStP3bvwWPq+uvzCMfrN1fEFe+xBmv1C/AtVX5K0uZtmcHitFZ" crossorigin="anonymous"></script>
<script defer src="https://use.fontawesome.com/releases/v6.2.1/js/fontawesome.js" integrity="sha384-6OIrr52G08NpOFSZdxxz1xdNSndlD4vdcf/q2myIUVO0VsqaGHJsB0RaBE01VTOY" crossorigin="anonymous"></script>

<!-- Font Awesome icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css">
<title>UnrealIRCd Panel</title>
<link rel="icon" type="image/x-icon" href="<?php echo get_config("base_url"); ?>img/favicon.ico">
</head>
<body role="document">

<script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js" integrity="sha384-+sLIOodYLS7CIrQpBjl+C7nPvqq+FbNUBDunl/OZv93DB7Ln/533i8e/mZXLi/P+" crossorigin="anonymous"></script>
<script src="<?php echo get_config("base_url"); ?>js/unrealircd-admin.js"></script>
<style>
	#optionsopen {
		transition: left 0.3s;
	}
	#optionsclose {
		transition: left 0.3s;
	}
	.w3-sidebar {
		top: 50px;
		color: white;
		transition: left 0.3s;
	}
	.container-fluid {
		transition: padding-left 0.3s;
	}
	.list-group-item-action {
		color: #e0e0e0;
	}
</style>
<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
<nav id="sidebarlol" style="left: 0" class="w3-sidebar navbar-expand-sm bg-dark padding-top me-5 ma-5">
<div class="sidebarlol list-group">
	<div class="badge badge-secondary rounded-pill">Main Menu</div>
	<?php 

function show_page_item($name, $page, $nestlevel)
{
	$active_page = NULL;
	$icon = $style = "";
	$class = "nav-link nav-item";
	if (is_string($active_page) && $page == $active_page)
		$class .= " active";

	if ($nestlevel > 0)
	{
		echo "<small>";
		$name = "&nbsp; ".$name;
		$style = "padding-bottom: 1px; padding-top: 1px";
	} else {
		echo "<b>";
	}
	if (is_array($page))
	{
		$style = "padding-bottom: 0px;";
	} else {
		echo "<a href=\"".get_config("base_url").$page."\" style=\"text-decoration: none\">\n";
	}
	echo "<div class=\"d-flex justify-content-between align-items-center $class list-group-item-action\" style=\"$style\">$name
		<div class=\"text-right padding-top\">
			<i class=\"fa fa-$icon\"></i>
		</div></div>\n";
	if (!is_array($page))
		echo "</a>";
	if ($nestlevel > 0)
		echo "</small>";
	else
		echo "</b>";
	if (is_array($page))
	{
		foreach ($page as $subname=>$subpage)
			show_page_item($subname, $subpage, 1);
	}
}
foreach($pages as $name=>$page)
	show_page_item($name, $page, 0);
?>
</div>
</nav>

<div class="container-fluid">
	
	<!-- Fixed navbar -->
	<nav class="navbar navbar-expand-sm navbar-dark bg-dark fixed-top z-index padding-top" style="max-height: 50px">
	<a class="navbar-brand" href="<?php echo get_config("base_url"); ?>"><img src="<?php echo get_config("base_url"); ?>img/favicon.ico" height="25" width="25"> UnrealIRCd Admin Panel</a>
		<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#collapsibleNavbar">
			<span class="navbar-toggler-icon"></span>
		</button>
		<div class="collapse navbar-collapse" id="collapsibleNavbar">
			<ul class="navbar-nav mr-auto">
				
<?php

foreach ($pages as $name => $page)
{
	$script = $_SERVER['SCRIPT_FILENAME'];
	$tok = split($script, "/");
	if (is_array($page))
		continue;
	if (is_string($page) && strlen($page) == 0) {
		$active_page = "";
	}
	else if (str_ends_with($script, get_config("base_url") . "index.php") && get_config("base_url") != "/" && !strlen($tok[0]))
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

$ToD = time_of_day();
$user = unreal_get_current_user();
if ($user)
{
	$name = ($user->first_name && strlen($user->first_name)) ? $user->first_name : $user->username; // address them by first name, else username
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

<div id="main_contain" class="container-fluid" style="padding-left: 210px" role="main">

