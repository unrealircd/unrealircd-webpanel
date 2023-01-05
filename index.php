<!DOCTYPE html>
<title>UnrealIRCd Panel</title>
<link rel="icon" type="image/x-icon" href="/img/favicon.ico">
<link href="css/unrealircd-admin.css" rel="stylesheet">
<body class="body-for-sticky">
<div id="headerContainer">
<h2><a href="">UnrealIRCd <small>Administration Panel</small></a></h2></div>
<script src="js/unrealircd-admin.js" defer></script>
<div class="topnav">
  <a data-tab-target="#overview" class="active" href="#overview">Overview</a>
  <a data-tab-target="#Users" href="#Users">Users</a>
  <a data-tab-target="#Channels" href="#Channels">Channels</a>
  <a data-tab-target="#TKL" href="#TKL">Server Bans</a>
  <a data-tab-target="#Spamfilter" href="#Spamfilter">Spamfilter</a>
  <a data-tab-target="#News" href="#News">News</a>
</div> 
<?php
define('UPATH', dirname(__FILE__));
include "config.php";
include "Classes/class-log.php";
include "Classes/class-message.php";
include "Classes/class-rpc.php";
do_log($_POST);

if (!empty($_POST)) {
	if (!($bantype = $_POST['bantype'])) {

	} else if (!($users = $_POST["userch"])) {
		Message::Fail("No user was specified");
	} else {
		foreach ($_POST["userch"] as $user) {
			$user = base64_decode($user);
			$bantype = (isset($_POST['bantype'])) ? $_POST['bantype'] : NULL;
			if (!$bantype)
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

			$rpc = new RPC();
			$rpc->set_method("user.get");
			$rpc->set_params(["nick" => "$user"]);
			$rpc->execute();
			$nick = ($rpc->result) ? $rpc->fetch_assoc() : NULL;
			if (!$nick)
			{
				Message::Fail("Could not find that user. Maybe they disconnected after you clicked this?");
				return;
			}

			$msg_msg = ($duration == "0" || $duration == "0w0d0h") ? "permanently" : "for $duration";
			$reason = (isset($_POST['ban_reason'])) ? $_POST['ban_reason'] : "No reason";
			if (rpc_tkl_add($user, $bantype, $duration, $reason))
			{
				$c = $nick['result']['client'];
				Message::Success($c['name'] . " (*@".$c['hostname'].") has been $bantype" . "d $msg_msg: $reason");
			}
		}
	}

	if (!empty($_POST['tklch']))
		foreach ($_POST as $key => $value) {
			foreach ($value as $tok) {
				$tok = explode(",", $tok);
				if (rpc_tkl_del(base64_decode($tok[0]), base64_decode($tok[1])))
					Message::Success(base64_decode($tok[1])." has been removed for ".base64_decode($tok[0]));
			}
		}

	if (!empty($_POST['sf']))
		foreach ($_POST as $key => $value) {
			foreach ($value as $tok) {
				$tok = explode(",", $tok);
				rpc_sf_del(base64_decode($tok[0]), base64_decode($tok[1]), base64_decode($tok[2]), base64_decode($tok[3]));
			}
		}
}

rpc_pop_lists();
?>

