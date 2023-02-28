<?php
require_once "../common.php";
require_once "../header.php";
require_once "../misc/channel-lookup-misc.php";

$title = "Channel Lookup";
$channel = "";
$channame = "";
$nick = NULL;
$channelObj = NULL;
do_log($_GET);
do_log($_POST);
if (isset($_GET['chan']))
{
	$channel = $_GET['chan'];
	$channelObj = $rpc->channel()->get($channel);
	if (!$channelObj && strlen($channel))
	{
		Message::Fail("Could not find channel: \"$channel\"");
	} elseif (strlen($channel)) {

		$channame = $channelObj->name;
		$title .= " for \"" . $channame . "\"";
		do_log($channelObj);
	}
}
$topicset = false;
$del_ex = false;
$del_inv = false;
$del_ban = false;
$checkboxes = [];

$chanban_errors = [];
if (isset($_POST))
{
	if (isset($_POST['update_topic']) && isset($_POST['set_topic']))
	{
		if (isset($channelObj))
		{
			if (!isset($channelObj->topic) || strcmp($channelObj->topic,$_POST['set_topic'])) // if the set topic is different
			{
				$user = (function_exists('unreal_get_current_user') && $u = unreal_get_current_user()) ? $u->username : NULL;
				$topicset = $rpc->channel()->set_topic($channelObj->name, $_POST['set_topic'], $user);
				$channelObj->topic = $_POST['set_topic'];
			}
		}
	}
	$checkboxes = (isset($_POST['ban_checkboxes'])) ? $_POST['ban_checkboxes'] : [];
	if (isset($_POST['delete_sel_ex']))
	{
		foreach($_POST['ce_checkboxes'] as $c)
			$checkboxes[] = $c;
		$del_ex = true;
		chlkup_autoload_modal("excepts_modal");
	}
	else if (isset($_POST['delete_sel_inv']))
	{
		foreach($_POST['ci_checkboxes'] as $c)
			$checkboxes[] = $c;
		$del_inv = true;
		chlkup_autoload_modal("invites_modal");
	}
	else if (isset($_POST['delete_sel_ban']))
	{
		foreach($_POST['cb_checkboxes'] as $c)
			$checkboxes[] = $c;
		$del_ban = true;
		chlkup_autoload_modal("bans_modal");
	}
	if (isset($_POST['add_chban']) || isset($_POST['add_chinv']) || isset($_POST['add_chex']))
	{

		if (isset($_POST['add_chban']))
			$mode = $_POST['add_chban'];
		else
			$mode = (isset($_POST['add_chinv'])) ? $_POST['add_chinv'] : $_POST['add_chex'];
		
		$nick = (strlen($_POST['ban_nick'])) ? $_POST['ban_nick'] : false;
		$action_string = (isset($_POST['bantype_sel_action']) && strlen($_POST['bantype_sel_action'])) ? $_POST['bantype_sel_action'] : false;
		$action = "";
		$type_string = (strlen($_POST['bantype_sel_type'])) ? $_POST['bantype_sel_type'] : false;
		$type = "";
		$expiry = (strlen($_POST['bantype_sel_ex'])) ? $_POST['bantype_sel_ex'] : false;
		$time = "";
		
		if (!$nick)
			$chanban_errors[] = "You did not specify a nick/mask";

		if ($action_string)
		{
			if (strstr($action_string,"Quiet"))
				$action = "~quiet:";
			elseif (strstr($action_string,"Nick-change"))
				$action = "~nickchange:";
			elseif (strstr($action_string,"Join"))
				$action = "~join:";
		}
		if ($type_string)
		{
			if (strstr($type_string,"Account"))
				$type = "~account:";
			elseif (strstr($type_string,"Channel"))
				$type = "~channel:";
			elseif (strstr($type_string,"Country"))
				$type = "~country:";
			elseif (strstr($type_string,"OperClass"))
				$type = "~operclass:";
			elseif (strstr($type_string,"GECOS"))
				$type = "~realname:";
			elseif (strstr($type_string,"Security"))
				$type = "~security-group:";
			elseif (strstr($type_string,"Certificate"))
				$type = "~certfp:";
		}
		if ($expiry)
		{
			$future = strtotime($expiry);
			$now = strtotime(date("Y-m-d h:i:s"));
			$ts = ($future - $now) / 60;
			$ts = (int)$ts;
			$time = "~time:$ts:";
			if ($ts > 9999 || $ts < 1)
				$chanban_errors[] = "Cannot set expiry more than ".(9999 / 60)." hours (".(9999 / 1440)." days) in the future, or in the past";
		}
		if (empty($chanban_errors))
			if ($rpc->channel()->set_mode($channel, "$mode", "$time$action$type$nick"))
				Message::Success("The mode was set successfully: $mode $time$action$type$nick");

		else
			foreach($chanban_errors as $err)
				Message::Fail($err);
	}
	/* and finally re-grab the channel because updates lol */
	$channelObj = $rpc->channel()->get($channel);

}

