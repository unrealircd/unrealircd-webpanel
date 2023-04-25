<?php
require_once "../inc/common.php";
require_once "../inc/header.php";

if (!empty($_GET))
{
	if (isset($_GET['account']) && !isset($_POST['uf_account']))
		$_POST['uf_account'] = $_GET['account'];

	if (isset($_GET['operonly']) && !isset($_POST['operonly']))
		$_POST['operonly'] = $_GET['operonly'];

	if (isset($_GET['servicesonly']) && !isset($_POST['servicesonly']))
		$_POST['servicesonly'] = $_GET['servicesonly'];
}

if (!empty($_POST))
{
	require_once "../inc/connection.php";
	do_log($_POST);
	$bantype = (isset($_POST['bantype'])) ? $_POST['bantype'] : NULL;

	if (isset($_POST['userch'])) {
		foreach ($_POST["userch"] as $user)
		{
			$user = $name = base64_decode($user);

			if (!$bantype) /* shouldn't happen? */
			{
				Message::Fail("An error occured");
			}
			
			else
			{
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
				}
				
				else
				{
					$msg_msg = ($duration == "0" || $duration == "0w0d0h") ? "permanently" : "for " . rpc_convert_duration_string($duration);
					$reason = (isset($_POST['ban_reason'])) ? $_POST['ban_reason'] : "No reason";

					if ($bantype == "qline")
						$rpc->nameban()->add($name, $reason, $duration);

					else if ($bantype == "kill")
					{
						if ($rpc->user()->kill($user->id, $reason))
							Message::Success($user->name . "(*@" . $user->hostname . ") has been killed: $reason");
						else
							Message::Fail("Could not kill $user->name: $rpc->error");
					}
					else if ($rpc->serverban()->add($user->id, $bantype, $duration, $reason))
						Message::Success($user->name . " (*@" . $user->hostname . ") has been $bantype" . "d $msg_msg: $reason");

					else
						Message::Fail("Could not add $bantype against $name: $rpc->error");
				}
			}
		}
	}
}

?>
<h4>Users Overview</h4>

Click on a username to view more information.

<div class="usertable">
	
	<?php

	if (isset($_POST['uf_nick']) && strlen($_POST['uf_nick']))
		Message::Info("Listing users which match nick: \"" . $_POST['uf_nick'] . "\"");

	if (isset($_POST['uf_ip']) && strlen($_POST['uf_ip']))
		Message::Info("Listing users which match IP: \"" . $_POST['uf_ip'] . "\"");

	if (isset($_POST['uf_host']) && strlen($_POST['uf_host']))
		Message::Info("Listing users which match hostmask: \"" . $_POST['uf_host'] . "\"");

	if (isset($_POST['uf_account']) && strlen($_POST['uf_account']))
		Message::Info("Listing users which match account: \"" . $_POST['uf_account'] . "\"");

	if (isset($_POST['uf_server']) && strlen($_POST['uf_server']))
		Message::Info("Listing users connected to servers matching: \"" . $_POST['uf_server'] . "\"");


	?>

	<form method="post">

	<!-- The user list -->
	<table id="data_list" class="table-striped display responsive nowrap" style="width:100%">
	<thead>
		<th scope="col"><input type="checkbox" label='selectall' onClick="toggle_user(this)" /></th>
		<th scope="col">Nick</th>
		<th class="countrycol" scope="col">Country</th>
		<th class="hostname" scope="col">Host / IP</th>
		<th class="accountcol" scope="col"><span data-toggle="tooltip" data-placement="bottom" title="The services account name, if the user identified to services." style="border-bottom: 1px dotted #000000">Account</span></th>
		<th class="opercol" scope="col">Oper</th>
		<th class="uplinkcol" scope="col">Connected to</th>
		<th class="reputationcol" scope="col"><span id="reputationheader" data-toggle="tooltip" data-placement="bottom" title="The reputation score gets higher when someone with this IP address has been connected in the past weeks. A low reputation score (like <10) is an indication of a new IP." style="border-bottom: 1px dotted #000000">Rep.</span> <a href="https://www.unrealircd.org/docs/Reputation_score" target="_blank">ℹ️</a></th>
	</thead>
	</table>

	<!-- User Actions -->
	<table class="table table-responsive table-light">
	<tr>
	<td colspan="2">
	<label for="bantype">Apply action: </label>
	<select name="bantype" id="bantype">
			<option value=""></option>
		<optgroup label="Bans">
			<option value="gline">GLine</option>
			<option value="gzline">GZLine</option>
			<option value="kill">Kill</option>
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
	</select>
	
	<br></td><tr><td colspan="3">
	
	<label for="ban_reason">Reason: </label>
	<input class="form-control" type="text" name="ban_reason" id="ban_reason" value="No reason">
	<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#ban_confirmation">
			Apply
	</button></td></table>

	<!-- Ban confirmation modal -->
	<div class="modal fade" id="ban_confirmation" tabindex="-1" role="dialog" aria-labelledby="confirmModalCenterTitle" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
		<div class="modal-header">
			<h5 class="modal-title" id="ban_confirmation_label">Apply ban</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			<span aria-hidden="true">&times;</span>
			</button>
		</div>
		<div class="modal-body">
			Are you sure you want to do this?
			
		</div>
		<div class="modal-footer">
			<button id="CloseButton" type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
			<button type="submit" action="post" class="btn btn-danger">Apply</button>
			
		</div>
		</div>
	</div>
	</div>
	
	</form>

	<style>
		#rclickmenu {
			position: fixed;
			z-index: 10000;
			width: 250px;
			background: #1b1a1a;
			border-radius: 5px;
			transform: scale(0);
			transform-origin: top left;
		}
		#rclickmenu.visible {
			transform: scale(1);
			transition: transform 120ms ease-in-out;
		}
		#rclickmenu .item {
			padding: 8px 10px;
			font-size: 15px;
			color: #eee;
			cursor: pointer;
			border-radius: inherit;
		}
		#rclickmenu .item:hover {
			background: #343434;
			text-decoration: none;
		}
	</style>

	<div id='rclickmenu' class="nav-item list-group">
		<div id="rclick_opt1" class="item list-group-item-action">View details</div>
		<div id="rclick_opt2" class="item list-group-item-action">Kill</div>
		<div id="rclick_opt3" class="item list-group-item-action">Copy
	</div>
