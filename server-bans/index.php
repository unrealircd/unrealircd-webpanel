<?php
require_once "../inc/common.php";
require_once "../inc/header.php";

if (!empty($_POST))
{
	require_once "../inc/connection.php";

	if (!empty($_POST['tklch'])) // User has asked to delete these tkls
	{
		if (!current_user_can(PERMISSION_SERVER_BAN_DEL))
		{
			Message::Fail("Could not delete: Permission denied");
		}
		else {
			foreach ($_POST['tklch'] as $key => $value) {
				$tok = explode(",", $value);
				$ban = base64_decode($tok[0]);
				$type = base64_decode($tok[1]);
				$success = false;
				if ($type == "except")
					$success = $rpc->serverbanexception()->delete($ban);
				else if ($type == "qline" || $type == "local-qline")
					$success = $rpc->nameban()->delete($ban);
				else
					$success = $rpc->serverban()->delete($ban, $type);


				if ($success)
					Message::Success("$type has been removed for $ban");
				else
					Message::Fail("Unable to remove $type on $ban: $rpc->error");
			}
		}
	}
	elseif (isset($_POST['do_add_ban']))
	{
		if (!current_user_can(PERMISSION_SERVER_BAN_ADD))
		{
			Message::Fail("Could not add: Permission denied");
		}
		else
		{
			if (empty($_POST['ban_host']) || empty($_POST['ban_type']))
			{
				Message::Fail("Unable to add Server Ban: No host or ban type selected");
			} else
			{
				$ban_host = $_POST['ban_host'];
				$ban_type = $_POST['ban_type'];
				$ban_soft = empty($_POST['ban_soft']) ? false : true;
				$ban_duration = $_POST['ban_duration'] ?? 0;
				$ban_reason = $_POST['ban_reason'] ?? '';
				if (!str_contains($ban_host, "@"))
					$ban_host = "*@$ban_host"; // prefix ban with *@ if no @ present
				if ($ban_soft)
					$ban_host = "%$ban_host"; // prefix ban with % if soft-ban
				if ($rpc->serverban()->add($ban_host, $ban_type, $ban_duration, $ban_reason))
				{
					Message::Success("Ban added on ".htmlspecialchars($ban_host));
				} else {
					$success = false;
					if (($rpc->errno == -1001) && !empty($_POST['edit_existing']))
					{
						// existing one = del + add
						// and yeah we do this after add() fails because then we now
						// at least the syntax and fields and everything are OK.
						// This so we don't accidentally remove a ban and the add fails
						// causing the edit to result in a deletion.
						$e = explode(":", $_POST['edit_existing'], 2);
						if (count($e) == 2)
						{
							if ($rpc->serverban()->delete($e[1], $e[0]))
							{
								/* Good, now try the add operation */
								if ($rpc->serverban()->add($ban_host, $ban_type, $ban_duration, $ban_reason))
								{
									Message::Success("Ban successfully modified: ".htmlspecialchars($ban_host));
									$success = true;
								}
							}
						}
					}
					if (!$success)
						Message::Fail("The ".htmlspecialchars($ban_type)." on ".htmlspecialchars($ban_host)." could not be added: $rpc->error / $rpc->errno");
				}
			}
		}
	}
	elseif (isset($_POST['search_types']) && !empty($_POST['search_types']))
	{
		
	}
}

?>
<h4>Server Bans Overview</h4>
Here are all your network bans, from K-Lines to G-Lines, it's all here.<br><br>
Click on an entry to edit it.
<!-- Top add button -->
<p><div class="btn btn-primary" onclick="add_ban()" <?php echo (current_user_can(PERMISSION_SERVER_BAN_ADD)) ? "" : "disabled"; ?>>
Add Ban</div></p></table>

