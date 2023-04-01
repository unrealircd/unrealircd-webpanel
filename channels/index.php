<?php
require_once "../common.php";
require_once "../connection.php";
require_once "../header.php";

if (!empty($_POST))
{
    do_log($_POST);

    /* Nothing being posted yet */

}

$channels = $rpc->channel()->getAll();

?>
<h4>Channels Overview</h4><br>
<table class="container-xxl table table-sm table-responsive caption-top table-striped">
	<thead class="table-primary">
	<th>Name</th>
	<th>Users</th>
	<th>Modes</th>
	<th>Topic</th>
	<th>Created</th>
</thead>
<tbody>
	<?php
		$columns = array_column($channels, 'num_users');
		array_multisort($columns, SORT_DESC, $channels);

		foreach($channels as $channel)
		{
			echo "<tr>";
			echo "<td><a href=\"details.php?chan=".urlencode(htmlspecialchars($channel->name))."\">".htmlspecialchars($channel->name)."</a></td>";
			echo "<td>".$channel->num_users."</td>";
			$modes = (isset($channel->modes)) ? "+" . $channel->modes : "<none>";
			echo "<td>".htmlspecialchars($modes)."</td>";
			$topic = (isset($channel->topic)) ? htmlspecialchars($channel->topic) : "";
			echo "<td>".$topic."</td>";
			echo "<td>".$channel->creation_time."</td>";
			echo "</tr>";
		}

	require_once("../footer.php");
	?>
</tbody>
</table>
