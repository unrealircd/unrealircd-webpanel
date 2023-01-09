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

<title>UnrealIRCd Panel</title>
<link rel="icon" type="image/x-icon" href="/img/favicon.ico">
<link href="css/unrealircd-admin.css" rel="stylesheet">
</head>
<body role="document">
<div class="container">
    <!-- Fixed navbar -->
    <nav class="navbar navbar-expand-sm navbar-light bg-primary fixed-top">
        <ul class="nav navbar-nav">
<?php
foreach($pages as $name=>$page)
{
	$active = '';
	if (str_ends_with($_SERVER['SCRIPT_FILENAME'], $page))
	{
		$active = " class=\"active\"";
	}
	echo "            <li class=\"nav-item\"".$active."><a class=\"nav-link\" href=\"$page\">$name</a></li>\n";
}

?>
        </ul>
    </nav>
</div>

<div class="container-fluid" role="main">
