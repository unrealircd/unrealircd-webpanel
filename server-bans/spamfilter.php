<?php
require_once "../inc/common.php";
require_once "../inc/header.php";
require_once "../inc/connection.php";

$spamfilter_target_info = Array(
	"p"=>Array("short_text" => "usermsg", "long_text" => "User message"),
	"n"=>Array("short_text" => "usernotice", "long_text" => "User notice"),
	"c"=>Array("short_text" => "chanmsg", "long_text" => "Channel message"),
	"N"=>Array("short_text" => "channotice", "long_text" => "Channel notice"),
	"P"=>Array("short_text" => "part", "long_text" => "Part message"),
	"q"=>Array("short_text" => "quit", "long_text" => "Quit message"),
	"d"=>Array("short_text" => "dcc", "long_text" => "DCC Filename"),
	"a"=>Array("short_text" => "away", "long_text" => "Away message"),
	"t"=>Array("short_text" => "topic", "long_text" => "Channel topic"),
	"T"=>Array("short_text" => "message-tag", "long_text" => "Message tag"),
	"u"=>Array("short_text" => "usermask", "long_text" => "User mask (nick!user@host:realname)"),
);

function spamfilter_targets_to_string($targets)
{
	global $spamfilter_target_info;

	$ret = '';
	for ($i = 0, $targs = ""; $i < strlen($targets); $i++)
	{
		$c = $targets[$i];
		if (isset($spamfilter_target_info[$c]))
			$ret .= $spamfilter_target_info[$c]["short_text"].", ";
		else
			$ret .= "??, ";
	}
	$ret = rtrim($ret,", ");
	return $ret;
}

function spamfilter_targets_to_string_with_info($targets)
{
	global $spamfilter_target_info;

	$ret = '';
	for ($i = 0, $targs = ""; $i < strlen($targets); $i++)
	{
		$c = $targets[$i];
		if (isset($spamfilter_target_info[$c]))
			$ret .= "<span data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"".$spamfilter_target_info[$c]["long_text"]."\" style=\"border-bottom: 1px dotted #000000\">".$spamfilter_target_info[$c]["short_text"]."</span>, ";
		else
			$ret .= "??, ";
	}
	$ret = rtrim($ret,", ");
	return $ret;
}

function spamfilter_target_name_to_char($name)
{
	global $spamfilter_target_info;

	foreach ($spamfilter_target_info as $char=>$e)
	{
		if ($e["short_text"] == $name)
			return $char;
	}
	return false;
}

function spamfilter_targets_from_array_to_chars($ar)
{
	$ret = '';
	foreach ($ar as $name)
	{
		$c = spamfilter_target_name_to_char($name);
		if ($c !== false)
			$ret .= $c;
	}
	return $ret;
}

if (!empty($_POST))
{

	do_log($_POST);

	if (($sf = (isset($_POST['sf_add'])) ? $_POST['sf_add'] : false)) // if it was a spamfilter entry
	{
		if (!current_user_can(PERMISSION_SPAMFILTER_ADD))
			Message::Fail("Could not add Spamfilter entry: Permission denied");
		else
		{
			
			/* get targets */
			$targets = []; // empty arrae
			foreach($_POST as $key => $value)
			{
				if (substr($key, 0, 7) == "target_")
					$targets[] = str_replace(["target_", "_"], ["", "-"], $key);
			}
			if (empty($targets))
				Message::Fail("No target was specified");

			if (!isset($_POST['sf_bantype']))
				Message::Fail("No action was chosen");

			else
			{

				$bantype = $_POST['sf_bantype'];
				$targ_chars = spamfilter_targets_from_array_to_chars($targets);
				/* duplicate code for now [= */
				$banlen_w = (isset($_POST['banlen_w'])) ? $_POST['banlen_w'] : NULL;
				$banlen_d = (isset($_POST['banlen_d'])) ? $_POST['banlen_d'] : NULL;
				$banlen_h = (isset($_POST['banlen_h'])) ? $_POST['banlen_h'] : NULL;
				$duration = "";
				if (!$banlen_d && !$banlen_h && !$banlen_w)
					$duration .= "0";
				
				else
				{
					if ($banlen_w)
						$duration .= $banlen_w;
					if ($banlen_d)
						$duration .= $banlen_d;
					if ($banlen_h)
						$duration .= $banlen_h;
				}
				$match_type = $_POST['matchtype']; // should default to 'simple'
					$reason = isset($_POST['ban_reason']) ? $_POST['ban_reason'] : "No reason";
					$soft = (isset($_POST['soft'])) ? true : false;
					if ($soft)
						$bantype = "soft-$bantype";
					if ($rpc->spamfilter()->add($sf, $match_type, $targ_chars, $bantype, $duration, $reason))
						Message::Success("Added spamfilter entry \"$sf\" [match type: $match_type] [targets: $targ_chars] [reason: $reason]");
					else
						Message::Fail("Could not add spamfilter entry \"$sf\" [match type: $match_type] [targets: $targ_chars] [reason: $reason]: $rpc->error");
			}
		}
	}
	else if (!empty($_POST['sf']))
	{
		if (!current_user_can(PERMISSION_SPAMFILTER_DEL))
			Message::Fail("Could not delete Spamfilter entry or entries: Permission denied");
		else
			foreach ($_POST['sf'] as $key => $value)
			{
				$tok = explode(",", $value);
				$name = base64_decode($tok[0]);
				$match_type = base64_decode($tok[1]);
				$spamfilter_targets = base64_decode($tok[2]);
				$ban_action = base64_decode($tok[3]);
				if ($rpc->spamfilter()->delete($name, $match_type, $spamfilter_targets, $ban_action))
					Message::Success("Spamfilter on $name has been removed");
				else
					Message::Fail("Unable to remove spamfilter on $name: $rpc->error");
			}
	}
	
}

