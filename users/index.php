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
$can_ban = current_user_can(PERMISSION_BAN_USERS);
if (!empty($_POST) && $can_ban)
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
				Message::Fail(__('unrealircd_user_userch'));
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
					Message::Fail(__('unrealircd_user_banlen_h'));
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
						Message::Fail(sprintf(__("unrealircd_user_kill_failed"), $user->name, $rpc->error));
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
<h4><?php echo __('unrealircd_users_overview'); ?></h4>

<?php echo __('unrealircd_users_overview_notice'); ?>

<div class="usertable">
	<form method="post">

	<!-- The user list -->
	<table id="data_list" class="table-striped display responsive nowrap" style="width:100%">
	<thead class="table-primary">
		<th scope="col"><input type="checkbox" label='selectall' onClick="toggle_user(this)" /></th>
		<th scope="col"><?php echo __('unrealircd_users_overview_nick'); ?></th>
		<th class="countrycol" scope="col"><?php echo __('unrealircd_users_overview_country'); ?></th>
		<th class="hostname" scope="col"><?php echo __('unrealircd_users_overview_host_ip'); ?></th>
		<th class="accountcol" scope="col"><span data-toggle="tooltip" data-placement="bottom" title="<?php echo __('unrealircd_users_overview_account_title'); ?>" style="border-bottom: 1px dotted #000000"><?php echo __('unrealircd_users_overview_account'); ?></span></th>
		<th class="opercol" scope="col"><?php echo __('unrealircd_users_overview_oper'); ?></th>
		<th class="uplinkcol" scope="col"><?php echo __('unrealircd_users_overview_connected_to'); ?></th>
		<th class="reputationcol" scope="col"><span id="reputationheader" data-toggle="tooltip" data-placement="bottom" title="<?php echo __('unrealircd_users_overview_rep_title'); ?>" style="border-bottom: 1px dotted #000000"><?php echo __('unrealircd_users_overview_rep'); ?></span> <a href="https://www.unrealircd.org/docs/Reputation_score" target="_blank">ℹ️</a></th>
	</thead>
	</table>

	<!-- User Actions -->
	<table class="table table-responsive table-light">
	<tr>
	<td colspan="2" class="<?php echo $can_ban ? "" : "disabled"?>">
		<label for="bantype"><?php echo __('unrealircd_user_actions_apply'); ?></label>
		<select name="bantype" id="bantype">
				<option value=""></option>
			<optgroup label="Bans">
				<option value="gline">GLine</option>
				<option value="gzline">GZLine</option>
				<option value="kill">Kill</option>
			</optgroup>
		</select></td><td colspan="2">
		<label for="banlen_w"><?php echo __('unrealircd_user_actions_duration'); ?></label>
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
		
		<br>
	</td>
	<tr><td colspan="3">
	
	<label for="ban_reason"><?php echo __('unrealircd_user_actions_reason'); ?></label>
	<input class="form-control <?php echo $can_ban ? "" : "disabled"?>" type="text" name="ban_reason" id="ban_reason" value="No reason">
	<button type="button" class="btn btn-primary <?php echo $can_ban ? "" : "disabled"?>" data-toggle="modal" data-target="#ban_confirmation">
			<?php echo __('unrealircd_user_actions_reason_apply'); ?>
	</button></td></table>

	<!-- Ban confirmation modal -->
	<div class="modal fade" id="ban_confirmation" tabindex="-1" role="dialog" aria-labelledby="confirmModalCenterTitle" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
		<div class="modal-header">
			<h5 class="modal-title" id="ban_confirmation_label"><?php echo __('unrealircd_user_actions_apply_ban'); ?></h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			<span aria-hidden="true">&times;</span>
			</button>
		</div>
		<div class="modal-body">
			<?php echo __('unrealircd_user_actions_apply_ban_notice'); ?>
			
		</div>
		<div class="modal-footer">
			<button id="CloseButton" type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo __('unrealircd_user_actions_cancel'); ?></button>
			<button type="submit" action="post" class="btn btn-danger"><?php echo __('unrealircd_user_actions_apply'); ?></button>
			
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
		<div id="rclick_opt1" class="item list-group-item-action"><?php echo __('unrealircd_user_view_details'); ?></div>
		<div id="rclick_opt2" class="item list-group-item-action"><?php echo __('unrealircd_user_view_kill'); ?></div>
		<div id="rclick_opt3" class="item list-group-item-action"><?php echo __('unrealircd_user_view_copy'); ?>
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
