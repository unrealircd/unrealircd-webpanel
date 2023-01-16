<?php
require_once "../common.php";
require_once "../header.php";

$title = "Channel Lookup";
$channel = "";
$nick = NULL;
do_log($_GET);
if (isset($_GET['chan']))
{
	$channel = $_GET['chan'];
	$channel = $rpc->channel()->get($channel);
	if (!$channel)
	{
		Message::Fail("Could not find channel: \"$channel\"");
	} else {
		$channame = $channel->name;
		$title .= " for \"" . $channame . "\"";
	}
}
?>
<title><?php echo $title; ?></title>
<h4><?php echo $title; ?></h4>
<br>

<form method="get" action="details.php">
<div class="input-group short-form-control justify-content-center align-items-center">
	<input style="margin: 0%; height: 24px;" class="left-pan form-control" id="chan" name="chan" type="text" value="<?php echo $channame; ?>">
	<div class="input-group-append">
		<br><button type="submit" class="btn btn-primary">Go</button>
	</div>
</div>
</form>

<?php if (!$channel)
		return; ?>

<br>
<div class="container-xxl">
	<div class="row">
		<div class="col-sm-6">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">Basic Information</h5>
					<p class="card-text"><?php //generate_html_chaninfo($nick); ?></p>
				</div>
			</div>
		</div>
		<div class="col-sm-5">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">Channel Settings</h5>
					<p class="card-text"><?php //generate_html_chansettings($nick); ?></p>
				</div>
			</div>
		</div>
	</div>
</div><br>
<div class="container-xxl">
	<div class="row">
		<div class="col">
				<div class="card">
					<div class="card-body">
						<h5 class="card-title">Occupants</h5>
						<p class="card-text"><?php //generate_html_channelusers($nick); ?></p>
					</div>
				</div>
			</div>
	</div>
</div>
<?php 
	require_once("../footer.php");

