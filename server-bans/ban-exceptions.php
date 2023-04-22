<?php
require_once "../inc/common.php";
require_once "../inc/connection.php";
require_once "../inc/header.php";
require_once "../misc/ban-exceptions-misc.php";
if (!empty($_POST))
{

	do_log($_POST);

	if (isset($_POST['tklch']) && !empty($_POST['tklch'])) // User has asked to delete these tkls
	{
		if (!current_user_can(PERMISSION_BAN_EXCEPTION_DEL))
			Message::Fail("Could not delete ban exception(s): Permission denied");
		else
			foreach ($_POST['tklch'] as $key => $value)
			{
				$tok = split($value, ",");
				$iphost = base64_decode($tok[0]);
				$success = false;
				$success = $rpc->serverbanexception()->delete($iphost);


				if ($success)
					Message::Success("Ban Exception has been removed for $iphost");
				else
					Message::Fail("Unable to remove Ban Exception on $iphost: $rpc->error");
			}
	}
	elseif (isset($_POST['tkl_add']) && !empty($_POST['tkl_add']))
	{
		if (!current_user_can(PERMISSION_BAN_EXCEPTION_ADD))
			Message::Fail("Could not add ban exception(s): Permission denied");
		else
		{
			if (!($iphost = $_POST['tkl_add']))
				Message::Fail("No mask was specified");

			$bantypes = isset($_POST['bantype']) ? $_POST['bantype'] : "";
			$bantypes_dup = "";
			if (!empty($bantypes))
				foreach ($bantypes as $bt)
					$bantypes_dup .= $bt;
			$bantypes = $bantypes_dup;
			/* duplicate code for now [= */
			$banlen_w = (isset($_POST['banlen_w'])) ? $_POST['banlen_w'] : NULL;
			$banlen_d = (isset($_POST['banlen_d'])) ? $_POST['banlen_d'] : NULL;
			$banlen_h = (isset($_POST['banlen_h'])) ? $_POST['banlen_h'] : NULL;
			$duration = "";
			if (!$banlen_d && !$banlen_h && !$banlen_w)
				$duration .= "0";
			else {
				if ($banlen_w)
					$duration .= $banlen_w;
				if ($banlen_d)
					$duration .= $banlen_d;
				if ($banlen_h)
					$duration .= $banlen_h;
			}
			$msg_msg = ($duration == "0" || $duration == "0w0d0h") ? "permanently" : "for " . rpc_convert_duration_string($duration);
			$reason = (isset($_POST['ban_reason'])) ? $_POST['ban_reason'] : "No reason";

			if (isset($_POST['soft']))
				$iphost = "%$iphost";
			if ($rpc->serverbanexception()->add($iphost, $bantypes, $reason, (($user = unreal_get_current_user())) ? $user->username : NULL, $duration))
				Message::Success("Ban Exception set against \"$iphost\": $reason");
			else
				Message::Fail("Ban Exception could not be set against \"$iphost\": $rpc->error");
		}
		
	}
	elseif (isset($_POST['search_types']) && !empty($_POST['search_types']))
	{
		
	}
}

$ban_exceptions = $rpc->serverbanexception()->getAll();