</div>

<script>
	var rclickmenu = document.getElementById('rclickmenu');
	var scopes = document.querySelectorAll('.userselector');
	document.addEventListener("click", (e) =>
	{
		if (e.target.offsetParent != rclickmenu)
		{
			rclickmenu.classList.remove("visible");
		}
	});
	scopes.forEach((scope) => {
		scope.addEventListener("contextmenu", (event) =>
		{
			event.preventDefault();
			var { clientX: mouseX, clientY: mouseY } = event;
			var name = $('#' + scope.id).attr('value')
			document.getElementById("rclick_opt1").innerHTML = 'View details for ' + name;
			rclickmenu.style.top = `${mouseY}px`;
			rclickmenu.style.left = `${mouseX}px`;
			rclickmenu.classList.remove("visible");
			setTimeout(() => { rclickmenu.classList.add("visible"); });
		});
	});
	document.addEventListener('keydown', (event) => {
	if (event.key === 'Escape')
	{
		rclickmenu.classList.remove("visible");
	}
});

$(document).ready( function () {
	args = {
		'responsive': true,
		'fixedHeader': {
			header: true,
			headerOffset: 53
		},
		'ajax': {
			'url': '<?php echo get_config("base_url"); ?>api/users.php',
			dataSrc: ''
		},
		'pageLength':100,
		'order':[[1,'asc']],
		'columns': [
			{ 'data': 'Select', 'responsivePriority': 1 },
			{ 'data': 'Nick', 'responsivePriority': 1 },
			{ 'data': 'Country', 'className':'countrycol', 'responsivePriority': 2 },
			{ 'data': 'Host/IP', 'className':'hostname', 'responsivePriority': 5 },
			{ 'data': 'Account', 'className':'accountcol', 'responsivePriority': 3 },
			{ 'data': 'Oper', 'className':'opercol', 'responsivePriority': 8 },
			{ 'data': 'Connected to', 'className':'uplinkcol', 'responsivePriority': 6 },
			{ 'data': 'Reputation', 'className':'reputationcol', 'responsivePriority': 4 },
		],
	};
	/* Hide on mobile */
	if (window.innerWidth > 800)
	{
		args['dom'] = 'Pfrtip';
		args['searchPanes'] = {
			'initCollapsed': 'true',
			'columns': [2,6],
			'dtOpts': {
				select: { style: 'multi'},
				order: [[ 1, "desc" ]]
			},
		}
	}

	$('#data_list').DataTable(args);
} );

</script>

<?php require_once UPATH.'/inc/footer.php'; ?>
