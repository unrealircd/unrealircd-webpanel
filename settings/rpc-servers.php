<?php
require_once "../inc/common.php";
require_once "../inc/header.php";

if (isset($_POST['do_del_server']))
{
	$server = $_POST['del_server_name'] ?? null;
	if (isset($config["unrealircd"][$server]))
	{
		unset($config["unrealircd"][$server]);
		set_at_least_one_default_rpc_server();
		write_config("unrealircd");
	} else {
		Message::Fail("Delete failed: could not find server");
	}
} else
if (isset($_POST['do_add_server']))
{
	$opts = (object)$_POST;

	/* TODO: Server-side validation */

	// TODO: syntax of each item

	// TODO: check server already exists with that (new) displayname

	if (isset($config["unrealircd"][$opts->rpc_displayname]) &&
	    !($opts->rpc_displayname == $opts->edit_existing))
	{
		die("Server with that name already exists"); // TODO: pretier :D
	}

	$new_properties = [
		"rpc_user" => $opts->rpc_user,
		"rpc_password" => $opts->rpc_password,
		"host"=>$opts->rpc_host,
		"port"=>$opts->rpc_port,
		"tls_verify_cert"=>isset($opts->rpc_tls_verify_cert)?true:false,
		"default"=>isset($opts->rpc_default)?true:false,
	];

	if (!empty($opts->edit_existing))
	{
		// Change existing server
		if (!isset($config["unrealircd"][$opts->edit_existing]))
			die("Editing a server that does not exist!?"); // not very graceful, isn't it?
		if ($new_properties["rpc_password"] == "****************")
			$new_properties["rpc_password"] = $config["unrealircd"][$opts->edit_existing]["rpc_password"];
		// name change? unset the old one
		if ($opts->edit_existing != $opts->rpc_displayname)
			unset($config["unrealircd"][$opts->edit_existing]);
		// set new properties
		$config["unrealircd"][$opts->rpc_displayname] = $new_properties;
	} else {
		// Add new server
		$config["unrealircd"][$opts->rpc_displayname] = $new_properties;
		// TODO: encrypt pwd ;)
	}

	if ($new_properties["default"])
		set_default_rpc_server($opts->rpc_displayname);
	else
		set_at_least_one_default_rpc_server();

	/* And write the new config */
	write_config();

	Message::Success("RPC Server successfully ". (empty($opts->edit_existing) ? "added" : "modified").".");
}

?>

<h2>RPC Servers</h2>
You can configure which JSON-RPC server(s) the panel can connect to.<br><br>
You normally only need one server, but it can be useful to have multiple servers, so
you can switch to a secondary server in case the primary server goes down.<br>
<br>

<?php
if (empty($config["unrealircd"]))
{
	Message::Info("Let's get your panel linked to UnrealIRCd. ".
	              "Read <u><a href=\"https://www.unrealircd.org/docs/UnrealIRCd_webpanel#Configuring_UnrealIRCd\" target=\"_blank\">the UnrealIRCd instructions</a></u> ".
	              "and then click <i>Add Server</i> below.");
}
?>

<!-- Server action buttons (only Add server) -->
<div id="ServerActions">
	<div class="row">
		<?php if (1) /* current_user_can(PERMISSION_MANAGE_RPC_SERVERS)) */ { ?>
		<div class="col-sm-3">
			<form method="post">
			<div class="btn btn-primary" onclick="add_rpc_server()">Add Server</div>
			</form>
		</div>
		<?php } ?>
	</div>
<br>

