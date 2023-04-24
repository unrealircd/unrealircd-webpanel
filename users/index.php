<?php
require_once "../inc/common.php";
require_once "../inc/connection.php";
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

	<!-- The user list -->
	<table id="data_list" class="container-xxl table table-sm table-responsive caption-top table-striped">
	<thead class="table-primary">
		<th scope="col"><input type="checkbox" label='selectall' onClick="toggle_user(this)" /></th>
		<th scope="col">Nick</th>
		<th class="countrycol" scope="col">Country</th>
		<th class="hostname" scope="col">Host / IP</th>
		<th class="accountcol" scope="col"><span data-toggle="tooltip" data-placement="bottom" title="The services account name, if the user identified to services." style="border-bottom: 1px dotted #000000">Account</span></th>
		<th class="umodescol" scope="col">Usermodes <a href="https://www.unrealircd.org/docs/User_modes" target="_blank">ℹ️</a></th>
		<th class="opercol" scope="col">Oper</th>
		<th class="securecol" scope="col"><span data-toggle="tooltip" data-placement="bottom" title="This shows [Secure] if the user is using SSL/TLS or is on localhost." style="border-bottom: 1px dotted #000000">Secure</span></th>
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
	<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#myModal">
			Apply
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

<?php /* ?>
	<h3>Top country</h3>
	<div id="top-country">
		<ul>
		<?php
			arsort($registrationOfaAllFlags);
			foreach($registrationOfaAllFlags as $country_code => $count){
				echo '<li>
				<div class="drag"><img src="https://flagcdn.com/108x81/'.htmlspecialchars(strtolower($country_code)).'.png" width="108" height="81"><br />
				'.$country_code . '
				</div>
				<div class="count">' . $count . ' <span>connected</span></div>
				</li>';
			}
		?>
		</ul>
	</div>
<?php */ ?>

</div>

<script>
	function resize_check()
	{
		var width = window.innerWidth;
		var show_elements = '';
		var hide_elements = '';
		if (width < 500)
		{
			show_elements = '';
			hide_elements = '.hostname, .opercol, .uplinkcol, .securecol, .umodescol, .countrycol';
		} else
		if (width < 600)
		{
			show_elements = '.countrycol';
			hide_elements = '.hostname, .opercol, .uplinkcol, .securecol, .umodescol';
		} else
		if (width < 700)
		{
			show_elements = '.umodescol, .countrycol';
			hide_elements = '.hostname, .opercol, .uplinkcol, .securecol';
		} else
		if (width < 768)
		{
			show_elements = '.securecol, .umodescol, .countrycol';
			hide_elements = '.hostname, .opercol, .uplinkcol';
		} else
		if (width < 875)
		{
			// left nav kicks in at 768+ so need to drop one column between 768..875
			show_elements = '.umodescol, .countrycol';
			hide_elements = '.hostname, .opercol, .uplinkcol, .securecol';
		} else if (width < 1000)
		{
			show_elements = '.securecol, .umodescol, .countrycol';
			hide_elements = '.hostname, .uplinkcol, .opercol';
		} else if (width < 1200)
		{
			show_elements = '.opercol, .securecol, .umodescol, .countrycol';
			hide_elements = '.hostname, .uplinkcol';
		} else if (width < 1550)
		{
			show_elements = '.opercol, .uplinkcol, .securecol, .umodescol, .countrycol';
			hide_elements = '.hostname';
		} else if (width < 1750)
		{
			show_elements = '.hostname, .opercol, .securecol, .umodescol, .countrycol';
			hide_elements = '.uplinkcol';
		} else {
			show_elements = '.hostname, .opercol, .uplinkcol, .securecol, .umodescol, .countrycol';
			hide_elements = '';
		}

		if (show_elements != '')
		{
			show_elements=document.querySelectorAll(show_elements);
			for (let i = 0; i < show_elements.length; i++)
				show_elements[i].style.display = '';
		}

		if (hide_elements != '')
		{
			hide_elements=document.querySelectorAll(hide_elements);
			for (let i = 0; i < hide_elements.length; i++)
				hide_elements[i].style.display = 'none';
		}
	}
	resize_check();
	window.addEventListener('resize', function() {
		resize_check();
	});
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
	$('#data_list').DataTable({
		'ajax': {
			'url': '<?php echo get_config("base_url"); ?>api/users.php',
			dataSrc: ''
		},
		'pageLength':100,
		'order':[[1,'asc']],
		'dom': 'Pfrtip',
		'searchPanes': {
			'initCollapsed': 'true',
			'columns': [2,8],
			'dtOpts': {
				select: { style: 'multi'},
				order: [[ 1, "desc" ]]
			}
		},
		'columns': [
			{ 'data': 'Select' },
			{ 'data': 'Nick' },
			{ 'data': 'Country' },
			{ 'data': 'Host/IP' },
			{ 'data': 'Account' },
			{ 'data': 'Usermodes', 'name':'Usermodes', 'searchPanes': { 'name':'Usermodes' } },
			{ 'data': 'Oper' },
			{ 'data': 'Secure' },
			{ 'data': 'Connected to' },
			{ 'data': 'Reputation' },
		],
	});
} );

</script>

<?php require_once UPATH.'/inc/footer.php'; ?>
