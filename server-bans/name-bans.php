<?php
require_once "../common.php";

require_once "../header.php";

if (!empty($_POST))
{

	do_log($_POST);

	if (isset($_POST['tklch']) && !empty($_POST['tklch'])) // User has asked to delete these tkls
	{
		if (!current_user_can(PERMISSION_NAME_BAN_DEL))
			Message::Fail("Could not delete name ban(s): Permission denied");
		else
			foreach ($_POST['tklch'] as $key => $value)
			{
				$tok = base64_decode($value);
				$success = false;
				$success = $rpc->nameban()->delete($tok);


				if ($success)
					Message::Success("Name Ban has been removed for $tok");
				else
					Message::Fail("Unable to remove Name Ban on $tok: $rpc->error");
			}
	}
	elseif (isset($_POST['tkl_add']) && !empty($_POST['tkl_add']))
	{
		if (!current_user_can(PERMISSION_NAME_BAN_ADD))
			Message::Fail("Could not add name ban(s): Permission denied");
		else
		{
			if (!($iphost = $_POST['tkl_add']))
				Message::Fail("No mask was specified");
			
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
		
			if ($rpc->nameban()->add($iphost, $reason, $duration))
				Message::Success("Name Ban set against \"$iphost\": $reason");
			else
				Message::Fail("Name Ban could not be set against \"$iphost\": $rpc->error");
		}
	}
	elseif (isset($_POST['search_types']) && !empty($_POST['search_types']))
	{
		
	}
}


$name_bans = $rpc->nameban()->getAll();

?>
<h4>Name Bans Overview</h4>
Here you can essentially forbid the use of a nick or channel name. This is useful to reserve services nicks so they cannot be used by normal users.<br>
You can also forbid the use of channel names. This is useful in such cases where an admin might need to close a channel for reasons relating to their own policy.<br>
<br>
<p><button type="button" class="btn btn-primary" data-toggle="modal" data-target="#myModal" <?php echo (current_user_can(PERMISSION_NAME_BAN_ADD)) ? "" : "disabled"; ?>>
			Add entry
	</button></p></table>
	<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="confirmModalCenterTitle" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
		<div class="modal-header">
			<h5 class="modal-title" id="myModalLabel">Add new Name Ban</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			<span aria-hidden="true">&times;</span>
			</button>
		</div>
		<div class="modal-body">
		
		<form  method="post">
			<div class="align_label">Nick / Channel</div> <input class="curvy" type="text" id="tkl_add" name="tkl_add"><br>
			
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
					<input class="curvy input_text" type="text" id="ban_reason" name="ban_reason"><br>
				
			</div>
			
		<div class="modal-footer">
			<button id="CloseButton" type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
			<button type="submit" action="post" class="btn btn-danger">Add Ban</button>
			</form>
		</div>
		</div>
	</div>
	</div>

	<table class="container-xxl table table-sm table-responsive caption-top table-striped">
	<thead class="table-primary">
	<form method="post" action="name-bans.php">
	<th scope="col"><input type="checkbox" label='selectall' onClick="toggle_tkl(this)" /></th>
	<th scope="col">Mask</th>
	<th scope="col">Duration</th>
	<th scope="col">Reason</th>
	<th scope="col">Set By</th>
	<th scope="col">Set On</th>
	<th scope="col">Expires</th>
	</thead>
	<tbody>
	<?php
		foreach($name_bans as $name_bans)
		{
			$set_in_config = ((isset($name_bans->set_in_config) && $name_bans->set_in_config) || ($name_bans->set_by == "-config-")) ? true : false;
			echo "<tr scope='col'>";
			if ($set_in_config)
				echo "<td scope=\"col\"></td>";
			else
				echo "<td scope=\"col\"><input type=\"checkbox\" value='" . base64_encode($name_bans->name)."' name=\"tklch[]\"></td>";
			echo "<td scope=\"col\">".$name_bans->name."</td>";
			echo "<td scope=\"col\">".$name_bans->duration_string."</td>";
			echo "<td scope=\"col\">".$name_bans->reason."</td>";
			$set_by = $set_in_config ? "<span class=\"badge rounded-pill badge-secondary\">Config</span>" : show_nick_only($name_bans->set_by);
			echo "<td scope=\"col\">".$set_by."</td>";
			echo "<td scope=\"col\">".$name_bans->set_at_string."</td>";
			echo "<td scope=\"col\">".$name_bans->expire_at_string."</td>";
			echo "</tr>";
		}
	?></tbody></table><p><button type="button" class="btn btn-danger" data-toggle="modal" data-target="#myModal2" <?php echo (current_user_can(PERMISSION_NAME_BAN_DEL)) ? "" : "disabled"; ?>>
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

<?php require_once '../footer.php'; ?>