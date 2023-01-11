<?php
require_once "../common.php";
require_once UPATH . "/header.php";

if (!empty($_POST))
{
    do_log($_POST);

    /* Nothing being posted yet */

}

$channels = $rpc->channel()->getAll();

?>
<h4>Channels Overview</h4><br>
<table class="table table-responsive caption-top table-striped">
	<thead class="table-primary">
	<th>Name</th>
	<th>Users</th>
	<th>Modes</th>
	<th>Topic</th>
	<th>Created</th>
</thead>
<tbody>
	<?php
		foreach($channels as $channel)
		{
			echo "<tr>";
			echo "<td>".$channel->name."</td>";
			echo "<td>".$channel->num_users."</td>";
			$modes = (isset($channel->modes)) ? "+" . $channel->modes : "<none>";
			echo "<td>".$modes."</td>";
			$topic = (isset($channel->topic)) ? $channel->topic : "";
			echo "<td>".$topic."</td>";
			echo "<td>".$channel->creation_time."</td>";
		}
	?>
</tbody>
</table>
