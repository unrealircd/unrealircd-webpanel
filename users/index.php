<?php
require_once "../common.php";
require_once "../header.php";

if (!empty($_GET) && isset($_GET['account']) && !isset($_POST['uf_account']))
	$_POST['uf_account'] = $_GET['account'];

if (!empty($_POST)) {
	do_log($_POST);
	$bantype = $_POST['bantype'];
	if (isset($_POST['userch'])) {
		foreach ($_POST["userch"] as $user) {
			$user = $name = base64_decode($user);
			$bantype = (isset($_POST['bantype'])) ? $_POST['bantype'] : NULL;
			if (!$bantype) /* shouldn't happen? */{
				Message::Fail("An error occured");
			} else {
				$banlen_w = (isset($_POST['banlen_w'])) ? $_POST['banlen_w'] : NULL;
				$banlen_d = (isset($_POST['banlen_d'])) ? $_POST['banlen_d'] : NULL;
				$banlen_h = (isset($_POST['banlen_h'])) ? $_POST['banlen_h'] : NULL;

				$duration = "";
				if (!$banlen_d && !$banlen_h && !$banlen_w)
					$duration .= "0";
				else {
					if ($banlen_w)
						$duration .= $banlen_w;
					if ($banlen_d)
						$duration .= $banlen_d;
					if ($banlen_h)
						$duration .= $banlen_h;
				}
				$user = $rpc->user()->get($user);
				if (!$user && $bantype !== "qline") {
					Message::Fail("Could not find that user: User not online");
				} else {
					$msg_msg = ($duration == "0" || $duration == "0w0d0h") ? "permanently" : "for " . rpc_convert_duration_string($duration);
					$reason = (isset($_POST['ban_reason'])) ? $_POST['ban_reason'] : "No reason";
					if ($bantype == "qline")
						$rpc->nameban()->add($name, $reason, $duration);
					else if ($rpc->serverban()->add($user->id, $bantype, $duration, $reason))
						Message::Success($user->name . " (*@" . $user->hostname . ") has been $bantype" . "d $msg_msg: $reason");
					else
						Message::Fail("Could not add $bantype against $name: $rpc->error");
				}
			}
		}
	}
}

/* Get the user list */
$users = $rpc->user()->getAll();
?>
<h4>Users Overview</h4>

Click on a username to view more information.