<!-- Add server modal -->
<div class="modal" id="server_add" tabindex="-1" role="dialog" aria-labelledby="confirmModalCenterTitle" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<form method="post">
			<input name="edit_existing" type="hidden" id="edit_existing" value="">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="server_add_title">Add RPC Server</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span></button>		
				</div>
				<div class="modal-body">
					<div class="form-group">
						<label for="rpc_displayname">Display name</label>
						<input name="rpc_displayname" type="text" class="form-control" id="rpc_displayname" aria-describedby="rpc_displayname_help" value="" required>
						<small id="rpc_displayname_help" class="form-text text-muted">A short display name for in the RPC server list.</small>
					</div>
					<div class="form-group form-check">
						<input name="rpc_default" type="checkbox" class="revalidation-needed-rpc form-check-input" id="rpc_default">
						<label class="form-check-label" for="rpc_default">Default server</label>
						<small id="rpc_default_help" class="form-text text-muted">Make this the default (primary) server that will be used for connections.</code></small>
					</div>
					<div class="form-group">
						<label for="rpc_host">Hostname or IP</label>
						<input name="rpc_host" type="text" class="revalidation-needed-rpc form-control" id="rpc_host" aria-describedby="hostname_help" value="127.0.0.1">
						<small id="hostname_help" class="form-text text-muted">The hostname or IP address of your UnrealIRCd server. You should use <code>127.0.0.1</code> for the same machine.</small>
					</div>
					<div class="form-group">
						<label for="rpc_port">Server Port</label>
						<input name="rpc_port" type="text" class="revalidation-needed-rpc form-control" id="rpc_port" aria-describedby="port_help" value="8600">
						<small id="port_help" class="form-text text-muted">The port which you designated for RPC connections in your <code>unrealircd.conf</code></small>
					</div>
					<div class="form-group form-check">
						<input name="rpc_tls_verify_cert" type="checkbox" class="revalidation-needed-rpc form-check-input" id="rpc_tls_verify_cert">
						<label class="form-check-label" for="rpc_tls_verify_cert">Verify SSL/TLS certificate</label>
						<small id="rpc_tls_verify_cert_help" class="form-text text-muted">Can only be used with hostnames, don't enable this for 127.0.0.1.</code></small>
					</div>
					<div class="form-group">
						<label for="rpc_username">Username</label>
						<input name="rpc_user" type="text" class="revalidation-needed-rpc form-control" id="rpc_user" aria-describedby="username_help" autocomplete="new-password">
						<small id="username_help" class="form-text text-muted">The name of your <code>rpc-user</code> block as defined in your <code>unrealircd.conf</code></small>
					</div>
					<div class="form-group">
						<label for="rpc_password">Password</label>
						<input name="rpc_password" type="password" class="revalidation-needed-rpc form-control" id="rpc_password" autocomplete="new-password">
					</div>
				</div>
								
				<div class="modal-footer">
					<button id="CloseButton" type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
					<button type="submit" name="do_add_server" id="do_add_server" class="btn btn-primary">Add Server</button>
				</div>
			</div>
		</form>
	</div>
</div>

<!-- Server error modal -->
<div class="modal" id="server_error" tabindex="-1" role="dialog" aria-labelledby="confirmModalCenterTitle" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">RPC Server error</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				The RPC Server failed to connect. Check your settings and try again.
			</div>
							
			<div class="modal-footer">
				<button id="CloseButton" type="button" class="btn btn-primary" data-dismiss="modal" onclick="cancel_error()">OK</button>
			</div>
		</div>
	</div>
</div>

<!-- Server delete confirmation modal -->
<div class="modal" id="server_confirm_del" tabindex="-1" role="dialog" aria-labelledby="confirmModalCenterTitle" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<form method="post">
				<input name="del_server_name" type="hidden" id="del_server_name" value="">
				<div class="modal-header">
					<h5 class="modal-title">Confirm deletion</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					Are you sure you want to delete this server?
				</div>
				<div class="modal-footer">
					<button id="CloseButton" type="button" class="btn btn-primary" data-dismiss="modal">Cancel</button>
					<button type="submit" name="do_del_server" id="do_del_server" class="btn btn-danger">Delete Server</button>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- Todo figure out why it didnt work in css -->
<style>
.btn-group-xs > .btn, .btn-xs {
padding: 1px 5px;
font-size: 12px;
line-height: 1.5;
border-radius: 3px;
}
</style>