$spamfilter = $rpc->spamfilter()->getAll();
?>

<h4>Spamfilter Overview</h4><br>
<p><button type="button" class="btn btn-primary" data-toggle="modal" data-target="#myModal" <?php echo (current_user_can(PERMISSION_SPAMFILTER_ADD)) ? "" : "disabled"; ?>>
			Add entry
	</button></p>
	<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="confirmModalCenterTitle" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
		<div class="modal-header">
			<h5 class="modal-title" id="myModalLabel">Add new Spamfilter Entry</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			<span aria-hidden="true">&times;</span>
			</button>
		</div>
		<div class="modal-body">
		
		<form action="spamfilter.php" method="post">
			<div class="align_label curvy">Match&nbsp;type: </div> <select name="matchtype" id="matchtype">
				<option value="simple">Simple</option>
				<option value="regex">Regular Expression</option>
			</select><br>
			<div class="align_label curvy">Entry: </div> <input class="curvy" type="text" id="sf_add" name="sf_add"><br>
			
			<div class="align_label curvy"><label for="banlen_w">Targets: </label></div>
<?php
			$first = true;
			foreach ($spamfilter_target_info as $letter=>$e)
			{
				$shortname = $e['short_text'];
				$longname = $e['long_text'];
				if (!$first)
					echo "<div class=\"align_label curvy\"><label></label></div>";
				$first = false;
				echo "<input type=\"checkbox\" class=\"curvy\" id=\"target_$shortname\" name=\"target_$shortname\">$longname<br>\n";
			}
?>
			<div class="align_label curvy">Action: </div> <select name="sf_bantype" id="sf_bantype">
				<option value=""></option>
				<optgroup label="Bans">
					<option value="kline">Kill Line (KLine)</option>
					<option value="gline">Global Kill Line (GLine)</option>
					<option value="zline">Zap Line (ZLine)</option>
					<option value="gzline">Global Zap Line (GZLine)</option>
					
				</optgroup>
				<optgroup label="Restrictions">
					<option value="tempshun">Temporary Shun (Session only)</option>
					<option value="shun">Shun</option>
					<option value="block">Block</option>
					<option value="dccblock">DCC Block</option>
					<option value="viruschan">Send to "Virus Chan"</option>
				</optgroup>
				<optgroup label="Other">
					<option value="warn">Warn the user</option>
				</optgroup>
			</select><br>
			<div class="align_label curvy"><label for="banlen_w">Duration: </label></div>
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
			</select><br>
			<input class="curvy" type="checkbox" id="soft" name="soft">Don't affect logged-in users (soft)
			<br><div class="align_label curvy"><label for="ban_reason">Reason: </label></div>
			<input class="curvy" type="text" id="ban_reason" name="ban_reason"><br>
				</div>
			
		<div class="modal-footer">
			<button id="CloseButton" type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
			<button type="submit" action="post" class="btn btn-danger">Add Spamfilter Entry</button>
			</form>
		</div></div></div></div>

	
	<table class="container-xxl table-sm table-responsive caption-top table-striped">
	<thead class="table-primary"><form action="spamfilter.php" method="post">
	<th><input type="checkbox" label='selectall' onClick="toggle_sf(this)" /></th>
	<th>Match Type</th>
	<th>Mask</th>
	<th>Target</th>
	<th>Action</th>
	<th>Action Duration</th>
	<th>Reason</th>
	<th>Set By</th>
	<th>Set On</th>
				</thead>
	
	<?php
		foreach($spamfilter as $sf)
		{
			echo "<tr>";
			echo "<td><input type=\"checkbox\" value='" . base64_encode($sf->name).",".base64_encode($sf->match_type).",".base64_encode($sf->spamfilter_targets).",".base64_encode($sf->ban_action) . "' name=\"sf[]\"></td>";
			echo "<td>".$sf->match_type."</td>";
			echo "<td data-toggle='tooltip' data-placement='bottom' title='$sf->name'>".(strlen($sf->name) > 50 ? substr($sf->name, 0, 50)."..." : $sf->name)."</td>";
			echo "<td>".spamfilter_targets_to_string_with_info($sf->spamfilter_targets)."</td>";
			echo "<td><span class=\"badge rounded-pill badge-info\">".$sf->ban_action."</span></td>";
			echo "<td>".$sf->ban_duration_string."</td>";
			echo "<td>".$sf->reason."</td>";
			echo "<td>".show_nick_only($sf->set_by)."</td>";
			echo "<td>".$sf->set_at_string."</td>";
			
		}
	?></table><p><button type="button" class="btn btn-danger" data-toggle="modal" data-target="#myModal2" <?php echo (current_user_can(PERMISSION_SPAMFILTER_DEL)) ? "" : "disabled"; ?>>
	Delete selected
	</button></p>
	<div class="modal fade" id="myModal2" tabindex="-1" role="dialog" aria-labelledby="confirmModalCenterTitle" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
		<div class="modal-header">
			<h5 class="modal-title" id="myModalLabel">Confirm deletion</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			<span aria-hidden="true">&times;</span>
			</button>
		</div>
		<div class="modal-body">
			Are you sure you want to do this?<br>
			This cannot be undone.			
		</div>
		<div class="modal-footer">
			<button id="CloseButton" type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
			<button type="submit" action="post" class="btn btn-danger">Delete</button>
			
		</div>
		</div>
	</div>
	</div>
</form></div></div>


<?php require_once '../inc/footer.php'; ?>
