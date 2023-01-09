<?php
require_once "common.php";

require_once "header.php";

if (!empty($_POST))
{

	do_log($_POST);

	if (!empty($_POST['tklch'])) // User has asked to delete these tkls
	{
		foreach ($_POST as $key => $value) {
			foreach ($value as $tok) {
				$tok = explode(",", $tok);
				$ban = base64_decode($tok[0]);
				$type = base64_decode($tok[1]);
				if ($rpc->serverban()->delete($ban, $type))
					Message::Success("$type has been removed for $ban");
				else
					Message::Fail("Unable to remove $type on $ban: $rpc->error");
			}
		}
	}
	else if (!($iphost = $_POST['tkl_add']))
			Message::Fail("No user was specified");
	else if (!($bantype = (isset($_POST['bantype'])) ? $_POST['bantype'] : false))
	{
		Message::Fail("Unable to add Server Ban: No ban type selected");
	}
	else /* It did */
	{
		
		if ((
				$bantype == "gline" ||
				$bantype == "gzline" ||
				$bantype == "shun" ||
				$bantype == "eline"
			) && strpos($iphost, "@") == false) // doesn't have full mask
			$iphost = "*@" . $iphost;

		$soft = ($_POST['soft']) ? true : false;

		if ($soft)
			$iphost = "%" . $iphost;
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
		$msg_msg = ($duration == "0" || $duration == "0w0d0h") ? "permanently" : "for ".rpc_convert_duration_string($duration);
		$reason = (isset($_POST['ban_reason'])) ? $_POST['ban_reason'] : "No reason";
		if ($rpc->serverban()->add($iphost, $bantype, $duration, $reason))
		{
			Message::Success("Host / IP: $iphost has been $bantype" . "d $msg_msg: $reason");
		}
		else
			Message::Fail("The $bantype against \"$iphost\" could not be added: $rpc->error");
	}
}

$tkl = $rpc->serverban()->getAll();
?>
<div class="tkl_add_boxheader">
		Add Server Ban
	</div>
	<div class="tkl_add_form">
		
		<form action="tkl.php" method="post">
			<div class="align_label">IP / Host:</div><input class="input_text" type="text" id="tkl_add" name="tkl_add"><br>
			<div class="align_label">Ban Type:</div><select name="bantype" id="bantype">
				<option value=""></option>
				<optgroup label="Bans">
					<option value="kline">Kill Line (KLine)</option>
					<option value="gline">Global Kill Line (GLine)</option>
					<option value="zline">Zap Line (ZLine)</option>
					<option value="gzline">Global Zap Line (GZLine)</option>
					
				</optgroup>
				<optgroup label="Restrictions">
					<option value="local-qline">Reserve Nick Locally(QLine)</option>
					<option value="qline">Reserve Nick Globally (QLine)</option>
					<option value="shun">Shun</option>

				</optgroup>
				<optgroup label="Settings">
					<option value="except">Global Exception (ELine)</option>
					<option value="local-exception">Local Exception (ELine)</option>
				</optgroup>
			</select><br>
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
	<form action="tkl.php" method="post">
	<th><input type="checkbox" label='selectall' onClick="toggle_tkl(this)" />Select all</th>
	<th>Mask</th>
	<th>Type</th>
	<th>Set By</th>
	<th>Set On</th>
	<th>Expires</th>
	<th>Duration</th>
	<th>Reason</th>
	
	<?php
		foreach($tkl as $tkl)
		{
			echo "<tr>";
			echo "<td><input type=\"checkbox\" value='" . base64_encode($tkl->name).",".base64_encode($tkl->type) . "' name=\"tklch[]\"></td>";
			echo "<td>".$tkl->name."</td>";
			echo "<td>".$tkl->type_string."</td>";
			$set_by = ($tkl->set_by == "-config-") ? "<span class=\"badge-pill badge-secondary\">Config</span>" : $tkl->set_by;
			echo "<td>".$set_by."</td>";
			echo "<td>".$tkl->set_at_string."</td>";
			echo "<td>".$tkl->expire_at_string."</td>";
			echo "<td>".$tkl->duration_string."</td>";
			echo "<td>".$tkl->reason."</td>";
		}
	?></table><p><input class="cute_button" type="submit" value="Delete selected"></p></form></div></div>

<?php require_once 'footer.php'; ?>
