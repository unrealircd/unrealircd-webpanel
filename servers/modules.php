<?php
require_once "../common.php";
require_once "../header.php";

$title = "Modules";
$servername = "";
$srv = NULL;
if (isset($_GET['server']))
{
	$servername = $_GET['server'];
	$srv = $rpc->server()->get($servername);
	if (!$srv)
	{
		Message::Fail("Could not find server: \"$servername\"");
	} else {

    $modules = $rpc->server()->module_list($srv->id);
    if (!$modules->list)
    {
      Message::Fail("$rpc->error");
    }
		$servername = $srv->name;
		$title .= " for \"" . $servername . "\"";
	}
}
?>
<title><?php echo $title; ?></title>
<h4><?php echo $title; ?></h4>
<br>
<form method="get" action="modules.php">
<div class="input-group short-form-control justify-content-center align-items-center">
	<input style="margin: 0%; height: 24px;" class="left-pan form-control" id="server" name="server" type="text" value=<?php echo $servername; ?>>
	<div class="input-group-append">
		<br><button type="submit" class="btn btn-primary">Go</button>
	</div>
</div>
</form>

<?php if (!$srv)
	return; ?>
<br>
<?php generate_html_modlist($srv); ?>