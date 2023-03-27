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
		do_delete_chanex($channelObj, $checkboxes);
	}
	if (isset($_POST['delete_sel_inv']))
	{
		foreach($_POST['ci_checkboxes'] as $c)
			$checkboxes[] = $c;
		do_delete_invite($channelObj, $checkboxes);
	}
	else if (isset($_POST['delete_sel_ban']))
	{
		foreach($_POST['cb_checkboxes'] as $c)
			$checkboxes[] = $c;
		do_delete_chanban($channelObj, $checkboxes);
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
	if ((isset($_POST['kickban']) || isset($_POST['muteban'])) && current_user_can(PERMISSION_EDIT_CHANNEL_USER))
	{
		$mute = (isset($_POST['muteban'])) ? true : false;
		$mutestr = ($mute) ? "~quiet:" : "";
		$user = (!$mute) ? $_POST['kickban'] : $_POST['muteban'];

		$kbuser = $rpc->user()->get($user);

		if (!$kbuser)
			Message::Fail("Could not kickban user: User does not exist");
		else
		{
			$rpc->channel()->set_mode($channelObj->name, "+b", "~time:".DEFAULT_CHAN_DETAIL_QUICK_ACTION_TIME.":".$mutestr."*!*@".$kbuser->user->cloakedhost);
			if (!$mute)
				$rpc->channel()->kick($channelObj->name, $kbuser->name, DEFAULT_CHAN_DETAIL_QUICK_BAN_REASON);

			$msgbox_str = ($mute)
			?
				"User \"$kbuser->name\" has been muted for ".DEFAULT_CHAN_DETAIL_QUICK_ACTION_TIME." minutes."
			:
				"User \"$kbuser->name\" has been banned for ".DEFAULT_CHAN_DETAIL_QUICK_ACTION_TIME." minutes."
			;
			Message::Success($msgbox_str);
		}
	}
	/* and finally re-grab the channel because updates lol */
	$channelObj = $rpc->channel()->get($channel);

}

?>
<title><?php echo $title; ?></title>
<h6><?php echo $title; ?></h6>
<br>


<div class="container-xl">
<form method="get" action="details.php">
	<div class="text-left input-group">
		<input class="form-control" id="chan" name="chan" type="text" value="<?php echo $channame; ?>">
		<div class="input-group-append">
			<button type="submit" class="btn btn-primary">Go</button>
		</div>
	</div>
</div>
</form>
<?php if (!$channelObj)
		return; ?>

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
<h6>
	Topic:<br></h6>
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

<!-- Modal for User Action -->
<div class="modal fade" id="useraction" tabindex="-1" role="dialog" aria-labelledby="confirmModalCenterTitle" aria-hidden="true">
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

<div class="container-xxl">
  <div class="row">
    <div class="col-sm-4">
      <div class="card">
        <div class="card-body">
          <h6 class="card-title">User List</h6>
          <p class="card-text"><?php generate_chan_occupants_table($channelObj); ?></p>
        </div>
      </div>
    </div>
    <div class="col-sm-8">
      <div class="card">
        <div class="card-body">
        	<h6 class="card-title">Channel Settings</h6>
			<ul class="nav nav-tabs" role="tablist">
				<li class="nav-item" role="presentation"><a class="nav-link" href="#chanmodes" aria-controls="chanmodes" role="tab" data-toggle="tab">Settings / Modes</a></li>
				<li class="nav-item" role="presentation"><a class="nav-link" href="#chanbans" aria-controls="chanbans" role="tab" data-toggle="tab">Bans</a></li>
				<li class="nav-item" role="presentation"><a class="nav-link" href="#chaninv" aria-controls="chaninv" role="tab" data-toggle="tab">Invites</a></li>
				<li class="nav-item" role="presentation"><a class="nav-link" href="#chanex" aria-controls="chanex" role="tab" data-toggle="tab">Excepts</a></li>
			</ul>
		
		<div class="tab-content">
		
		<div role="tabpanel" class="tab-pane fade in" id="chanmodes">
			<p class="card-text"><?php generate_html_chansettings($channelObj); ?></p>
		</div>
		
		<div role="tabpanel" class="tab-pane fade in" id="chanbans">
			<p class="card-text"><?php generate_chanbans_table($channelObj); ?></p>
		</div>
		<div role="tabpanel" class="tab-pane fade in" id="chaninv">
			<p class="card-text"><?php generate_chaninvites_table($channelObj); ?></p>
		</div>
		<div role="tabpanel" class="tab-pane fade in" id="chanex">
			<p class="card-text"><?php generate_chanexcepts_table($channelObj); ?></p>
		</div>
		
		</div>
        </div>
      </div>
    </div>
</div>
<script>
    // show dat first tab
$('.nav-tabs a[href="#chanmodes"]').tab('show')
</script>
<?php 
require_once("../footer.php");

