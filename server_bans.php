<?php
require_once "common.php";

require_once "header.php";

if (!empty($_POST))
{

	do_log($_POST);

	if (!empty($_POST['tklch'])) // User has asked to delete these tkls
	{
		foreach ($_POST as $key => $value) {
			foreach ($value as $tok) {
				$tok = explode(",", $tok);
				$ban = base64_decode($tok[0]);
				$type = base64_decode($tok[1]);
				if ($rpc->serverban()->delete($ban, $type))
					Message::Success("$type has been removed for $ban");
				else
					Message::Fail("Unable to remove $type on $ban: $rpc->error");
			}
		}
	}
	else if (!($iphost = $_POST['tkl_add']))
			Message::Fail("No user was specified");
	else if (!($bantype = (isset($_POST['bantype'])) ? $_POST['bantype'] : false))
	{
		Message::Fail("Unable to add Server Ban: No ban type selected");
	}
	else /* It did */
	{
		
		if ((
				$bantype == "gline" ||
				$bantype == "gzline" ||
				$bantype == "shun" ||
				$bantype == "eline"
			) && strpos($iphost, "@") == false) // doesn't have full mask
			$iphost = "*@" . $iphost;

		$soft = ($_POST['soft']) ? true : false;

		if ($soft)
			$iphost = "%" . $iphost;
		/* duplicate code for now [= */
		$banlen_w = (isset($_POST['banlen_w'])) ? $_POST['banlen_w'] : NULL;
		$banlen_d = (isset($_POST['banlen_d'])) ? $_POST['banlen_d'] : NULL;
		$banlen_h = (isset($_POST['banlen_h'])) ? $_POST['banlen_h'] : NULL;
		$duration = "";
		if (!$banlen_d && !$banlen_h && !$banlen_w)
			$duration .= "0";
		
		else
		{
			if ($banlen_w)
				$duration .= $banlen_w;
			if ($banlen_d)
				$duration .= $banlen_d;
			if ($banlen_h)
				$duration .= $banlen_h;
		}
		$msg_msg = ($duration == "0" || $duration == "0w0d0h") ? "permanently" : "for ".rpc_convert_duration_string($duration);
		$reason = (isset($_POST['ban_reason'])) ? $_POST['ban_reason'] : "No reason";
		if ($bantype == "qline")
		{
			if ($rpc->nameban()->add($iphost, $reason, $duration))
				Message::Success("Name Ban set against \"$iphost\": $reason");
			else
				Message::Fail("Name Ban could not be set against \"$iphost\": $rpc->error");
		}
		elseif ($bantype == "except")
		{
			if ($rpc->exception()->add($iphost, "", $duration, $reason))
				Message::Success("Exception set for \"$iphost\": $reason");
			else
				Message::Fail("Exception could not be set \"$iphost\": $rpc->error");
		}
		else if ($rpc->serverban()->add($iphost, $bantype, $duration, $reason))
		{
			Message::Success("Host / IP: $iphost has been $bantype" . "d $msg_msg: $reason");
		}
		else
			Message::Fail("The $bantype against \"$iphost\" could not be added: $rpc->error");
	}
}

$tkl = $rpc->serverban()->getAll();
foreach ($rpc->nameban()->getAll() as $v)
	$tkl[] = $v;
?>
<h4>Server Bans Overview</h4><br>
<p><button type="button" class="btn btn-primary" data-toggle="modal" data-target="#myModal">
			Add entry
	</button></p></table>
	<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="confirmModalCenterTitle" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
		<div class="modal-header">
			<h5 class="modal-title" id="myModalLabel">Add new Server Ban</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			<span aria-hidden="true">&times;</span>
			</button>
		</div>
		<div class="modal-body">
		
		<form  method="post">
			<div class="align_label">IP / Host: </div> <input class="curvy" type="text" id="tkl_add" name="tkl_add"><br>
			<div class="align_label">Ban Type: </div> <select class="curvy" name="bantype" id="bantype">
				<option value=""></option>
				<optgroup label="Bans">
					<option value="kline">Kill Line (KLine)</option>
					<option value="gline">Global Kill Line (GLine)</option>
					<option value="zline">Zap Line (ZLine)</option>
					<option value="gzline">Global Zap Line (GZLine)</option>
					
				</optgroup>
				<optgroup label="Restrictions">
					<option value="local-qline">Reserve Nick Locally(QLine)</option>
					<option value="qline">Reserve Nick Globally (QLine)</option>
					<option value="shun">Shun</option>

				</optgroup>
				<optgroup label="Settings">
					<option value="except">Global Exception (ELine)</option>
					<option value="local-exception">Local Exception (ELine)</option>
				</optgroup>
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
					<input class="curvy input_text" type="text" id="ban_reason" name="ban_reason"><br>
					<input class="curvy input_text" type="checkbox" id="soft" name="soft">Don't affect logged-in users (soft)
				
			</div>
			
		<div class="modal-footer">
			<button id="CloseButton" type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
			<button type="submit" action="post" class="btn btn-danger">Add Ban</button>
			</form>
		</div>
		</div>
	</div>
	</div>

	<table class="table table-responsive caption-top table-striped">
	<thead class="table-primary">
	<form method="post">
	<th><input type="checkbox" label='selectall' onClick="toggle_tkl(this)" />Select all</th>
	<th>Mask</th>
	<th>Type</th>
	<th>Set By</th>
	<th>Set On</th>
	<th>Expires</th>
	<th>Duration</th>
	<th>Reason</th>
	</thead>
	
	<?php
		foreach($tkl as $tkl)
		{
			echo "<tr>";
			echo "<td><input type=\"checkbox\" value='" . base64_encode($tkl->name).",".base64_encode($tkl->type) . "' name=\"tklch[]\"></td>";
			echo "<td>".$tkl->name."</td>";
			echo "<td>".$tkl->type_string."</td>";
			$set_by = ($tkl->set_by == "-config-") ? "<span class=\"badge-pill badge-secondary\">Config</span>" : show_nick_only($tkl->set_by);
			echo "<td>".$set_by."</td>";
			echo "<td>".$tkl->set_at_string."</td>";
			echo "<td>".$tkl->expire_at_string."</td>";
			echo "<td>".$tkl->duration_string."</td>";
			echo "<td>".$tkl->reason."</td>";
		}
	?></table><p><button type="button" class="btn btn-danger" data-toggle="modal" data-target="#myModal2">
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

<?php require_once 'footer.php'; ?>