<!-- Server list -->
<form method="post">
<table class="container-xxl table table-sm table-responsive caption-top table-striped">
	<thead class="table-primary">
	<th scope="col"></th>
	<th scope="col">Display name</th>
	<th scope="col">Hostname</th>
	<th scope="col">Port</th>
	<th scope="col">RPC User</th>
	</thead>
	<tbody>
	<?php
		foreach($config['unrealircd'] as $name=>$e)
		{
			$primary = "";
			if (isset($e["default"]) && $e["default"])
                            $primary = " <span class=\"badge rounded-pill badge-success\">Default</span>";
                        $name = htmlspecialchars($name);
                        $default_server = $e["default"] ? "true" : "false";
                        $host = htmlspecialchars($e["host"]);
                        $port = htmlspecialchars($e["port"]);
                        $rpc_user = htmlspecialchars($e["rpc_user"]);
                        $tls_verify_cert = $e["tls_verify_cert"] ? "true" : "false";
                        $html_name = "<a href=\"javascript:edit_rpc_server('$name',$default_server,'$host','$port','$rpc_user',$tls_verify_cert)\">$name</a>";
			echo "<tr>";
			echo "<td scope=\"col\"><button type=\"button\" class=\"btn btn-xs btn-danger\" onclick=\"confirm_delete('".$name."')\"><i class=\"fa fa-trash fa-1\" aria-hidden=\"true\"></i></button></td>";
			echo "<td scope=\"col\">".$html_name.$primary."</td>";
			echo "<td scope=\"col\"><code>".$host."</code></td>";
			echo "<td scope=\"col\"><code>".$port."</code></td>";
			echo "<td scope=\"col\"><code>".$rpc_user."</code></td>";
			echo "</tr>";
		}
		?>
	</tbody>
</table>

<script>
	let do_add_server = document.getElementById('do_add_server');

	let rpc_host = document.getElementById('rpc_host');
	let rpc_port = document.getElementById('rpc_port');
	let rpc_user = document.getElementById('rpc_user');
	let rpc_pass = document.getElementById('rpc_password');
	let rpc_tls_verify_cert = document.getElementById('rpc_tls_verify_cert');
	let rpc_server_ok = false;
	
	do_add_server.addEventListener('click', e => {
		if (rpc_server_ok)
			return true;
		e.preventDefault();
		test_rpc_server();
		return false;
	});

	/* The RPC connection tester! */
	function test_rpc_server()
	{
		fetch(<?php echo get_config("base_url"); ?> + 'api/test_rpc_server.php', {
		      method:'POST',
		      headers: {'Content-Type':'application/x-www-form-urlencoded'},
		      body: 'method=rpc&'+
		            'host='+encodeURIComponent(rpc_host.value)+
		            '&port='+encodeURIComponent(rpc_port.value)+
		            '&user='+encodeURIComponent(rpc_user.value)+
		            '&password='+encodeURIComponent(rpc_pass.value)+
		            '&tls_verify='+rpc_tls_verify_cert.checked+
		            '&edit_existing='+encodeURIComponent(edit_existing.value)
		      })
		.then(response => response.json())
		.then(data => {
			if (data.success)
			{
				rpc_server_ok = true;
				do_add_server.click();
			}
			else
			{
				$('#server_add').modal('hide');
				$('#server_error').modal();
			}
		})
		.catch(error => {
			test_conn.innerHTML = "Failed!";
				$('#server_add').modal('hide');
				$('#server_error').modal();
		});
	}

	function cancel_error()
	{
		$('#server_add').modal('show');
		return true;
	}

	function edit_rpc_server(name, default_server, host, port, rpc_user, tls_verify_cert)
	{
		$('#edit_existing').val(name);
		$('#rpc_displayname').val(name);
		$('#rpc_default').prop('checked', default_server);
		$('#rpc_host').val(host);
		$('#rpc_port').val(port);
		$('#rpc_user').val(rpc_user);
		$('#rpc_password').val("****************"); // magic value to indicate saved password
		$('#server_add_title').html("Edit Server");
		$('#do_add_server').html("Submit");
		$('#rpc_tls_verify_cert').prop('checked', tls_verify_cert);
		$('#do_del_server').show();
		$('#server_add').modal('show');
	}

	// This is in a function because a canceled edit_rpc_server otherwise causes a prefilled effect
	function add_rpc_server()
	{
		$('#edit_existing').val("");
		$('#rpc_displayname').val("");
		$('#rpc_host').val("127.0.0.1");
		$('#rpc_port').val("8600");
		$('#rpc_user').val("");
		$('#rpc_password').val("");
		$('#server_add_title').html("Add Server");
		$('#do_add_server').html("Add Server");
		$('#rpc_tls_verify_cert').prop('checked', false);
		$('#do_del_server').hide();
		$('#server_add').modal('show');
	}

	function confirm_delete(name)
	{
		$('#del_server_name').val(name);
		$('#server_confirm_del').modal('show');
	}


</script>

<?php
require_once "../inc/footer.php";
