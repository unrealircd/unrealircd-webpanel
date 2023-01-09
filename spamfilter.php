<?php
require_once "common.php";

require_once "header.php";

if (!empty($_POST))
{

	do_log($_POST);

	if ($sf = $_POST['sf_add']) // if it was a spamfilter entry
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
			$targ_chars = "";
			foreach($targets as $targ)
			{
				switch ($targ) {
					case "channel":
						$targ_chars .= "c";
						break;
					case "private":
						$targ_chars .= "p";
						break;
					case "channel-notice":
						$targ_chars .= "N";
						break;
					case "private-notice":
						$targ_chars .= "n";
						break;
					case "part":
						$targ_chars .= "P";
						break;
					case "quit":
						$targ_chars .= "q";
						break;
					case "dcc":
						$targ_chars .= "d";
						break;
					case "away":
						$targ_chars .= "a";
						break;
					case "topic":
						$targ_chars .= "t";
						break;
					case "messagetag":
						$targ_chars .= "T";
						break;
					case "user":
						$targ_chars .= "u";
						break;
				}
			}
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
					$targ_chars = "%" . $targ_chars;
				if ($rpc->spamfilter()->add($sf, $match_type, $targ_chars, $bantype, $duration, $reason))
					Message::Success("Added spamfilter entry \"$sf\" [match type: $match_type] [targets: $targ_chars] [reason: $reason]");
				else
					Message::Fail("Could not add spamfilter entry \"$sf\" [match type: $match_type] [targets: $targ_chars] [reason: $reason]: $rpc->error");
		}
	}
	else if (!empty($_POST['sf']))
		foreach ($_POST as $key => $value)
			foreach ($value as $tok)
			{
				$tok = explode(",", $tok);
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

$spamfilter = $rpc->spamfilter()->getAll();
?>
<div class="tkl_add_boxheader">
		Add Spamfilter Entry
	</div>
	<div class="tkl_add_form">
		
		<form action="spamfilter.php" method="post">
			<div class="align_label">Entry: </div><input class="input_text" type="text" id="sf_add" name="sf_add"><br>
			<div class="align_label">MatchType: </div><select name="matchtype" id="matchtype">
				<option value="simple">Simple</option>
				<option value="regex">Regular Expression</option>
			</select><br>
			<div class="align_label">Action: </div><select name="sf_bantype" id="sf_bantype">
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
			
			<div class="align_label"><label for="banlen_w">Targets: </label></div>
			<input type="checkbox" class="input_text" id="target_channel" name="target_channel">Channel messages<br>
			<div class="align_label"><label></label></div><input type="checkbox" class="input_text" id="target_private" name="target_private">Private messages<br>
			<div class="align_label"><label></label></div><input type="checkbox" class="input_text" id="target_channel_notice" name="target_channel_notice">Channel notices<br>
			<div class="align_label"><label></label></div><input type="checkbox" class="input_text" id="target_private_notice" name="target_private_notice">Private notices<br>
			<div class="align_label"><label></label></div><input type="checkbox" class="input_text" id="target_part" name="target_part">Part reason<br>
			<div class="align_label"><label></label></div><input type="checkbox" class="input_text" id="target_dcc" name="target_dcc">DCC Filename<br>
			<div class="align_label"><label></label></div><input type="checkbox" class="input_text" id="target_away" name="target_away">Away messages<br>
			<div class="align_label"><label></label></div><input type="checkbox" class="input_text" id="target_topic" name="target_topic">Channel topic<br>
			<div class="align_label"><label></label></div><input type="checkbox" class="input_text" id="target_messagetag" name="target_messagetag">MessageTags<br>
			<div class="align_label"><label></label></div><input type="checkbox" class="input_text" id="target_user" name="target_user">Userhost (nick!user@host:realname)<br>
			<div class="align_label"><label for="banlen_w">Duration: </label></div>
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
			<br><div class="align_label"><label for="ban_reason">Reason: </label></div>
			<input class="input_text" type="text" id="ban_reason" name="ban_reason"><br>
			<input class="input_text" type="checkbox" id="soft" name="soft">Don't affect logged-in users (soft)
			<div class="align_right_button_tkl_add"><input class="cute_button" type="submit" id="submit" value="Submit"></div>
		</form>
	</div>
	<table class='users_overview'>
	<form action="spamfilter.php" method="post">
	<th><input type="checkbox" label='selectall' onClick="toggle_sf(this)" />Select all</th>
	<th>Mask</th>
	<th>Type</th>
	<th>Set By</th>
	<th>Set On</th>
	<th>Expires</th>
	<th>Duration</th>
	<th>Match Type</th>
	<th>Action</th>
	<th>Action Duration</th>
	<th>Target</th>
	<th>Reason</th>
	
	<?php
		foreach($spamfilter as $sf)
		{
			echo "<tr>";
			echo "<td><input type=\"checkbox\" value='" . base64_encode($sf->name).",".base64_encode($sf->match_type).",".base64_encode($sf->spamfilter_targets).",".base64_encode($sf->ban_action) . "' name=\"sf[]\"></td>";
			echo "<td>".$sf->name."</td>";
			echo "<td>".$sf->type_string."</td>";
			echo "<td>".$sf->set_by."</td>";
			echo "<td>".$sf->set_at_string."</td>";
			echo "<td>".$sf->expire_at_string."</td>";
			echo "<td>".$sf->duration_string."</td>";
			echo "<td>".$sf->match_type."</td>";
			echo "<td>".$sf->ban_action."</td>";
			echo "<td>".$sf->ban_duration_string."</td>";
			for ($i = 0, $targs = ""; $i < strlen($sf->spamfilter_targets); $i++)
			{
				$c = $sf->spamfilter_targets[$i];
				if ($c == "c")
					$targs .= "Channel, ";
				else if ($c == "p")
					$targs .= "Private,";
				else if ($c == "n")
					$targs .= "Notice, ";
				else if ($c == "N")
					$targs .= "Channel notice, ";
				else if ($c == "P")
					$targs .= "Part message, ";
				else if ($c == "q")
					$targs .= "Quit message, ";
				else if ($c == "d")
					$targs .= "DCC filename, ";
				else if ($c == "a")
					$targs .= "Away message, ";
				else if ($c == "t")
					$targs .= "Channel topic, ";
				else if ($c == "T")
					$targs .= "MessageTag, ";
				else if ($c == "u")
					$targs .= "Usermask, ";
			}
			$targs = rtrim($targs,", ");
			echo "<td>".$targs."</td>";
			echo "<td>".$sf->reason."</td>";
			
		}
	?></table><p><input class="cute_button" type="submit" value="Delete selected"></p></form></div></div>


<?php require_once 'footer.php'; ?>