<div id="Users">
	
	<?php
	if (isset($_POST['uf_nick']) && strlen($_POST['uf_nick']))
		Message::Info("Listing users which match nick: \"" . $_POST['uf_nick'] . "\"");

	if (isset($_POST['uf_ip']) && strlen($_POST['uf_ip']))
		Message::Info("Listing users which match IP: \"" . $_POST['uf_ip'] . "\"");

	if (isset($_POST['uf_host']) && strlen($_POST['uf_host']))
		Message::Info("Listing users which match hostmask: \"" . $_POST['uf_host'] . "\"");

	if (isset($_POST['uf_account']) && strlen($_POST['uf_account']))
		Message::Info("Listing users which match account: \"" . $_POST['uf_account'] . "\"");

	?>
	<table class="container-xxl table table-responsive caption-top table-striped">
	<thead>
		<th scope="col"><h5>Filter:</h5></th>
		<form action="" method="post">
		<th scope="col" colspan="2">Nick <input name="uf_nick" type="text" class="form-control short-form-control">
		<th scope="col" colspan="2">Host <input name="uf_host" type="text" class="form-control short-form-control"></th>
		<th scope="col" colspan="2">IP <input name="uf_ip" type="text" class="form-control short-form-control"></th>
		<th scope="col" colspan="2">Account <input name="uf_account" type="text" class="form-control short-form-control"></th>
		<th scope="col"> <input class="btn btn-primary" type="submit" value="Search"></th></form>
	</thead></table>

	<table class="container-xxl table table-sm table-responsive caption-top table-striped">
	<thead class="table-primary">
		<th scope="col"><input type="checkbox" label='selectall' onClick="toggle_user(this)" /></th>
		<th scope="col">Nick</th>
		<th scope="col">Host / IP</th>
		<th scope="col"><span data-toggle="tooltip" data-placement="bottom" title="The services account name, if the user identified to services." style="border-bottom: 1px dotted #000000">Account</span></th>
		<th scope="col">Usermodes <a href="https://www.unrealircd.org/docs/User_modes" target="_blank">ℹ️</a></th>
		<th scope="col">Oper</th>
		<th scope="col"><span data-toggle="tooltip" data-placement="bottom" title="This shows [Secure] if the user is using SSL/TLS or is on localhost." style="border-bottom: 1px dotted #000000">Secure</span></th>
		<th scope="col">Connected to</th>
		<th scope="col"><span data-toggle="tooltip" data-placement="bottom" title="The reputation score gets higher when someone with this IP address has been connected in the past weeks. A low reputation score (like <10) is an indication of a new IP." style="border-bottom: 1px dotted #000000">Reputation</span> <a href="https://www.unrealircd.org/docs/Reputation_score" target="_blank">ℹ️</a></th>
	</thead>
	
	<tbody>
	<form method="post">
	<?php

		foreach($users as $user)
		{

		
			/* Some basic filtering for NICK */
			if (isset($_POST['uf_nick']) && strlen($_POST['uf_nick']) && 
			strpos(strtolower($user->name), strtolower($_POST['uf_nick'])) !== 0 &&
			strpos(strtolower($user->name), strtolower($_POST['uf_nick'])) == false)
				continue;

			/* Some basic filtering for HOST */
			if (isset($_POST['uf_host']) && strlen($_POST['uf_host']) && 
			strpos(strtolower($user->hostname), strtolower($_POST['uf_host'])) !== 0 &&
			strpos(strtolower($user->hostname), strtolower($_POST['uf_host'])) == false)
				continue;

			/* Some basic filtering for IP */
			if (isset($_POST['uf_ip']) && strlen($_POST['uf_ip']) && 
			strpos(strtolower($user->ip), strtolower($_POST['uf_ip'])) !== 0 &&
			strpos(strtolower($user->ip), strtolower($_POST['uf_ip'])) == false)
				continue;

			/* Some basic filtering for ACCOUNT */
			if (isset($_POST['uf_account']) && strlen($_POST['uf_account']) && 
			strpos(strtolower($user->user->account), strtolower($_POST['uf_account'])) !== 0 &&
			strpos(strtolower($user->user->account), strtolower($_POST['uf_account'])) == false)
				continue;

			echo "\n<tr>";
			echo "<th scope=\"row\"><input type=\"checkbox\" value='" . base64_encode($user->id)."' name=\"userch[]\"></th>";
			$isBot = (strpos($user->user->modes, "B") !== false) ? ' <span class="badge rounded-pill badge-dark">Bot</span>' : "";
			echo "<td><a href=\"details.php?nick=".$user->id."\">$user->name$isBot</a></td>";
			echo "<td>".$user->hostname." (".$user->ip.")</td>";
			$account = (isset($user->user->account)) ? "<a href=\"".BASE_URL."users/?account=".$user->user->account."\">".$user->user->account."</a>" : '<span class="badge rounded-pill badge-primary">None</span>';
			echo "<td>".$account."</td>";
			$modes = (isset($user->user->modes)) ? "+" . $user->user->modes : "<none>";
			echo "<td>".$modes."</td>";
			$oper = (isset($user->user->operlogin)) ? $user->user->operlogin." <span class=\"badge rounded-pill badge-secondary\">".$user->user->operclass."</span>" : "";
			if (!strlen($oper))
				$oper = (strpos($user->user->modes, "S") !== false) ? '<span class="badge rounded-pill badge-warning">Services Bot</span>' : "";
			echo "<td>".$oper."</td>";

			$secure = (isset($user->tls)) ? "<span class=\"badge rounded-pill badge-success\">Secure</span>" : "<span class=\"badge rounded-pill badge-danger\">Insecure</span>";
			if (strpos($user->user->modes, "S") !== false)
				$secure = "";
			echo "<td>".$secure."</td>";
			echo "<td>".$user->user->servername."</td>";
			echo "<td>".$user->user->reputation."</td>";
			echo "</tr>";
		}
	?>
	</tbody></table>
	<table class="table table-responsive table-light">
	<tr>
	<td colspan="2">
	<label for="bantype">Apply action: </label>
	<select name="bantype" id="bantype">
			<option value=""></option>
		<optgroup label="Bans">
			<option value="gline">GLine</option>
			<option value="gzline">GZLine</option>
		</optgroup>
	</select></td><td colspan="2">
	<label for="banlen_w">Duration: </label>
	<select name="banlen_w" id="banlen_w">
			<?php
			for ($i = 0; $i <= 56; $i++)
			{
				if (!$i)
					echo "<option value=\"0w\"></option>";
				else
				{
					$w = ($i == 1) ? "week" : "weeks";
					echo "<option value=\"$i" . "w\">$i $w" . "</option>";
				}
			}
			?>
	</select>
	<select name="banlen_d" id="banlen_d">
			<?php
			for ($i = 0; $i <= 31; $i++)
			{
				if (!$i)
					echo "<option value=\"0d\"></option>";
				else
				{
					$d = ($i == 1) ? "day" : "days";
					echo "<option value=\"$i" . "d\">$i $d" . "</option>";
				}
			}
			?>
	</select>
	<select name="banlen_h" id="banlen_h">
			<?php
			for ($i = 0; $i <= 24; $i++)
			{
				if (!$i)
					echo "<option value=\"0d\"></option>";
				else
				{
					$h = ($i == 1) ? "hour" : "hours";
					echo "<option value=\"$i" . "h\">$i $h" . "</option>";
				}
			}
			
			?>
	</select><br></td><tr><td colspan="3">
	
	<label for="ban_reason">Reason: </label>
	<input class="form-control short-form-control" type="text" name="ban_reason" id="ban_reason" value="No reason">
	<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#myModal">
			Apply ban
	</button></td></table>
	<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="confirmModalCenterTitle" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
		<div class="modal-header">
			<h5 class="modal-title" id="myModalLabel">Apply ban</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			<span aria-hidden="true">&times;</span>
			</button>
		</div>
		<div class="modal-body">
			Are you sure you want to do this?
			
		</div>
		<div class="modal-footer">
			<button id="CloseButton" type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
			<button type="submit" action="post" class="btn btn-danger">Ban</button>
			
		</div>
		</div>
	</div>
	</div>
	
	</form>
	
		</div>

<script>
    
    $("#myModal").on('shown.bs.modal', function(){
        $("#CloseButton").focus();
    });
</script>

<?php require_once UPATH.'/footer.php'; ?>
