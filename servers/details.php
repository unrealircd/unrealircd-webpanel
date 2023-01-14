<?php
require_once "../common.php";
require_once "../header.php";

$title = "Server Lookup";
$servername = "";
$srv = NULL;
if (isset($_POST))
{
  if (isset($_POST['disconnect']))
  {
    if ($rpc->server()->disconnect($_POST['disconnect'], $_POST['reason']))
      Message::Success("Server \"".$_POST['disconnect']."\" has been successfully disconnected from the network.");
    else
      Message::Fail($rpc->error);
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
		$servername = $srv->name;
		$title .= " for \"" . $servername . "\"";
	}
}
?>
<title><?php echo $title; ?></title>
<h4><?php echo $title; ?></h4>
<br>
<form method="get" action="details.php">
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
<div class="row">
  <div class="col-sm-3">
    <div class="btn btn-sm btn-info" data-toggle="modal" data-target="#module_modal">Modules</div>  
    <div class="btn btn-sm btn-danger" data-toggle="modal" data-target="#disconnect_modal">Disconnect</div>
  </div>
</div>
<br>
<div class="row">
  <div class="col-sm-3">
    <div class="card">
      <div class="card-body">
        <h5 class="card-title">Basic Information</h5>
        <p class="card-text"><?php generate_html_serverinfo($srv); ?></p>
      </div>
    </div>
  </div>
  <div class="col-sm-4">
    <div class="card">
      <div class="card-body">
        <h5 class="card-title">Channel Modes</h5>
        <p class="card-text"><?php generate_html_servermodes($srv); ?></p>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="disconnect_modal" tabindex="-1" role="dialog" aria-labelledby="confirmModalCenterTitle" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
		<div class="modal-header">
			<h5 class="modal-title" id="myModalLabel">Disconnect Server<?php echo $srv->name; ?></h5>
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

<div class="modal fade" id="module_modal" tabindex="-1" role="dialog" aria-labelledby="confirmModalCenterTitle" aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered container-fluid" role="document">
		<div class="modal-content">
		<div class="modal-header">
			<h5 class="modal-title" id="myModalLabel">Server Modules</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			<span aria-hidden="true">&times;</span>
			</button>
		</div>
		<div class="modal-body">
			<?php generate_html_modlist($srv); ?>
		</div>
		<div class="modal-footer">
			  <button id="CloseButton" action="post" type="submit" class="btn btn-secondary" data-dismiss="modal">Close</button>
		</div>
		</div>
	</div>
</div>

<div class="modal fade" id="module_modal" tabindex="-1" role="dialog" aria-labelledby="confirmModalCenterTitle" aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered container-fluid" role="document">
		<div class="modal-content">
		<div class="modal-header">
			<h5 class="modal-title" id="myModalLabel">Rehash Server"</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			<span aria-hidden="true">&times;</span>
			</button>
		</div>
		<div class="modal-body">
			<?php generate_html_modlist($srv); ?>
		</div>
		<div class="modal-footer">
			  <button id="CloseButton" action="post" type="submit" class="btn btn-secondary" data-dismiss="modal">Close</button>
		</div>
		</div>
	</div>
</div>