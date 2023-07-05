<?php
function generate_chanbans_table($channel)
{
	global $rpc;
	$channel = $rpc->channel()->get($channel->name);
	?><p><table class="container-xxl table table-sm table-responsive caption-top table-striped">
	<button class="btn btn-primary mr-1 btn-sm" data-toggle="modal" data-target="#ban">Add New</button>
	<form method="post">
	<button class="btn btn-info btn-sm" type="submit" name="delete_sel_ban">Delete</button>
	</p>
	
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
	?><p><table class="table table-sm table-responsive table-hover caption-top table-striped">
	<button class="btn btn-primary btn-sm mr-1" data-toggle="modal" data-target="#invite">Add New</button>
	<form method="post">
	<button class="btn btn-info btn-sm" type="submit" name="delete_sel_inv">Delete</button>
	</p>
	
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
	?><p><table class="table table-sm table-responsive table-hover caption-top table-striped">
	<button class="btn btn-primary mr-1 btn-sm" data-toggle="modal" data-target="#except">Add New</button>
	<form method="post">
	<button class="btn btn-info btn-sm" type="submit" name="delete_sel_ex">Delete</button>
	</p>
	
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
	global $rpc;
	?>
	<form method="post"><p>
	
	</p>
	<table class="container-xxl table table-sm table-responsive caption-top table-striped">
	<thead class="table-info">
		<th><input type="checkbox" label='selectall' onClick="toggle_checkbox(this)" /></th>
		<th>Name</th>
		<th>Status</th>
		<th>Host</th>
	</thead>
	<tbody>
		<?php
		$m = sort_user_list($channel->members);

		$i = 0;
		foreach ($m as $member)
		{
			$lvlstring = "";

			if (isset($member->level))
			{
				for ($i = 0; isset($member->level[$i]) && $m = $member->level[$i]; $i++)
				{
					switch ($m)
					{
						case "v":
							$lvlstring .= "<div class='badge rounded-pill badge-primary'>Voice</div> ";
							break;
						case "h":
							$lvlstring .= "<div class='badge rounded-pill badge-secondary'>Half-Op</div> ";
							break;
						case "o":
							$lvlstring .= "<div class='badge rounded-pill badge-warning'>Op</div> ";
							break;
						case "a":
							$lvlstring .= "<div class='badge rounded-pill badge-danger'>Admin</div> ";
							break;
						case "q":
							$lvlstring .= "<div class='badge rounded-pill badge-success'>Owner</div> ";
							break;
						
						// providing support third/ojoin
						case "Y":
							$lvlstring .= "<div class='badge rounded-pill'>OJOIN</div> ";
							break;
					}
				}
			}
			
			echo "<tr>";
			?><form method="post" action=""><?php
			$disabled = (current_user_can(PERMISSION_EDIT_CHANNEL_USER)) ? "" : "disabled";
			$disabledcolor = ($disabled) ? "btn-secondary" : "btn-primary";
			echo "<td scope=\"row\"><input type=\"checkbox\" value='$member->id' name=\"checkboxes[]\"></td>";
			echo "<td><a href=\"".get_config("base_url")."users/details.php?nick=$member->id\">".htmlspecialchars($member->name)."</a></td>";
			echo "<td class='text-right'>$lvlstring</td>";
			echo "<td><code>".((property_exists($member, 'hostname')) ? htmlspecialchars($member->hostname) : "")."</code></td>";
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
				$paramed_modes = sort_paramed_modes($modes, $params);

                for ($i=0; ($mode = (isset($modes[$i])) ? $modes[$i] : NULL); $i++)
                {
					$modeinfo = IRCList::lookup($mode);
					if (!$modeinfo)
						continue;
					?>
						<tr>
							<th><?php echo $modeinfo['name']; ?></th>
							<td><code><?php echo isset($paramed_modes[$mode]) ? $paramed_modes[$mode] : ""; ?></code></td>
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

/** Return user-friendly mode group names as an array like:
 * "Join restrictions"=>"kliRzOL", ...
 * The group "Other" has a number of preset ones PLUS
 * will be automatically filled with any remaining modes
 * that are detected and that we forgot to put in Other.
 */
function group_modes()
{
	$modes = '';
	foreach(IRCList::$uplink as $mode_groups)
		$modes .= $mode_groups;
		
	$grouping = IRCList::$grouping;

	foreach (str_split($modes) as $letter)
	{
		$found = false;
		foreach($grouping as $groupname=>$value)
		{
			if (str_contains($value, $letter))
			{
				$found = true;
				break;
			}
		}
		if (!$found)
			$grouping["Other"] .= $letter;
		
	}
	return $grouping;
}

function generate_edit_chmodes($chan)
{
	?>
           <?php
		if (!isset($chan->modes))
		{
			echo "No modes set";
			return;
		}
		$fmodes = $chan->modes;
		$tok = split($fmodes);
		$modes = $tok[0];
		$params = rparv($fmodes);
		$paramed_modes = sort_paramed_modes($modes, $params);

		$all_modes = IRCList::$uplink;
		$groups = group_modes();

		foreach ($groups as $group_name=>$m)
		{
			echo "<fieldset class=\"border p-1 col-sm-6\">\n";
			echo "<legend class=\"w-auto\" style=\"font-size: 16px\">$group_name</legend>\n";
			for ($i=0; ($mode = (isset($m[$i])) ? $m[$i] : NULL); $i++)
			{
				$group = get_chmode_group($mode);
				if (!$group || $group == 1)
					continue;
				$modeinfo = IRCList::lookup($mode);
				$checked = (strstr($modes,$mode)) ? "checked " : " ";

				$disabled = "";
				if (isset($modeinfo) && $modeinfo['requires'] == "Server")
						$disabled = "disabled";
				
				?>
				<div class="form-group row">
					<div class="col-sm-5">
						<input <?php echo $checked.$disabled; ?> type="checkbox" value='<?php echo $mode; ?>' name="newmodes[]">
						<span data-toggle="tooltip" data-placement="top" title="<?php echo htmlspecialchars($modeinfo['description']); ?>"><?php echo htmlspecialchars($modeinfo['name'])." (<code>+$mode</code>)";  ?>
					</div>
					<div class="col-sm-2">
						<?php
							
							if ($group == 2 || $group == 3)
							{ 
								?><input type="text" class="input-group-sm" name="paramed_modes[<?php echo $mode; ?>]"
								id="<?php echo $mode; ?>" value="<?php echo ($checked)
								?
									htmlspecialchars(
										isset($paramed_modes[$mode])
										?
											$paramed_modes[$mode]
										:
											""
									)
								:
								 ""; ?>"><?php
							}
						?>
					</div>
				</div>
				<?php
			}
			echo "</fieldset>\n";
		}
           ?>
    <?php
}

function get_chmode_group(string $mode) : int
{
	foreach(IRCList::$uplink as $i => $grp)
		if (strstr($grp,$mode))
			return $i + 1;

	return 0; // invalid
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

/**
 * Returns an array of parametered modes and their params
 */
function sort_paramed_modes($modes, $params) : array
{
	$m = [];
	for ($i=0; isset($modes[$i]) && $mode = $modes[$i]; $i++)
	{
		$group = get_chmode_group($mode);
		if ($group != 2 && $group != 3)
			continue;
		$p = split($params);
		$m[$mode] = $p[0];
		$p[0] = NULL;
		$params = glue($p);
	}
	return $m;
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
		// check if the string needs therapy
		if ((strlen($n) + 1) + (strlen($str) + strlen($l)) > 150)
		{
			// send what we've got already and prepare for the next lot
			if (!$rpc->channel()->set_mode($chan->name, htmlspecialchars("-$n"), htmlspecialchars($str)))
				$errors[] = $rpc->error . " ($rpc->errno)";

			// prepare for the next set
			$n = "";
			$str = "";
		} 
		$n .= $char;
		$str .= " ".$l;
	}
	// if multiple, this will be the last one so we return here
	if ($rpc->channel()->set_mode($chan->name, htmlspecialchars("-$n"), htmlspecialchars($str)))
	{
		if (!empty($errors))
			Message::Fail("One or more errors occurred:", $errors);
		Message::Success("Deleted successfully");
		return true;
	}
	$errors[] = $rpc->error . " ($rpc->errno)";
		Message::Fail("An error occurred:", $errors);
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



/**
 * Sort the channels user list:
 */
function sort_user_list($list) : array
{
	if (empty($list))
		return $list;

	$new = [];
	foreach($list as $k => $user)
	{
		if (!property_exists($user,"level"))
		{
			$new["rest"][] = $user;
			$list[$k] = NULL;
		}
		else if (strstr($user->level,"Y"))
		{
			$new["Y"][] = $user;
			$list[$k] = NULL;
		}
		else if (strstr($user->level,"q"))
		{
			$new["q"][] = $user;
			$list[$k] = NULL;
		}
		else if (strstr($user->level,"a"))
		{
			$new["a"][] = $user;
			$list[$k] = NULL;
		}
		else if (strstr($user->level,"o"))
		{
			$new["o"][] = $user;
			$list[$k] = NULL;
		}
		else if (strstr($user->level,"h"))
		{
			$new["h"][] = $user;
			$list[$k] = NULL;
		}
		else if (strstr($user->level,"v"))
		{
			$new["v"][] = $user;
			$list[$k] = NULL;
		}
		
	}

	unset($list);
	$list = [];
	if (isset($new["q"]))
		foreach($new["q"] as $u)
			$list[] = $u;

	if (isset($new["a"]))
		foreach($new["a"] as $u)
			$list[] = $u;

	if (isset($new["o"]))
		foreach($new["o"] as $u)
			$list[] = $u;

	if (isset($new["h"]))
		foreach($new["h"] as $u)
			$list[] = $u;

	if (isset($new["v"]))
		foreach($new["v"] as $u)
			$list[] = $u;

	if (isset($new["rest"]))
		foreach($new["rest"] as $u)
			$list[] = $u;

	return $list;
}