<!-- Add/edit ban -->
	<div class="modal fade" id="ban_add" tabindex="-1" role="dialog" aria-labelledby="confirmModalCenterTitle" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<form method="post">
			<input name="edit_existing" type="hidden" id="edit_existing" value="">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="ban_add_title">Add server ban</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span></button>		
				</div>
				<div class="modal-body">
					<div class="form-group">
						<label for="ban_host">IP / Host</label>
						<input name="ban_host" type="text" class="form-control" id="ban_host" aria-describedby="ban_host_help" value="" required>
						<small id="ban_host_help" class="form-text text-muted">IP or host on which the ban is applied.</small>
					</div>
					<div class="form-group">
						<label for="ban_type">Type</label><br>
						<select class="curvy" name="ban_type" id="ban_type">
							<option value=""></option>
							<optgroup label="Bans">
							<option value="kline">Local Kill (K-Line)</option>
							<option value="gline">Global Kill (G-Line)</option>
							<option value="zline">Local Z-Line</option>
							<option value="gzline">Global Z-line</option>
							</optgroup>
						</select>
						<small id="ban_type_help" class="form-text text-muted">Usually K-Line or G-Line. Use Z-Lines with care.</small>
					</div>
					<div class="form-group">
						<input class="curvy input_text" type="checkbox" id="ban_soft" name="ban_soft"><label for="ban_soft">Soft-ban</label><br>
						<small id="ban_soft_help" class="form-text text-muted">Ban does not affect logged in users</small>
					</div>
					<div class="form-group">
						<label for="ban_duration">Duration</label>
						<input name="ban_duration" type="text" class="form-control" id="ban_duration" aria-describedby="ban_duration_help" value="" placeholder="(empty means permanent ban)">
						<small id="ban_duration_help" class="form-text text-muted">Duration of the ban in seconds, or in a format like 1d for 1 day. Leave empty for permanent ban</small>
					</div>
					<div class="form-group">
						<label for="ban_reason">Reason</label>
						<input name="ban_reason" type="text" class="form-control" id="ban_reason" aria-describedby="ban_reason_help" value="">
						<small id="ban_reason_help" class="form-text text-muted">Reason of the ban (shown to the banned user)</small>
					</div>
				</div>
								
				<div class="modal-footer">
					<button id="CloseButton" type="button" id="cancel_add_ban" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
					<button type="submit" name="do_add_ban" id="do_add_ban" class="btn btn-primary">Add Ban</button>
				</div>
			</div>
		</form>
	</div>
	</div>

	<!-- The banlist table -->
	<form method="post">
	<table id="data_list" class="table-striped display responsive nowrap" style="width:100%">
	<thead class="table-primary">
		<th scope="col"><input type="checkbox" label='selectall' onClick="toggle_tkl(this)" /></th>
		<th scope="col">Mask</th>
		<th scope="col">Type</th>
		<th scope="col">Duration</th>
		<th scope="col">Reason</th>
		<th scope="col">Set By</th>
		<th scope="col">Set On</th>
		<th scope="col">Expires</th>
	</thead>
	</table>

	<!-- Delete button -->
	<p><button type="button" class="btn btn-danger" data-toggle="modal" data-target="#myModal2" <?php echo (current_user_can(PERMISSION_SERVER_BAN_DEL)) ? "" : "disabled"; ?>>
	Delete selected
	</button></p>
	<div class="modal fade" id="myModal2" tabindex="-1" role="dialog" aria-labelledby="confirmModalCenterTitle" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
		<div class="modal-header">
			<h5 class="modal-title" id="myModalLabel">Confirm deletion</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			<span aria-hidden="true">&times;</span>
			</button>
		</div>
		<div class="modal-body">
			Are you sure you want to do this?<br>
			This cannot be undone.			
		</div>
		<div class="modal-footer">
			<button id="CloseButton" type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
			<button type="submit" action="post" class="btn btn-danger">Delete</button>
			
		</div>
		</div>
	</div>
	</div></form></div></div>

<script>
let data_list_table = null;

$(document).ready( function () {
	args = {
		'responsive': true,
		'fixedHeader': {
			header: true,
			headerOffset: 53
		},
		'ajax': {
			'url': '<?php echo get_config("base_url"); ?>api/server-bans.php',
			dataSrc: ''
		},
		'columns': [
			{ 'data': 'Select', 'responsivePriority': 1 },
			{ 'data': 'Mask', 'responsivePriority': 2, 'className':'virtuallink' },
			{ 'data': 'Type', 'responsivePriority': 3 },
			{ 'data': 'Duration', 'responsivePriority': 4 },
			{ 'data': 'Reason', 'responsivePriority': 5, 'render': DataTable.render.ellipsis(50, false) },
			{ 'data': 'Set By', 'responsivePriority': 6 },
			{ 'data': 'Set On', 'responsivePriority': 7 },
			{ 'data': 'Expires', 'responsivePriority': 8 },
		],
		'pageLength':100,
		'order':[[1,'asc']],
		createdRow: function(row) {
			var td = jQuery(row).find(".truncate");
			td.each(function(index, el) {
				jQuery(this).attr("title", jQuery(this).html());
				});
			},
	};
	/* Only show filter pane on desktop */
	if (window.innerWidth > 800)
	{
		args['dom'] = 'Pfrtip';
		args['searchPanes'] = {
			'initCollapsed': 'true',
			'columns': [2,3,5],
			'dtOpts': {
				select: { style: 'multi'},
				order: [[ 1, "desc" ]]
			},
		}
	}

	data_list_table = $('#data_list').DataTable(args);

	$('#data_list').on( 'click', 'td', function () {
		edit_ban(this);
	} );
} );

	function edit_ban(e)
	{
		/* The first column is the 'Select' column */
		if (data_list_table.cell(e).index().column == 0)
			return;
		/* For all the other columns we try to popup and edit screen */
		var data = data_list_table.row(e).data();
		$host = data['Mask'];
		if ($host.startsWith('%'))
		{
			$('#ban_host').val($host.substring(1));
			$('#ban_soft').prop('checked', true);
		} else {
			$('#ban_host').val($host);
			$('#ban_soft').prop('checked', false);
		}
		$type = data['Type'].replace('Soft ','');
		if ($type == 'Global Z-Line')
			$type = 'gzline';
		else if ($type == 'Z-Line')
			$type = 'zline';
		else if ($type == 'G-Line')
			$type = 'gline';
		else
			$type = 'kline';
		$('#ban_type').val($type);
		if (data['Duration'] == 'permanent')
			$('#ban_duration').val();
		else
			$('#ban_duration').val(data['Duration']);
		$('#ban_reason').val(data['Reason']);
		$('#do_del_ban').show();
		$('#ban_add_title').html("Edit server ban");
		$('#do_add_ban').html("Modify Ban");
		$('#edit_existing').val($type+':'+data['Mask']);
		$('#ban_add').modal('show');
	}

	// This is in a function because a canceled edit_rpc_server otherwise causes a prefilled effect
	function add_ban()
	{
		$('#edit_existing').val("");
		$('#ban_host').val("");
		$('#ban_type').val("");
		$('#ban_duration').val("");
		$('#ban_reason').val("");
		$('#ban_soft').prop('checked', false);
		$('#do_del_ban').hide();
		$('#ban_add_title').html("Add server ban");
		$('#do_add_ban').html("Add Ban");
		$('#ban_add').modal('show');
	}


</script>

<?php require_once '../inc/footer.php'; ?>
