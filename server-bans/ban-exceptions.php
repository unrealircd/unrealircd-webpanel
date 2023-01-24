<?php
require_once "../common.php";

require_once "../header.php";


$ban_exceptions = $rpc->serverbanexception()->getAll();

?>
<h4>Ban Exceptions Overview</h4>
Here is where you can make an exception to bans, that is, to make it so that the target mask is exempt from the ban types you specify.<br>
<br>
<p><button type="button" class="btn btn-primary" data-toggle="modal" data-target="#myModal">
			Add entry
	</button></p></table>
	<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="confirmModalCenterTitle" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered modal-lg" role="document">
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
			<div class="align_label">Exception Type: </div> <select multiple name="bantype" id="bantype" data-live-search="true">
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

	<table class="container-xxl table table-sm table-responsive caption-top table-striped">
	<thead class="table-primary">
	<form method="post">
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
			echo "<td scope=\"col\">".$ban_exceptions->reason."</td>";
			$set_by = $set_in_config ? "<span class=\"badge rounded-pill badge-secondary\">Config</span>" : show_nick_only($ban_exceptions->set_by);
			echo "<td scope=\"col\">".$set_by."</td>";
			echo "<td scope=\"col\">".$ban_exceptions->set_at_string."</td>";
			echo "<td scope=\"col\">".$ban_exceptions->expire_at_string."</td>";
			echo "</tr>";
		}
	?></tbody></table><p><button type="button" class="btn btn-danger" data-toggle="modal" data-target="#myModal2">
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
