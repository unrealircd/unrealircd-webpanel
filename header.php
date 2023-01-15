<!DOCTYPE html>
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
<body role="document">
<div class="container-fluid">
	
	<!-- Fixed navbar -->
	<nav class="navbar navbar-expand-sm navbar-dark bg-dark fixed-top">
		<ul class="nav navbar-nav">
			<a class="navbar-brand" href="<?php echo BASE_URL; ?>/index.php"><img src="<?php echo BASE_URL; ?>img/favicon.ico" height="25" width="25"> UnrealIRCd Admin Panel</a>
<?php

$active_page = NULL;
/* Needs to be a separate step due to multiple matches */
foreach ($pages as $name => $page)
{
	$script = $_SERVER['SCRIPT_FILENAME'];
	$tok = split($script, "/");
	if (strlen($page) == 0) {
		$active_page = "";
	}
	else if (str_ends_with($script, BASE_URL . "index.php") && BASE_URL != "/" && !strlen($tok[0]))
	{
		echo "2";
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
	
	echo "			<li $class><a class=\"nav-link\" href=\"".BASE_URL.$page."\">$name</a></li> \n";
}
?>
	
		</ul>
	</nav><br>
</div>

<div class="container-fluid" role="main">
