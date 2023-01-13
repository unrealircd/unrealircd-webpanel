<?php
require_once "../common.php";
require_once "../header.php";

$title = "User Lookup";
$nickname = "";
$nick = NULL;
if (isset($_GET['nick']))
{
	$nickname = $_GET['nick'];
	$nick = $rpc->user()->get($nickname);
	if (!$nick)
	{
		Message::Fail("Could not find user: \"$nickname\"");
	} else {
		$nickname = $nick->name;
		$title .= " for \"" . $nickname . "\"";
	}
}
?>
<title><?php echo $title; ?></title>
<h4><?php echo $title; ?></h4>
<br>
<form method="get" action="details.php">
<div class="input-group short-form-control justify-content-center align-items-center">
	<input style="margin: 0%; height: 24px;" class="left-pan form-control" id="nick" name="nick" type="text" value=<?php echo $nickname; ?>>
	<div class="input-group-append">
		<br><button type="submit" class="btn btn-primary">Go</button>
	</div>
</div>
</form>

<?php if (!$nick)
	return; ?>
<br>
<div class="container-xxl">
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
    <div class="col-sm-3">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Channels</h5>
            <p class="card-text"><?php generate_html_userchannels($nick); ?></p>
          </div>
        </div>
      </div>
  </div>
</div>
<?php 
	require_once("../footer.php");

