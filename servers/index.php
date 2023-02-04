<?php
require_once "../common.php";
require_once UPATH . "/header.php";

$rehash_errors = [];
$rehash_warnings = [];
$rehash_success = [];

if (!empty($_POST))
{
	do_log($_POST);
	if (isset($_POST['rehash']))
		foreach ($_POST['serverch'] as $servID)
			if ($response = $rpc->server()->rehash($servID)) 
			{
				$serb = $rpc->server()->get($servID);
				do_log($servID, $response);
				if ($response->success || (!isset($response->success) != false && $response == true))
				{
					$rehash_success[] = $serb->name;
					foreach($response->log as $log)
					{
						do_log($log->level);
						if ($log->level == "warn")
							$rehash_warnings[$log->log_source][] = $log->msg;
					}
				}
				else if (isset($response->success) && !$response->success)
				{
					foreach ($response->log as $log)
					{
						if ($log->level == "error")
							$rehash_errors[$log->log_source][] = $log->msg;
					}
				}		 
			}
}
$checkforupdates = (isset($_POST['checkforupdates'])) ? true : false;
/* Get the server list */
$servers = $rpc->server()->getAll();
$latest = 0;
if ($checkforupdates)
{
	$latest = get_unreal_latest_version();
}
?>
<h4>Servers Overview</h4>
<?php
	if (isset($_POST['rehash']))
	{
	if (!empty($rehash_success)) {
		do_log($rehash_success);
		$servlist_bullet = "<ol>";

		foreach ($rehash_success as $serv) {
			$servlist_bullet .= "<li>$serv</li>";
		}
		$servlist_bullet .= "</ol>";
		$servlist_err_bullet = "";
		foreach ($rehash_errors as $serv => $err) {
			$servlist_err_bullet .= "<h6>$serv</h6><ol>";
			foreach ($err as $er)
				$servlist_err_bullet .= "<li>$er</li>";
			echo "</ol>";
		}
		$servlist_warn_bullet = ""; foreach ($rehash_warnings as $server => $warning) {
			$servlist_warn_bullet .= "<h6>$serv</h6><ol>";
			foreach ($warning as $w)
				$servlist_warn_bullet .= "<li>$w</li>";
			$servlist_warn_bullet .= "</ol>";
		}
		if (!empty($rehash_success))
			Message::Success(
				"The following server(s) were successfully rehashed:",
				$servlist_bullet
			);
		if (!empty($rehash_warnings))
			Message::Warning(
				"The following warning(s) were encountered:",
				$servlist_warn_bullet
			);
		if (!empty($rehash_errors))
			Message::Fail(
				"The following error(s) were encountered and the server(s) failed to rehash:",
				$servlist_err_bullet
			);
		}
	}
	if (isset($_POST['sf_name']) && strlen($_POST['sf_name']))
		Message::Info("Listing servers which match name: \"" . $_POST['sf_name'] . "\"");

	?>
Click on a server name to view more information.

<div id="Servers">
	
	
	<table class="container-xxl table table-sm table-responsive caption-top table-striped">
	<thead>
		<th scope="col"><h5>Filter:</h5></th>
		<form action="" method="post">
		<th scope="col" colspan="2">Name: <input name="sf_name" type="text" class="short-form-control">
		<th scope="col"> <input class="btn btn-primary btn-sm" type="submit" value="Search"></th></form>
	</thead></table>
	<form action="index.php" method="post">
		<div class="btn btn-sm btn-warning" data-toggle="modal" data-target="#rehash_modal"><i class="fa-solid fa-arrows-rotate"></i> Rehash Selected</div>
		<button name="checkforupdates" type="submit" class="btn btn-sm btn-info"><i class="fa-solid fa-cloud-arrow-down"></i> Check for upgrades</div><br>

		<div class="modal fade" id="rehash_modal" tabindex="-1" role="dialog" aria-labelledby="confirmModalCenterTitle" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="myModalLabel">Rehash Selected Servers</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						Are you sure you want to rehash the selected servers?	
					</div>
					<div class="modal-footer">
							<button id="CloseButton" type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
							<button type="submit" class="btn btn-primary" name="rehash">Rehash Selected</button>
					</div>
				</div>
			</div>
		</div>
	<table class="container-xxl table table-sm table-responsive caption-top table-striped">
	<thead class="table-primary">
		<th scope="col"><input type="checkbox" label='selectall' onClick="toggle_server(this)" /></th>
		<th scope="col">Name</th>
		<th scope="col">Users</th>
		<th scope="col">Version</th>
		<th scope="col">Connected to</th>
		<th scope="col">Up since</th>
	</thead>
	
	<tbody>
	<?php

		foreach($servers as $server)
		{

		
			/* Some basic filtering for NAME */
			if (isset($_POST['sf_name']) && strlen($_POST['sf_name']) && 
			strpos(strtolower($server->name), strtolower($_POST['sf_name'])) !== 0 &&
			strpos(strtolower($server->name), strtolower($_POST['sf_name'])) == false)
				continue;

			$update = "";
			if ($checkforupdates && $latest)
			{
				
				$tok = split($server->server->features->software, "-");
				if (!strcasecmp($tok[0],"unrealircd"))
				{
					if ($latest > $tok[1])
						$update = " <i class=\"fa-solid fa-cloud-arrow-down\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"Upgrade available!\"></i>";
				}
			}

			echo "<tr>";
			echo "<th scope=\"row\"><input type=\"checkbox\" value='$server->id' name=\"serverch[]\"></th>";
			echo "<td><a href=\"details.php?server=".$server->id."\">$server->name</a> $update</td>";
			echo "<td>".$server->server->num_users."</td>";
			
			$s = sinfo_conv_version_string($server);
			
			echo "<td>$s</td>";
			if (isset($server->server->uplink))
				echo "<td>".$server->server->uplink."</td>";
			else
				echo "<td></td>"; /* self */
			echo "<td>".$server->server->boot_time."</td>";
		}
	?>
	</form>
	</tbody></table>
</div>

<?php require_once UPATH.'/footer.php'; ?>