?>
<title><?php echo $title; ?></title>
<h4><?php echo $title; ?></h4>
<br>


<div class="container-xl">
<form method="get" action="details.php">
	<div class="input-group">
		<input  class="form-control" id="chan" name="chan" type="text" value="<?php echo $channame; ?>">
		<div class="input-group-append">
			<button type="submit" class="btn btn-primary">Go</button>
		</div>
	</div>
</div>
</form>
<?php if (!$channelObj)
		return; ?>


<!-- Modal for Channel Bans -->
<div class="modal fade" id="bans_modal" name="bans_modal" tabindex="-1" role="dialog" aria-labelledby="confirmModalCenterTitle" aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-centered" role="document">
		<div class="modal-content">
		<div class="modal-header">
			<h5 class="modal-title" id="myModalLabel">Channel Bans</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			<span aria-hidden="true">&times;</span>
			</button>
		</div>
		<div class="modal-body">
			<?php if ($del_ban) do_delete_chanban($channelObj, $checkboxes); ?>
			<form method="post">
			<?php generate_chanbans_table($channelObj); ?>		
			</form>
		</div>
		</div>
	</div>
</div>
<!-- Modal for Channel Invited -->
<div class="modal fade" id="invites_modal" name="#invites_modal" tabindex="-1" role="dialog" aria-labelledby="confirmModalCenterTitle" aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-centered" role="document">
		<div class="modal-content">
		<div class="modal-header">
			<h5 class="modal-title" id="myModalLabel">Channel Invites</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			<span aria-hidden="true">&times;</span>
			</button>
		</div>
		<div class="modal-body">
			<?php if ($del_inv) do_delete_invite($channelObj, $checkboxes); ?>
			<form method="post">
			<?php generate_chaninvites_table($channelObj); ?>		
			</form>
		</div>
		</div>
	</div>
</div>

<!-- Modal for Channel Exceptions -->
<div class="modal fade" id="excepts_modal" tabindex="-1" role="dialog" aria-labelledby="confirmModalCenterTitle" aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-centered" role="document">
		<div class="modal-content">
		<div class="modal-header">
			<h5 class="modal-title" id="myModalLabel">Channel Exceptions</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			<span aria-hidden="true">&times;</span>
			</button>
		</div>
		<div class="modal-body">
			<?php if ($del_ex) do_delete_chanex($channelObj, $checkboxes); ?>
			<form method="post">
			<?php generate_chanexcepts_table($channelObj); ?>		
			</form>
		</div>
		</div>
	</div>
</div>


