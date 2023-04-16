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
	<th class="modescol">Modes</th>
	<th class="topiccol">Topic</th>
	<th class="createdcol">Created</th>
</thead>
<tbody>
	<?php
		$columns = array_column($channels, 'num_users');
		array_multisort($columns, SORT_DESC, $channels);

		foreach($channels as $channel)
		{
			echo "<tr>";
			echo "<td><a href=\"details.php?chan=".urlencode(htmlspecialchars($channel->name))."\">".htmlspecialchars($channel->name)."</a></td>";
			$s = ($channel->num_users) ? "success" : "danger";
			echo "<td><span class=\"badge rounded-pill badge-$s\">".$channel->num_users."</span></td>";
			$modes = (isset($channel->modes)) ? "+" . $channel->modes : "<none>";
			echo "<td class=\"modescol\">".htmlspecialchars($modes)."</td>";
			$topic = (isset($channel->topic)) ? htmlspecialchars($channel->topic) : "";
			echo "<td class=\"topiccol\" style=\"overflow:hidden;\">".$topic."</td>";
			$date = explode("T", $channel->creation_time)[0];
			echo "<td class=\"createdcol\" style=\"white-space:nowrap\">".
			     "<span data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"".$channel->creation_time."\">".
			     "$date</td>";
			echo "</tr>";
		}

	require_once("../footer.php");
	?>
</tbody>
</table>
<script>
	function resize_check()
	{
		var width = window.innerWidth;
		var show_elements = '';
		var hide_elements = '';
		if (width < 500)
		{
			show_elements = '.createdcol';
			hide_elements = '.modescol, .topiccol';
		} else
		if (width < 800)
		{
			show_elements = '.createdcol, .topiccol';
			hide_elements = '.modescol';
		} else
		{
			show_elements = '.createdcol, .modes, .topiccol';
			hide_elements = '';
		}

		if (show_elements != '')
		{
			show_elements=document.querySelectorAll(show_elements);
			for (let i = 0; i < show_elements.length; i++)
				show_elements[i].style.display = '';
		}

		if (hide_elements != '')
		{
			hide_elements=document.querySelectorAll(hide_elements);
			for (let i = 0; i < hide_elements.length; i++)
				hide_elements[i].style.display = 'none';
		}
	}
	resize_check();
	window.addEventListener('resize', function() {
		resize_check();
	});
</script>