<div class="tab-content\">
<div id="overview" data-tab-content class="active">
	<table class='unrealircd_overview'>
	<th>Chat Overview</th><th></th>
		<tr><td><b>Users</b></td><td><?php echo count(RPC_List::$user); ?></td></tr>
		<tr><td><b>Opers</b></td><td><?php echo RPC_List::$opercount; ?></td></tr>
		<tr><td><b>Services</b></td><td><?php echo RPC_List::$services_count; ?></td></tr>
		<tr><td><b>Most popular channel</b></td><td><?php echo RPC_List::$most_populated_channel; ?> (<?php echo RPC_List::$channel_pop_count; ?> users)</td></tr>
		<tr><td><b>Channels</b></td><td><?php echo count(RPC_List::$channel); ?></td></tr>
		<tr><td><b>Server bans</b></td><td><?php echo count(RPC_List::$tkl); ?></td></tr>
		<tr><td><b>Spamfilter entries</b></td><td><?php echo count(RPC_List::$spamfilter); ?></td></tr></th>
	</table></div></div>
	
	<div class="tab-content\">
	<div id="Users" data-tab-content>
	<table class='users_filter'>
	<th class="thuf">Filter by: </th>
	<th>
		<form action="" method="post">
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
	<th>Usermodes<a href="https://www.unrealircd.org/docs/User_modes" target="_blank">ℹ️</a></th>
	<th>Oper</th>
	<th>Secure</th>
	<th>Connected to</th>
	<th>Reputation <a href="https://www.unrealircd.org/docs/Reputation_score" target="_blank">ℹ️</a></th>
	
	<form action="" method="post">
	<?php
		foreach(RPC_List::$user as $user)
		{

			/* Some basic filtering for NICK */
			if (isset($_POST['uf_nick']) && strlen($_POST['uf_nick']) && 
			strpos(strtolower($user['name']), strtolower($_POST['uf_nick'])) !== 0 &&
			strpos(strtolower($user['name']), strtolower($_POST['uf_nick'])) == false)
				continue;

			/* Some basic filtering for HOST */
			if (isset($_POST['uf_host']) && strlen($_POST['uf_host']) && 
			strpos(strtolower($user['hostname']), strtolower($_POST['uf_host'])) !== 0 &&
			strpos(strtolower($user['hostname']), strtolower($_POST['uf_host'])) == false)
				continue;

			/* Some basic filtering for IP */
			if (isset($_POST['uf_ip']) && strlen($_POST['uf_ip']) && 
			strpos(strtolower($user['ip']), strtolower($_POST['uf_ip'])) !== 0 &&
			strpos(strtolower($user['ip']), strtolower($_POST['uf_ip'])) == false)
				continue;

			/* Some basic filtering for ACCOUNT */
			if (isset($_POST['uf_account']) && strlen($_POST['uf_account']) && 
			strpos(strtolower($user['user']['account']), strtolower($_POST['uf_account'])) !== 0 &&
			strpos(strtolower($user['user']['account']), strtolower($_POST['uf_account'])) == false)
				continue;

			echo "<tr>";
			echo "<td><input type=\"checkbox\" value='" . base64_encode($user['id'])."' name=\"userch[]\"></td>";
			echo "<td>".$user['name']."</td>";
			echo "<td>".$user['id']."</td>";
			echo "<td>".$user['hostname']." (".$user['ip'].")</td>";
			$account = (isset($user['user']['account'])) ? '<span class="label">'.$user['user']['account'].'</span>' : '<span class="label noaccount">No account</span>';
			echo "<td>".$account."</td>";
			$modes = (isset($user['user']['modes'])) ? "+" . $user['user']['modes'] : "<none>";
			echo "<td>".$modes."</td>";
			$oper = (isset($user['user']['operlogin'])) ? $user['user']['operlogin']." <span class=\"label operclass-label\">".$user['user']['operclass']."</span>" : "";
			echo "<td>".$oper."</td>";
			$secure = (isset($user['tls'])) ? "<span class=\"label secure-connection\">Secure</span>" : "<span class=\"label noaccount\">Insecure</span>";
			echo "<td>".$secure."</td>";
			echo "<td>".$user['user']['servername']."</td>";
			echo "<td>".$user['user']['reputation']."</td>";
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
	
	</div></div>

	<div class="tab-content\">
	<div id="Channels" data-tab-content>
	<p></p>
	<table class='users_overview'>
	<th>Name</th>
	<th>Created</th>
	<th>User count</th>
	<th>Topic</th>
	<th>Topic Set</th>
	<th>Modes</th>
	
	<?php
		foreach(RPC_List::$channel as $channel)
		{
			echo "<tr>";
			echo "<td>".$channel['name']."</td>";
			echo "<td>".$channel['creation_time']."</td>";
			echo "<td>".$channel['num_users']."</td>";
			$topic = (isset($channel['topic'])) ? $channel['topic'] : "";
			echo "<td>".$topic."</td>";
			$setby = (isset($channel['topic'])) ? "By ".$channel['topic_set_by'] .", at ".$channel['topic_set_at'] : "";
			echo "<td>".$setby."</td>";
			$modes = (isset($channel['modes'])) ? "+" . $channel['modes'] : "<none>";
			echo "<td>".$modes."</td>";
		}
	?></table></div></div>


	<div class="tab-content\">
	<div id="TKL" data-tab-content>
	
	<table class='users_overview'>
	<form action="" method="post">
	<th><input type="checkbox" label='selectall' onClick="toggle_tkl(this)" />Select all</th>
	<th>Mask</th>
	<th>Type</th>
	<th>Set By</th>
	<th>Set On</th>
	<th>Expires</th>
	<th>Duration</th>
	<th>Reason</th>
	
	<?php
		foreach(RPC_List::$tkl as $tkl)
		{
			echo "<tr>";
			echo "<td><input type=\"checkbox\" value='" . base64_encode($tkl['name']).",".base64_encode($tkl['type']) . "' name=\"tklch[]\"></td>";
			echo "<td>".$tkl['name']."</td>";
			echo "<td>".$tkl['type_string']."</td>";
			echo "<td>".$tkl['set_by']."</td>";
			echo "<td>".$tkl['set_at_string']."</td>";
			echo "<td>".$tkl['expire_at_string']."</td>";
			echo "<td>".$tkl['duration_string']."</td>";
			echo "<td>".$tkl['reason']."</td>";
		}
	?></table><p><input class="cute_button" type="submit" value="Delete selected"></p></form></div></div>
	

	<div class="tab-content\">
	<div id="Spamfilter" data-tab-content>
	<p></p>
	<table class='users_overview'>
	<form action="" method="post">
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
		foreach(RPC_List::$spamfilter as $sf)
		{
			echo "<tr>";
			echo "<td><input type=\"checkbox\" value='" . base64_encode($sf['name']).",".base64_encode($sf['match_type']).",".base64_encode($sf['spamfilter_targets']).",".base64_encode($sf['ban_action']) . "' name=\"sf[]\"></td>";
			echo "<td>".$sf['name']."</td>";
			echo "<td>".$sf['type_string']."</td>";
			echo "<td>".$sf['set_by']."</td>";
			echo "<td>".$sf['set_at_string']."</td>";
			echo "<td>".$sf['expire_at_string']."</td>";
			echo "<td>".$sf['duration_string']."</td>";
			echo "<td>".$sf['match_type']."</td>";
			echo "<td>".$sf['ban_action']."</td>";
			echo "<td>".$sf['ban_duration_string']."</td>";
			for ($i = 0, $targs = ""; ($c = $sf['spamfilter_targets'][$i]); $i++)
			{
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

				$targs = rtrim($targs,", ");
			}
			echo "<td>".$targs."</td>";
			echo "<td>".$sf['reason']."</td>";
			
		}
	?></table><p><input class="cute_button" type="submit" value="Delete selected"></p></form></div></div>



	<div class="tab-content\">
	<div id="News" data-tab-content>
	<iframe style="border:none;" height="1000" width="600" data-tweet-url="https://twitter.com/Unreal_IRCd" src="data:text/html;charset=utf-8,%3Ca%20class%3D%22twitter-timeline%22%20href%3D%22https%3A//twitter.com/Unreal_IRCd%3Fref_src%3Dtwsrc%255Etfw%22%3ETweets%20by%20Unreal_IRCd%3C/a%3E%0A%3Cscript%20async%20src%3D%22https%3A//platform.twitter.com/widgets.js%22%20charset%3D%22utf-8%22%3E%3C/script%3E%0A%3Cstyle%3Ehtml%7Boverflow%3Ahidden%20%21important%3B%7D%3C/style%3E"></iframe>
	<iframe style="border:none;" height="1000" width="600" data-tweet-url="https://twitter.com/irc_stats" src="data:text/html;charset=utf-8,%3Ca%20class%3D%22twitter-timeline%22%20href%3D%22https%3A//twitter.com/irc_stats%3Fref_src%3Dtwsrc%255Etfw%22%3ETweets%20by%20IRC%20Stats%3C/a%3E%0A%3Cscript%20async%20src%3D%22https%3A//platform.twitter.com/widgets.js%22%20charset%3D%22utf-8%22%3E%3C/script%3E%0A%3Cstyle%3Ehtml%7Boverflow%3Ahidden%20%21important%3B%7D%3C/style%3E"></iframe>
	</div></div>
	
</body>