<!-- Modal for Add Ban -->
<div class="modal fade" id="ban" tabindex="-1" role="dialog" aria-labelledby="confirmModalCenterTitle" aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-centered" role="document">
		<div class="modal-content">
		<div class="modal-header">
			<h5 class="modal-title" id="myModalLabel">Add New Channel Ban</h5>
			<div type="button" class="close" data-dismiss="modal" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		</div>
		</div>
		<div class="modal-body">
			<form method="post">
			<div class="input-group mb-3">
				<label for="ban_nick">Mask
					<a href="https://www.unrealircd.org/docs/Extended_bans" target="__blank"><i class="fa fa-info-circle" aria-hidden="true"
					title="The mask or other value. For example if you are matching a country in 'Ban Type' then you would put the country code as this value. Click to view more information on Extended Bans"
					></i></a>
					<input style="width: 170%;" name="ban_nick" id="ban_nick" class="form-control curvy" type="text"
							placeholder="nick!user@host or something else"
					></label>
					
			</div>
			<div class="input-group mb-3">
				<label for="bantype_action">Ban Action
					<select class="form-control" name="bantype_sel_action" id="bantype_sel">
						<option></option>
						<option>Quiet (Mute)</option>
						<option>Nick-change</option>
						<option>Join</option>
					</select>
				</label>
			</div>
			<div class="input-group mb-3">
				<label for="bantype_sel_type">Ban Type
					<select class="form-control" name="bantype_sel_type" id="bantype_sel_type">
						<option></option>
						<option>Match Account</option>
						<option>Match Channel</option>
						<option>Match Country</option>
						<option>Match OperClass</option>
						<option>Match RealName / GECOS</option>
						<option>Match Security Group</option>
						<option>Match Certificate Fingerprint</option>
					</select>
				</label>
			</div>
			<div class="input-group mb-3">
			<label for="bantype_sel_ex">Expiry Date-Time <br><small>Leave blank to mean "Permanent"</small>
					<input type="datetime-local" name="bantype_sel_ex" id="bantype_sel_ex" class="form-control">
				</label>
			</div>
		</div>
		<div class="modal-footer">
			<input type="hidden" id="server" name="add_chban" value="b"></input>
			<button id="CloseButton" type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
			<button type="submit" action="post" class="btn btn-danger">Add Channel Ban</button>
			</form>
		</div>
		</div>
	</div>
</div>


<!-- Modal for Add Invite -->
<div class="modal fade" id="invite" tabindex="-1" role="dialog" aria-labelledby="add_invite_modal" aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-centered" role="document">
		<div class="modal-content">
		<div class="modal-header">
			<h5 class="modal-title" id="add_invite_modal">Add New Channel Invite</h5>
			<div type="button" class="close" data-dismiss="modal" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		</div>
		</div>
		<div class="modal-body">
			<form method="post">
			<div class="input-group mb-3">
				<label for="ban_nick">Mask
					<a href="https://www.unrealircd.org/docs/Extended_bans" target="__blank"><i class="fa fa-info-circle" aria-hidden="true"
					title="The mask or other value. For example if you are matching a country in 'Invite Type' then you would put the country code as this value. Click to view more information on Extended Bans"
					></i></a>
					<input style="width: 170%;" name="ban_nick" id="ban_nick" class="form-control curvy" type="text"
							placeholder="nick!user@host or something else"
					></label>
					
			</div>
			<div class="input-group mb-3">
				<label for="bantype_sel_type">Invite Type
					<select class="form-control" name="bantype_sel_type" id="bantype_sel_type">
						<option></option>
						<option>Match Account</option>
						<option>Match Channel</option>
						<option>Match Country</option>
						<option>Match OperClass</option>
						<option>Match RealName / GECOS</option>
						<option>Match Security Group</option>
						<option>Match Certificate Fingerprint</option>
					</select>
				</label>
			</div>
			<div class="input-group mb-3">
			<label for="bantype_sel_ex">Expiry Date-Time <br><small>Leave blank to mean "Permanent"</small>
					<input type="datetime-local" name="bantype_sel_ex" id="bantype_sel_ex" class="form-control">
				</label>
			</div>
		</div>
		<div class="modal-footer">
			<input type="hidden" name="add_chinv" value="I"></input>
			<button id="CloseButton" type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
			<button type="submit" action="post" class="btn btn-danger">Add Invite</button>
			</form>
		</div>
		</div>
	</div>
</div>

