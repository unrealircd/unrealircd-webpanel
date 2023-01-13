<?php
require_once "../common.php";
require_once "../header.php";

$title = "Server Lookup";
$servername = "";
$nick = NULL;
if (isset($_GET['server']))
{
	$servername = $_GET['server'];
	$nick = $rpc->server()->get($servername);
  echo highlight_string("<?php ".var_export($nick, true));
	if (!$nick)
	{
		Message::Fail("Could not find server: \"$servername\"");
	} else {
		$servername = $nick->name;
		$title .= " for \"" . $servername . "\"";
	}
}
?>
<title><?php echo $title; ?></title>
<h4><?php echo $title; ?></h4>
<br>
<form method="get" action="details.php">
<div class="input-group short-form-control justify-content-center align-items-center">
	<input style="margin: 0%; height: 24px;" class="left-pan form-control" id="nick" name="nick" type="text" value=<?php echo $servername; ?>>
	<div class="input-group-append">
		<br><button type="submit" class="btn btn-primary">Go</button>
	</div>
</div>
</form>

<?php if (!$nick)
	return; ?>
<br>
<div class="row">
  <div class="col-sm-3">
    <div class="card">
      <div class="card-body">
        <h5 class="card-title">Basic Information</h5>
        <p class="card-text"><?php generate_html_whois($nick); ?></p>
      </div>
    </div>
  </div>
  <div class="col-sm-4">
    <div class="card">
      <div class="card-body">
        <h5 class="card-title">User Settings</h5>
        <p class="card-text"><?php generate_html_usersettings($nick); ?></p>
      </div>
    </div>
  </div>
</div>
<div class="row">
  <div class="col-sm-3">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">User Settings</h5>
          <p class="card-text"><?php generate_html_usersettings($nick); ?></p>
        </div>
      </div>
    </div>
</div>