?>
<h4>Ban Exceptions Overview</h4>
Here is where you can make an exception to bans, that is, to make it so that the target mask is exempt from the ban types you specify.<br>
<br>
<p><button type="button" class="btn btn-primary" data-toggle="modal" data-target="#myModal" <?php echo (current_user_can(PERMISSION_BAN_EXCEPTION_ADD)) ? "" : "disabled"; ?>>
			Add entry
	</button></p></table>
	<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="confirmModalCenterTitle" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
		<div class="modal-header">
			<h5 class="modal-title" id="myModalLabel">Add new Ban Exception</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			<span aria-hidden="true">&times;</span>
			</button>
		</div>
		<div class="modal-body">
		
		<form  method="post">
			<div class="align_label">IP / Mask</div> <input class="curvy" type="text" id="tkl_add" name="tkl_add"><br>
			<div class="align_label">Exception Type: </div> <select multiple name="bantype[]" id="bantype" data-live-search="true">
				<option value=""></option>
				
					<option value="k">Kill Line (KLine)</option>
					<option value="G">Global Kill Line (GLine)</option>
					<option value="z">Zap Line (ZLine)</option>
					<option value="Z">Global Zap Line (GZLine)</option>
					<option value="Q">Reserve Nick Globally (QLine)</option>
					<option value="s">Shun</option>
					<option value="F">Spamfilter</option>
					<option value="b">Blacklist</option>
					<option value="c">Connect Flood</option>
					<option value="d">Handshake Flood</option>
					<option value="m">Max Per IP</option>
					<option value="r">Anti-Random</option>
					<option value="8">Anti-Mixed-UTF8</option>
					<option value="v">Versions</option>
			</select><br>
			<div class="align_label"><label for="banlen_w">Duration: </label></div>
					<select class="curvy" name="banlen_w" id="banlen_w">
							<?php
							for ($i = 0; $i <= 56; $i++)
							{
								if (!$i)
									echo "<option value=\"0w\"></option>";
								else
								{
									$w = ($i == 1) ? "week" : "weeks";
									echo "<option value=\"$i" . "w\">$i $w" . "</option>";
								}
							}
							?>
					</select>
					<select class="curvy" name="banlen_d" id="banlen_d">
							<?php
							for ($i = 0; $i <= 31; $i++)
							{
								if (!$i)
									echo "<option value=\"0d\"></option>";
								else
								{
									$d = ($i == 1) ? "day" : "days";
									echo "<option value=\"$i" . "d\">$i $d" . "</option>";
								}
							}
							?>
					</select>
					<select class="curvy" name="banlen_h" id="banlen_h">
							<?php
							for ($i = 0; $i <= 24; $i++)
							{
								if (!$i)
									echo "<option value=\"0d\"></option>";
								else
								{
									$h = ($i == 1) ? "hour" : "hours";
									echo "<option value=\"$i" . "h\">$i $h" . "</option>";
								}
							}
							?>
					</select>
					<br><div class="align_label"><label for="ban_reason">Reason: </label></div>
					<input class="curvy input_text" type="text" id="ban_reason" name="ban_reason">
				
			</div>
			
		<div class="modal-footer">
			<button id="CloseButton" type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
			<button type="submit" action="post" class="btn btn-danger">Add Ban Exception</button>
			</form>
		</div>
		</div>
	</div>
	</div>

	<table class="container-xxl table table-sm table-responsive caption-top table-striped">
	<thead class="table-primary">
	<form method="post">
	<th scope="col"><input type="checkbox" label='selectall' onClick="toggle_tkl(this)" /></th>
	<th scope="col">Mask</th>
	<th scope="col">Duration</th>
	<th scope="col">Type</th>
	<th scope="col">Exception Types</th>
	<th scope="col">Reason</th>
	<th scope="col">Set By</th>
	<th scope="col">Set On</th>
	<th scope="col">Expires</th>
	</thead>
	<tbody>
	<?php
		foreach($ban_exceptions as $ban_exceptions)
		{
			$set_in_config = ((isset($ban_exceptions->set_in_config) && $ban_exceptions->set_in_config) || ($ban_exceptions->set_by == "-config-")) ? true : false;
			echo "<tr scope='col'>";
			if ($set_in_config)
				echo "<td scope=\"col\"></td>";
			else
				echo "<td scope=\"col\"><input type=\"checkbox\" value='" . base64_encode($ban_exceptions->name).",".base64_encode($ban_exceptions->type) . "' name=\"tklch[]\"></td>";
			echo "<td scope=\"col\">".$ban_exceptions->name."</td>";
			echo "<td scope=\"col\">".$ban_exceptions->duration_string."</td>";
			echo "<td scope=\"col\"><span class=\"badge badge-pill badge-primary\">".$ban_exceptions->type."</span></td>";
			echo "<td scope=\"col\">".convert_exceptiontypes_to_badges($ban_exceptions->exception_types)."</td>";
			echo "<td scope=\"col\">".$ban_exceptions->reason."</td>";
			$set_by = $set_in_config ? "<span class=\"badge rounded-pill badge-secondary\">Config</span>" : show_nick_only($ban_exceptions->set_by);
			echo "<td scope=\"col\">".$set_by."</td>";
			echo "<td scope=\"col\">".$ban_exceptions->set_at_string."</td>";
			echo "<td scope=\"col\">".$ban_exceptions->expire_at_string."</td>";
			echo "</tr>";
		}
	?></tbody></table><p><button type="button" class="btn btn-danger" data-toggle="modal" data-target="#myModal2" <?php echo (current_user_can(PERMISSION_BAN_EXCEPTION_DEL)) ? "" : "disabled"; ?>>
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

<?php require_once '../inc/footer.php'; ?>
