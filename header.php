<?php $arr = []; Hook::run(HOOKTYPE_PRE_HEADER, $arr); ?>
<!DOCTYPE html>
<head>
<div class="media">
<div class="media-body">

	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="HandheldFriendly" content="true">


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
	<nav class="navbar navbar-expand-sm navbar-dark bg-dark fixed-top z-index padding-top">
		<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#collapsibleNavbar">
			<span class="navbar-toggler-icon"></span>
		</button>
		<div class="collapse navbar-collapse" id="collapsibleNavbar">
			<ul class="navbar-nav mr-auto">
				<a class="navbar-brand" href="<?php echo BASE_URL; ?>"><img src="<?php echo BASE_URL; ?>img/favicon.ico" height="25" width="25"> UnrealIRCd Admin Panel</a>
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
?>
	
		</ul></div>
	</nav><br>
</div>

<div class="container-fluid" role="main">
