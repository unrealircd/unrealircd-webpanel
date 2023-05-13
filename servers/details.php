<?php
require_once "../inc/common.php";
require_once "../inc/connection.php";
require_once "../inc/header.php";

$title = "Server Lookup";
$servername = "";
$srv = NULL;

$rehash_errors = [];
$rehash_warnings = [];
$rehash_success = [];

if (isset($_POST))
{
	if (isset($_POST['rehash']))
	{
		$servID = $_POST['rehash'];
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
	if (isset($_POST['disconnect']))
	{
		if ($rpc->server()->disconnect($_POST['disconnect'], $_POST['reason']))
			Message::Success("Server \"".$_POST['disconnect']."\" has been successfully disconnected from the network.");
		else
			Message::Fail((isset($rpc->error)) ? $rpc->error : "No error");
	}
	
}
if (isset($_GET['server']))
{
	$servername = $_GET['server'];
	$srv = $rpc->server()->get($servername);
	
	if (!$srv)
	{
		Message::Fail("Could not find server: \"$servername\"");
	}
	else {
		do_log($srv);
		$servername = $srv->name;
		$title .= " for \"" . $servername . "\"";
	}
}
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
?>
<title><?php echo $title; ?></title>
<h4><?php echo $title; ?></h4>
<br>
<form method="get" action="details.php">
<div class="input-group short-form-control">
	<input class="short-form-control" id="server" name="server" type="text" value=<?php echo $servername; ?>>
	<div class="input-group-append">
		<br><button type="submit" class="btn btn-primary">Go</button>
	</div>
</div>
</form>

<?php if (!$srv)
{
	require_once UPATH.'/inc/footer.php';
	return;
}
?>
<br>
<div class="row">
	<div class="col-sm-3">
		<div class="btn btn-sm btn-warning" data-toggle="modal" data-target="#rehash_modal">Rehash</div>
		<div class="btn btn-sm btn-danger" data-toggle="modal" data-target="#disconnect_modal">Disconnect</div>
	</div>
</div>
<br>
<div class="modal fade" id="disconnect_modal" tabindex="-1" role="dialog" aria-labelledby="confirmModalCenterTitle" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
		<div class="modal-header">
			<h5 class="modal-title" id="myModalLabel">Disconnect Server</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			<span aria-hidden="true">&times;</span>
			</button>
		</div>
		<div class="modal-body">
			<form method="post">
			Please enter a reason for disconnecting "<?php echo $srv->name; ?>"?
			<input type="text" class="short-form-control form-control" id="reason" name="reason" value="No reason">
		</div>
		<div class="modal-footer">
				<input type="hidden" id="server" name="disconnect" value="<?php echo $srv->name; ?>"></input>
				<button id="CloseButton" type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
				<button type="submit" action="post" class="btn btn-danger">Disconnect</button>
			</form>
		</div>
		</div>
	</div>
</div>

<div class="modal fade" id="rehash_modal" tabindex="-1" role="dialog" aria-labelledby="confirmModalCenterTitle" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
		<div class="modal-header">
			<h5 class="modal-title" id="myModalLabel">Rehash Server</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			<span aria-hidden="true">&times;</span>
			</button>
		</div>
		<div class="modal-body">
			<form method="post">
			Are you sure you want to rehash <?php echo $srv->name; ?>?
		</div>
		<div class="modal-footer">
				<input type="hidden" id="server" name="rehash" value="<?php echo $srv->name; ?>"></input>
				<button id="CloseButton" type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
				<button type="submit" action="post" class="btn btn-primary">Rehash</button>
			</form>
		</div>
		</div>
	</div>
</div>

<div class="container-xxl">
  <div class="row">
    
    <div class="col-sm-8">
      <div class="card">
        <div class="card-body">
        	<h6 class="card-title">Server Settings</h6>
			<ul class="nav nav-tabs" role="tablist">
				<li class="nav-item" role="presentation"><a class="nav-link" href="#servermodes" aria-controls="servermodes" role="tab" data-toggle="tab">Info</a></li>
				<li class="nav-item" role="presentation"><a class="nav-link" href="#serverinv" aria-controls="serverinv" role="tab" data-toggle="tab">Channel Modes</a></li>
				<li class="nav-item" role="presentation"><a class="nav-link" href="#serverex" aria-controls="serverex" role="tab" data-toggle="tab">User Modes</a></li>
				<li class="nav-item" role="presentation"><a class="nav-link" href="#serverbans" aria-controls="serverbans" role="tab" data-toggle="tab">Modules</a></li>
			</ul>
		
<div class="tab-content">
	<br>
	<div class="tab-pane fade in" id="servermodes">
		
		<p class="card-text row">
			<div class="row" style="margin-left:5px">
				<h4>Server information</h4>
				<?php generate_html_serverinfo($srv); ?>
				<h4>Extra information</h4>
				<?php generate_html_extserverinfo($srv); ?>
			</div>
		</p>
	</div><form id="editservermodes" method="post" name="editservermodes">
	<div class="tab-pane" style="display: none" id="servermodes_edit">
		
			<div class="btn btn-sm btn-secondary" id="editchmodesbk">Go back</div>
			<button type="submit" class="btn btn-sm btn-primary">Save Settings</button>
			<p class="card-text"><div></div></p>
		</form>
	</div>
	
	<div class="tab-pane fade in" id="serverbans">
		<p class="card-text"><?php generate_html_modlist($srv); ?></p>
	</div>
	<div class="tab-pane fade in" id="serverinv">
		<p class="card-text"><?php generate_html_servermodes($srv); ?></p>
	</div>
	<div class="tab-pane fade in" id="serverex">
		<p class="card-text"><?php generate_html_usermodes($srv); ?></p>
	</div>
	<div class="tab-pane fade in" id="servermodes_edit">
		<p class="card-text"><?php /* insert hacks here */ ?></p>
	</div>

</div>
</div>
</div>

	</div>
</div>
<?php require_once UPATH.'/inc/footer.php'; ?>
<script>
    // show dat first tab
$('.nav-tabs a[href="#servermodes"]').tab('show')
</script>