<!DOCTYPE html>
<title>UnrealIRCd Panel</title>
<link rel="icon" type="image/x-icon" href="/img/favicon.ico">
<link href="css/unrealircd-admin.css" rel="stylesheet">
<body class="body-for-sticky">
<div id="headerContainer">
<h2><a href="index.php">UnrealIRCd <small>Administration Panel</small></a></h2></div>
<script src="js/unrealircd-admin.js" defer></script>
<div class="topnav">
	
<?php
foreach($pages as $name=>$page)
{
	$active = '';
	if (str_ends_with($_SERVER['SCRIPT_FILENAME'], $page))
	{
		$active = "class=\"active\" ";
	}
	echo "<a ".$active."href=\"$page\">$name</a>\n";
}

?>
</div>
