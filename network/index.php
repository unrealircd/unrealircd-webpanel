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
	<table class="table table-responsive caption-top table-striped">
	<thead>
		<th scope="col"><h5>Filter:</h5></th>
		<form action="" method="post">
		<th scope="col" colspan="2">Name<input name="sf_name" type="text" class="form-control short-form-control">
		<th scope="col"> <input class="btn btn-primary" type="submit" value="Search"></th></form>
	</thead></table>

	<table class="table table-responsive caption-top table-striped">
	<thead class="table-primary">
		<th scope="col"><input type="checkbox" label='selectall' onClick="toggle_server(this)" /></th>
		<th scope="col">Name</th>
		<th scope="col">Host / IP</th>
		<th scope="col"><span data-toggle="tooltip" data-placement="bottom" title="This shows [Secure] if the server is using SSL/TLS or is on localhost." style="border-bottom: 1px dotted #000000">Secure</span></th>
		<th scope="col">Connected to</th>
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
			$isBot = (strpos($server->server->modes, "B") !== false) ? ' <span class="badge-pill badge-dark">Bot</span>' : "";
			echo "<td><a href=\"details.php?nick=".$server->id."\">$server->name$isBot</a></td>";
			echo "<td>".$server->hostname." (".$server->ip.")</td>";
			$secure = (isset($server->tls)) ? "<span class=\"badge-pill badge-success\">Secure</span>" : "<span class=\"badge-pill badge-danger\">Insecure</span>";
			if (strpos($server->server->modes, "S") !== false)
				$secure = "";
			echo "<td>".$secure."</td>";
			echo "<td>".$server->server->uplink."</td>";
		}
	?>
	</tbody></table>
</div>

<?php require_once UPATH.'/footer.php'; ?>
