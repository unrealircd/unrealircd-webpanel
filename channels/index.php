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
	<th>Created</th>
	<th>User count</th>
	<th>Topic</th>
	<th>Topic Set</th>
	<th>Modes</th>
</thead>
<tbody>
	<?php
		foreach($channels as $channel)
		{
			echo "<tr>";
			echo "<td>".$channel->name."</td>";
			echo "<td>".$channel->creation_time."</td>";
			echo "<td>".$channel->num_users."</td>";
			$topic = (isset($channel->topic)) ? $channel->topic : "";
			echo "<td>".$topic."</td>";
			$setby = (isset($channel->topic)) ? "By ".$channel->topic_set_by .", at ".$channel->topic_set_at : "";
			echo "<td>".$setby."</td>";
			$modes = (isset($channel->modes)) ? "+" . $channel->modes : "<none>";
			echo "<td>".$modes."</td>";
		}
	?>
</tbody>
</table>
