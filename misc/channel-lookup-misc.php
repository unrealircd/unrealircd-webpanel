<?php

function generate_chanbans_table($channel)
{
	global $rpc;
	$channel = $rpc->channel()->get($channel->name);
	?>
	<form method="post"><p>
	<button class="btn btn-info btn-sm" type="submit" name="delete_sel_ban">Delete</button>
	</p>
	<table class="table table-responsive table-hover caption-top table-striped">
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
	<table class="table table-responsive table-hover caption-top table-striped">
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
	<table class="table table-responsive table-hover caption-top table-striped">
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
	<table class="table table-responsive table-hover table-striped">
	<thead class="table-info">
		<th><input type="checkbox" label='selectall' onClick="toggle_checkbox(this)" /></th>
		<th>Name</th>
		<th>Status</th>
		<th>Quick Actions</th>
		<th></th>
	</thead>
	<tbody>
		<?php
		foreach ($channel->members as $member)
		{
			$lvlstring = "";

			if (isset($member->level))
			{
				for ($i = 0; isset($member->level[$i]) && $m = $member->level[$i]; $i++)
				{
					switch ($m)
					{
						case "v":
							$lvlstring .= "<div class='badge rounded-pill badge-primary'>Voice</div>";
							break;
						case "h":
							$lvlstring .= "<div class='badge rounded-pill badge-secondary'>Half-Op</div>";
							break;
						case "o":
							$lvlstring .= "<div class='badge rounded-pill badge-warning'>Op</div>";
							break;
						case "a":
							$lvlstring .= "<div class='badge rounded-pill badge-danger'>Admin</div>";
							break;
						case "q":
							$lvlstring .= "<div class='badge rounded-pill badge-success'>Owner</div>";
							break;
						
						// providing support third/ojoin
						case "Y":
							$lvlstring .= "<div class='badge rounded-pill'>OJOIN</div>";
							break;
					}
				}
			}
			echo "<tr>";
			?><form method="post" action=""><?php
			$disabled = (current_user_can(PERMISSION_EDIT_CHANNEL_USER)) ? "" : "disabled";
			$disabledcolor = ($disabled) ? "btn-secondary" : "btn-primary";
			echo "<td scope=\"row\"><input type=\"checkbox\" value='$member->id' name=\"checkboxes[]\"></td>";
			echo "<td><a href=\"".BASE_URL."users/details.php?nick=$member->id\">".htmlspecialchars($member->name)."</a></td>";
			echo "<td>$lvlstring</td>";
			echo "<td><button name=\"kickban\" value=\"$member->id\" type=\"submit\" class=\"btn-sm $disabledcolor\" $disabled>Kick ban</button></td>";
			echo "<td><button name=\"muteban\" value=\"$member->id\" type=\"submit\" class=\"btn-sm $disabledcolor\" $disabled>Mute ban</button></td>";
			echo "</tr>";
		}

		?>
	</tbody>
	</table>
	</form>

	<?php
}

function generate_html_chansettings($channel)
{
	global $rpc;
	?>

    <table class="table-sm table-responsive caption-top table-hover">
        <tbody>
           <?php
		   		if (BadPtr($channel->modes))
				{
					echo "No modes set";
					return;
				}
				$fmodes = $channel->modes;
				$tok = split($fmodes);
				$modes = $tok[0];
				$params = rparv($fmodes);
				$uplink = NULL;

				/* We get our uplink server so we can see what modes there are and in what group */
				$servlist = $rpc->server()->getAll();
				foreach($servlist as $serv) // find the one with no "->server->uplink" which will be our uplink
					if (BadPtr($serv->server->uplink)) // found it
						$uplink = $serv;

				if (!$uplink) // whaaaa?!¿
					die("Could not find our uplink. Weird and should not have happened");
				
				$groups = $uplink->server->features->chanmodes;
				
                for ($i=0; ($mode = (isset($modes[$i])) ? $modes[$i] : NULL); $i++)
                {
					$modeinfo = IRCList::$cmodes[$mode];
					?>
						<tr>
							<th><?php echo $modeinfo['name']; ?></th>
							<td>
								<?php echo $modeinfo['description']; ?>
							</td>
						</tr>
                	<?php
                }
				

           ?>
        </tbody>
    </table>

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