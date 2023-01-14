<?php
require_once "../common.php";
require_once UPATH . "/header.php";

if (!empty($_POST)) {
	do_log($_POST);
}

/* Get the server list */
$servers = $rpc->server()->getAll();
?>
<h4>Servers Overview</h4>

Click on a server name to view more information.

<div id="Servers">
	
	<?php
	if (isset($_POST['sf_name']) && strlen($_POST['sf_name']))
		Message::Info("Listing servers which match name: \"" . $_POST['sf_name'] . "\"");

	?>
	<table class="container-xxl table table-sm table-responsive caption-top table-striped">
	<thead>
		<th scope="col"><h5>Filter:</h5></th>
		<form action="" method="post">
		<th scope="col" colspan="2">Name<input name="sf_name" type="text" class="form-control short-form-control">
		<th scope="col"> <input class="btn btn-primary btn-sm" type="submit" value="Search"></th></form>
	</thead></table>

	<table class="container-xxl table table-sm table-responsive caption-top table-striped">
	<thead class="table-primary">
		<th scope="col"><input type="checkbox" label='selectall' onClick="toggle_server(this)" /></th>
		<th scope="col">Name</th>
		<th scope="col">Users</th>
		<th scope="col">Version</th>
		<th scope="col">Host / IP</th>
		<th scope="col">Connected to</th>
		<th scope="col">Up since</th>
	</thead>
	
	<tbody>
	<form method="post">
	<?php

		foreach($servers as $server)
		{

		
			/* Some basic filtering for NAME */
			if (isset($_POST['sf_name']) && strlen($_POST['sf_name']) && 
			strpos(strtolower($server->name), strtolower($_POST['sf_name'])) !== 0 &&
			strpos(strtolower($server->name), strtolower($_POST['sf_name'])) == false)
				continue;


			echo "<tr>";
			echo "<th scope=\"row\"><input type=\"checkbox\" value='" . base64_encode($server->id)."' name=\"serverch[]\"></th>";
			echo "<td><a href=\"details.php?server=".$server->id."\">$server->name</a></td>";
			echo "<td>".$server->server->num_users."</td>";
			$s = "";
			if (isset($server->server->features->software)) // not (always) present on services
				$s .= $server->server->features->software;

			if ($server->server->ulined == true)
					$s .= " <span class=\"badge rounded-pill badge-warning\">Services</span>";
			
			echo "<td>$s</td>";
			echo "<td>".$server->hostname." (".$server->ip.")</td>";
			if (isset($server->server->uplink))
				echo "<td>".$server->server->uplink."</td>";
			else
				echo "<td></td>"; /* self */
			echo "<td>".$server->server->boot_time."</td>";
		}
	?>
	</tbody></table>
</div>

<?php require_once UPATH.'/footer.php'; ?>
