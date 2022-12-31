<!DOCTYPE html>
<link href="css/unrealircd-admin.css" rel="stylesheet">
<body>
<div id="headerContainer">
<h2>UnrealIRCd <small>Administration Panel</small></h2><br>
</div>
<script src="js/unrealircd-admin.js" defer></script>
<div class="topnav">
  <a data-tab-target="#overview" class="active" href="#overview">Overview</a>
  <a data-tab-target="#Users" href="#Users">Users</a>
  <a data-tab-target="#Channels" href="#Channels">Channels</a>
  <a data-tab-target="#TKL" href="#TKL">Server Bans</a>
  <a data-tab-target="#Spamfilter" href="#Spamfilter">Spamfilter</a>
</div> 
<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link	   https://https://github.com/ValwareIRC
 * @since	  1.0.0
 *
 * @package	Unrealircd
 * @subpackage Unrealircd/admin/partials
 */

define('UPATH', true);
include "Classes/class-rpc.php";

rpc_pop_lists(); // populate our static lists (users, channels, tkl, spamfilter)
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
	<p></p>
	<table class='users_overview'>
	<th>Nick</th>
	<th>UID</th>
	<th>Host / IP</th>
	<th>Account</th>
	<th>Usermodes</th>
	<th>Oper</th>
	<th>Secure</th>
	<th>Connected to</th>
	<th>Reputation <a href="https://www.unrealircd.org/docs/Reputation_score"></a>ℹ️</th>
	
	<?php
		foreach(RPC_List::$user as $user)
		{
			echo "<tr>";
			echo "<td>".$user['name']."</td>";
			echo "<td>".$user['id']."</td>";
			echo "<td>".$user['hostname']." (".$user['ip'].")</td>";
			$account = (isset($user['account'])) ? $user['account'] : "";
			echo "<td>".$account."</td>";
			$modes = (isset($user['user']['modes'])) ? "+" . $user['user']['modes'] : "<none>";
			echo "<td>".$modes."</td>";
			$oper = (isset($user['user']['operlogin'])) ? $user['user']['operlogin']." (".$user['user']['operclass'].")" : "";
			echo "<td>".$oper."</td>";
			$secure = (isset($user['tls'])) ? "✅" : "❌";
			echo "<td>".$secure."</td>";
			echo "<td>".$user['user']['servername']."</td>";
			echo "<td>".$user['user']['reputation']."</td>";
		}
	?></table></div></div>

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
	<p></p>
	<table class='users_overview'>
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
			echo "<td>".$tkl['name']."</td>";
			echo "<td>".$tkl['type_string']."</td>";
			echo "<td>".$tkl['set_by']."</td>";
			echo "<td>".$tkl['set_at_string']."</td>";
			echo "<td>".$tkl['expire_at_string']."</td>";
			echo "<td>".$tkl['duration_string']."</td>";
			echo "<td>".$tkl['reason']."</td>";
		}
	?></table></div></div>
	

	<div class="tab-content\">
	<div id="Spamfilter" data-tab-content>
	<p></p>
	<table class='users_overview'>
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
	?></table></div></div>
	
</body>

<div class="footer"><p>Copyright 2022 © <a href="https://unrealircd.org/">UnrealIRCd</a></p></div>
