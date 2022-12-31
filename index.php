<!DOCTYPE html>
<link href="/css/unrealircd-admin.css" rel="stylesheet">
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
	<p>Your shiny IRC overview</p>
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
	<th>IP/Host</th>
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
	?>

</body>

<div class="footer"><p>Copyright 2022 © <a href="https://unrealircd.org/">UnrealIRCd</a></p></div>