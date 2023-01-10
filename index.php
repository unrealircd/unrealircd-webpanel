<?php
require_once "common.php";

require_once "header.php";

rpc_pop_lists();
?>

	<table class='unrealircd_overview table'>
	<thead class="table-primary">
	<th>Chat Overview</th><th></th></thead>
		<tr><td><b>Users</b></td><td><?php echo count(RPC_List::$user); ?></td></tr>
		<tr><td><b>Opers</b></td><td><?php echo RPC_List::$opercount; ?></td></tr>
		<tr><td><b>Services</b></td><td><?php echo RPC_List::$services_count; ?></td></tr>
		<tr><td><b>Most popular channel</b></td><td><?php echo RPC_List::$most_populated_channel; ?> (<?php echo RPC_List::$channel_pop_count; ?> users)</td></tr>
		<tr><td><b>Channels</b></td><td><?php echo count(RPC_List::$channel); ?></td></tr>
		<tr><td><b>Server bans</b></td><td><?php echo count(RPC_List::$tkl); ?></td></tr>
		<tr><td><b>Spamfilter entries</b></td><td><?php echo count(RPC_List::$spamfilter); ?></td></tr></th>
	</table>