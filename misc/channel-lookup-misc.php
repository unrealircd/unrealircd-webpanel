<?php

function generate_chanbans_table($channel)
{
	global $rpc;
	$channel = $rpc->channel()->get($channel->name);
	?>
	<form method="post"><p>
	<button class="btn btn-info btn-sm" type="submit" name="delete_sel_ban">Delete</button>
	</p>
	<table class="table table-responsive table-hover">
	<thead class="table-info">
		<th><input type="checkbox" label='selectall' onClick="toggle_chanbans(this)" /></th>
		<th>Name</th>
		<th>Set by</th>
		<th>Set at</th>
		<th></th>
	</thead>
	<tbody>
		<?php
		foreach ($channel->bans as $ban) {
			echo "<tr>";
			echo "<td scope=\"row\"><input type=\"checkbox\" value='$ban->name' name=\"cb_checkboxes[]\"></td>";
			echo "<td><code>".htmlspecialchars($ban->name)."</code></td>";
			$set_by = htmlspecialchars($ban->set_by);
			echo "<td>$set_by</td>";
			$set_at = $ban->set_at;
			echo "<td>$set_at</td>";
			echo "<td></td>";
			echo "</tr>";
		}

		?>
	</tbody>
	</table>
	</form>
	<?php
}
function generate_chaninvites_table($channel)
{
	global $rpc;
	$channel = $rpc->channel()->get($channel->name);
	?>
	<form method="post"><p>
	<button class="btn btn-info btn-sm" type="submit" name="delete_sel_inv">Delete</button>
	</p>
	<table class="table table-responsive table-hover">
	<thead class="table-info">
		<th><input type="checkbox" label='selectall' onClick="toggle_chaninvs(this)" /></th>
		<th>Name</th>
		<th>Set by</th>
		<th>Set at</th>
		<th></th>
	</thead>
	<tbody>
		<?php
		foreach ($channel->invite_exceptions as $inv) {
			echo "<tr>";
			echo "<td scope=\"row\"><input type=\"checkbox\" value='$inv->name' name=\"ci_checkboxes[]\"></td>";
			echo "<td><code>".htmlspecialchars($inv->name)."</code></td>";
			$set_by = htmlspecialchars($inv->set_by);
			echo "<td>$set_by</td>";
			$set_at = $inv->set_at;
			echo "<td>$set_at</td>";
			echo "<td></td>";
			echo "</tr>";
		}

		?>
	</tbody>
	</table>
	</form>
	<?php
}


function generate_chanexcepts_table($channel)
{
	global $rpc;
	$channel = $rpc->channel()->get($channel->name);
	?>
	<form method="post"><p>
	<button class="btn btn-info btn-sm" type="submit" name="delete_sel_ex">Delete</button>
	</p>
	<table class="table table-responsive table-hover">
	<thead class="table-info">
		<th><input type="checkbox" label='selectall' onClick="toggle_chanexs(this)" /></th>
		<th>Name</th>
		<th>Set by</th>
		<th>Set at</th>
		<th></th>
	</thead>
	<tbody>
		<?php
		foreach ($channel->ban_exemptions as $ex) {
			echo "<tr>";
			echo "<td scope=\"row\"><input type=\"checkbox\" value='$ex->name' name=\"ce_checkboxes[]\"></td>";
			echo "<td><code>".htmlspecialchars($ex->name)."</code></td>";
			$set_by = htmlspecialchars($ex->set_by);
			echo "<td>$set_by</td>";
			$set_at = $ex->set_at;
			echo "<td>$set_at</td>";
			echo "<td></td>";
			echo "</tr>";
		}

		?>
	</tbody>
	</table>
	</form>
	<?php
}

/**
 *  Generate the user list of a channel
 * 
 * Why is it called chan occupants? o.o
 * For the code, to avoid mixups
 * It's called "User List" on the website
 * @param mixed $channel
 * @return void
 */
function generate_chan_occupants_table($channel)
{
	?>
	<form method="post"><p>
	
	</p>
	<table class="table table-responsive table-hover">
	<thead class="table-info">
		<th><input type="checkbox" label='selectall' onClick="toggle_checkbox(this)" /></th>
		<th>Name</th>
		<th>Status</th>
		<th>Actions</th>
		<th></th>
	</thead>
	<tbody>
		<?php
		foreach ($channel->members as $member) {
			echo "<tr>";
			echo "<td scope=\"row\"><input type=\"checkbox\" value='$member->id' name=\"checkboxes[]\"></td>";
			echo "<td><a href=\"".BASE_URL."users/details.php?nick=$member->id\">".htmlspecialchars($member->name)."</a></td>";
			echo "<td>Status</td>";
			echo "<td>Op</td>";
			echo "<td>Deop</td>";
			echo "</tr>";
		}

		?>
	</tbody>
	</table>
	</form>

	<?php
}

/**
 * 	Force loading of a particular modal by name
 */
function chlkup_autoload_modal($name)
{
	?>
		<script>
			$(document).ready(function () {
				$("#<?php echo $name; ?>").modal("show");
			});
		</script>
	<?php
}


function _do_chan_item_delete($chan, string $type, array $list, array &$errors) : bool
{
	global $rpc;
	$n = "";
	$str = "";

	if ($type == "invite")
		$char = "I";
	elseif ($type == "ban")
		$char = "b";
	elseif ($type == "except")
		$char = "e";
	else
		return false;

	foreach($list as $l)
	{
		$n .= $char;
		$str .= " ".$l;
	}
	if ($rpc->channel()->set_mode($chan->name, "-$n", $str))
	{
		Message::Success("Deleted successfully");
		return true;
	}
	$errors[] = $rpc->error . " ($rpc->errno)";
		Message::Fail("An error occurred: $rpc->error");
	return false;
}

function do_delete_invite($chan, $list)
{
	$errs = [];
	_do_chan_item_delete($chan, "invite", $list, $errs);
}

function do_delete_chanban($chan, $list)
{
	$errs = [];
	_do_chan_item_delete($chan, "ban", $list, $errs);
}

function do_delete_chanex($chan, $list)
{
	$errs = [];
	_do_chan_item_delete($chan, "except", $list, $errs);
}