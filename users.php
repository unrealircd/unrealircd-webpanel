<?php
require_once "common.php";

require_once "header.php";

if (!empty($_POST))
{
	do_log($_POST);
	$bantype = $_POST['bantype'];
	if (isset($_POST['userch']))
	{
		foreach ($_POST["userch"] as $user)
		{
			$user = base64_decode($user);
			$bantype = (isset($_POST['bantype'])) ? $_POST['bantype'] : NULL;
			if (!$bantype) /* shouldn't happen? */
			{
				Message::Fail("An error occured");
				return;
			}
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

			$user = $rpc->user()->get($user);
			if (!user)
			{
				Message::Fail("Could not find that user. Maybe they disconnected after you clicked this?");
				return;
			}

			$msg_msg = ($duration == "0" || $duration == "0w0d0h") ? "permanently" : "for ".rpc_convert_duration_string($duration);
			$reason = (isset($_POST['ban_reason'])) ? $_POST['ban_reason'] : "No reason";
			if ($rpc->serverban()->add($user, $bantype, $duration, $reason))
				Message::Success($user->name . " (*@".$user->hostname.") has been $bantype" . "d $msg_msg: $reason");
		}
	}
}

/* Get the user list */
$users = $rpc->user()->getAll();
?>

<div id="Users">
	<table class='users_filter'>
	<th class="thuf">Filter by: </th>
	<th>
		<form action="users.php" method="post">
			Nick: <input name="uf_nick" id="uf_nick" type="text">
			<input class="cute_button2" type="submit" value="Search">
		</form>
	</th>
	<th>
		<form action="" method="post">
			Hostname: <input name="uf_host" id="uf_host" type="text">
			<input class="cute_button2" type="submit" value="Search">
		</form>
	</th>
	<th>
		<form action="" method="post">
			IP: <input name="uf_ip" id="uf_ip" type="text">
			<input class="cute_button2" type="submit" value="Search">
		</form>
	</th>
	<th class="thuffer">
		<form action="" method="post">
			Account: <input name="uf_account" id="uf_account" type="text">
			<input class="cute_button2" type="submit" value="Search">
		</form>
	</th>
	</form>
	</table>
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
	<table class='users_overview'>
	<th><input type="checkbox" label='selectall' onClick="toggle_user(this)" />Select all</th>
	<th>Nick</th>
	<th>UID</th>
	<th>Host / IP</th>
	<th>Account</th>
	<th>Usermodes <a href="https://www.unrealircd.org/docs/User_modes" target="_blank">ℹ️</a></th>
	<th>Oper</th>
	<th>Secure</th>
	<th>Connected to</th>
	<th>Reputation <a href="https://www.unrealircd.org/docs/Reputation_score" target="_blank">ℹ️</a></th>
	
	<form action="users.php" method="post">
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

			echo "<tr>";
			echo "<td><input type=\"checkbox\" value='" . base64_encode($user->id)."' name=\"userch[]\"></td>";
			$isBot = (strpos($user->user->modes, "B") !== false) ? ' <span class="label">Bot</span>' : "";
			echo "<td>".$user->name.$isBot.'</td>';
			echo "<td>".$user->id."</td>";
			echo "<td>".$user->hostname." (".$user->ip.")</td>";
			$account = (isset($user->user->account)) ? $user->user->account : '<span class="label bluelabel	">None</span>';
			echo "<td>".$account."</td>";
			$modes = (isset($user->user->modes)) ? "+" . $user->user->modes : "<none>";
			echo "<td>".$modes."</td>";
			$oper = (isset($user->user->operlogin)) ? $user->user->operlogin." <span class=\"label bluelabel\">".$user->user->operclass."</span>" : "";
			if (!strlen($oper))
				$oper = (strpos($user->user->modes, "S") !== false) ? '<span class="label secure-connection">Service</span>' : "";
			echo "<td>".$oper."</td>";
			$secure = (isset($user->tls)) ? "<span class=\"label secure-connection\">Secure</span>" : "<span class=\"label redlabel\">Insecure</span>";
			echo "<td>".$secure."</td>";
			echo "<td>".$user->user->servername."</td>";
			echo "<td>".$user->user->reputation."</td>";
		}
	?></table>
	<label for="bantype">Apply action: </label><br>
	<select name="bantype" id="bantype">
			<option value=""></option>
		<optgroup label="Bans">
			<option value="gline">GLine</option>
			<option value="gzline">GZLine</option>
		</optgroup>
	</select>
	<br>
	<label for="banlen_w">Duration: </label><br>
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
	<br><label for="ban_reason">Reason:<br></label>
	<textarea name="ban_reason" id="ban_reason">No reason</textarea><br>
	<input class="cute_button" type="submit" value="Apply">
	</form>
	
	</div
</div>

<?php require_once 'footer.php'; ?>