<!-- Modal for Add Ban Exceptions -->
<div class="modal fade" id="except" tabindex="-1" role="dialog" aria-labelledby="add_except_modal" aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-centered" role="document">
		<div class="modal-content">
		<div class="modal-header">
			<h5 class="modal-title" id="add_except_modal">Add New Channel Ban Exception</h5>
			<div type="button" class="close" data-dismiss="modal" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		</div>
		</div>
		<div class="modal-body">
			<form method="post">
			<div class="input-group mb-3">
				<label for="ban_nick">Mask
					<a href="https://www.unrealircd.org/docs/Extended_bans" target="__blank"><i class="fa fa-info-circle" aria-hidden="true"
					title="The mask or other value. For example if you are matching a country in 'Ban Type' then you would put the country code as this value. Click to view more information on Extended Bans"
					></i></a>
					<input style="width: 170%;" name="ban_nick" id="ban_nick" class="form-control curvy" type="text"
							placeholder="nick!user@host or something else"
					></label>
					
			</div>
			<div class="input-group mb-3">
				<label for="bantype_sel_type">Ban Type
					<select class="form-control" name="bantype_sel_type" id="bantype_sel_type">
						<option></option>
						<option>Match Account</option>
						<option>Match Channel</option>
						<option>Match Country</option>
						<option>Match OperClass</option>
						<option>Match RealName / GECOS</option>
						<option>Match Security Group</option>
						<option>Match Certificate Fingerprint</option>
					</select>
				</label>
			</div>
			<div class="input-group mb-3">
			<label for="bantype_sel_ex">Expiry Date-Time <br><small>Leave blank to mean "Permanent"</small>
					<input type="datetime-local" name="bantype_sel_ex" id="bantype_sel_ex" class="form-control">
				</label>
			</div>
		</div>
		<div class="modal-footer">
			<input type="hidden" id="server" name="add_chex" value="e"></input>
			<button id="CloseButton" type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
			<button type="submit" action="post" class="btn btn-danger">Add Exception</button>
			</form>
		</div>
		</div>
	</div>
</div>

<br>
<h3>
	Topic:<br></h3>
	<form method="post" action="details.php?chan=<?php echo urlencode($channelObj->name); ?>">
	<div class="input-group">
	<input maxlength="360" type="text" class="input-group form-control" name="set_topic" value="<?php echo (isset($channelObj->topic)) ? htmlspecialchars($channelObj->topic) : ""; ?>">
	<div class="input-group-append"><button type="submit" name="update_topic" value="true" class="btn btn-info">Update Topic</button></div></div>
	</form>
<?php 
if ($topicset)
	Message::Success("The topic for $channelObj->name has been updated to be: \"".htmlspecialchars($channelObj->topic)."\"");
?>
<br>
<div class="row" style="margin-left: 0px">
	<div class="p-1">
		<button class="btn btn-danger dropdown-toggle" data-toggle="dropdown" aria-expanded="false">Bans</button>
		<div class="dropdown-menu">
			<div class="dropdown-item" data-toggle="modal" data-target="#ban">Add New</div>
			<div class="dropdown-item" data-toggle="modal" data-target="#bans_modal">List</div>
		</div>
	</div>
	<div class="p-1">
		<button class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-expanded="false">Invites</button>
		<div class="dropdown-menu">
			<div class="dropdown-item" data-toggle="modal" data-target="#invite">Add New</div>
			<div class="dropdown-item" data-toggle="modal" data-target="#invites_modal">List</div>
		</div>
	</div>
	<div class="p-1">	
	<button class="btn btn-warning dropdown-toggle" data-toggle="dropdown" aria-expanded="false">Excepts</button>
		<div class="dropdown-menu">
			<div class="dropdown-item" data-toggle="modal" data-target="#except">Add New</div>
			<div class="dropdown-item" data-toggle="modal" data-target="#excepts_modal">List</div>
		</div>
	</div>
</div>
<br>


<div class="container-xxl">
	<div class="accordion" id="accordionExample">
		<div class="card">
			<div class="card-header" id="headingOne">
			<h2 class="mb-0">
				<button class="btn" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
				User List
				</button>
			</h2>
			</div>

			<div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordionExample">
			<div class="card-body">
				<?php generate_chan_occupants_table($channelObj); ?>
			</div>
			</div>
		</div>
		<div class="card">
			<div class="card-header" id="headingTwo">
			<h2 class="mb-0">
				<button class="btn collapsed" type="button" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
				Collapsible Group Item #2
				</button>
			</h2>
			</div>
			<div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionExample">
			<div class="card-body">
				Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS.
			</div>
			</div>
		</div>
		<div class="card">
			<div class="card-header" id="headingThree">
			<h2 class="mb-0">
				<button class="btn collapsed" type="button" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
				Collapsible Group Item #3
				</button>
			</h2>
			</div>
			<div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#accordionExample">
			<div class="card-body">
				Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS.
			</div>
			</div>
		</div>
	</div>
</div>

<?php 
require_once("../footer.php");

