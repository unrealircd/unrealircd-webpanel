<?php
$conn = NULL;

require_once "../inc/common.php";
require_once "../inc/header.php";
do_log($_POST);




?>
<h4>Panel Accounts</h4>

<?php

if (isset($_POST))
{
	$p = $_POST;
	if (isset($p['delete_user']) && current_user_can(PERMISSION_MANAGE_USERS))
	{
		$info = [];
		$msg = "Message::Fail";
		foreach ((array)($p['userch'] ?? []) as $id)
		{
			$user = new PanelUser(NULL, $id);
			$us = unreal_get_current_user();
			$deleted = delete_user($id, $info);
			if ($us->id == $user->id) // if it's the current user
			{
				/* (the page header was already sent, so no Location: header) */
				panel_session_logout();
				echo "<script>window.location = BASE_URL + 'login/';</script>";
				die();
			}
			$msg = ($deleted == 1) ? "Message::Success" : "Message::Fail";
		}
		if (!empty($info))
			$msg($info);
		unset($info);
	}

	if (isset($p['do_add_user']) && current_user_can(PERMISSION_MANAGE_USERS))
	{
		$user = [];
		/* Only accept roles that exist */
		if (!isset($p['user_role']) || !is_string($p['user_role']) || !array_key_exists($p['user_role'], get_panel_user_roles_list()))
		{
			Message::Fail("Failed to create user: unknown role");
			$p['do_add_user'] = NULL;
		}
	}
	if (isset($p['do_add_user']) && current_user_can(PERMISSION_MANAGE_USERS))
	{
		$user = [];
		$user['user_name'] = $p['user_add'];
		$user['user_pass'] = $p['password'];
		$user['fname'] = $p['add_first_name'];
		$user['lname'] = $p['add_last_name'];
		$user['user_email'] = $p['user_email'];
		$user['user_bio'] = $p['user_bio'];
		$user['err'] = "";
		if (!create_new_user($user))
		{
			Message::Fail("Failed to create user: " . $user['user_name'] . " " . $user['err']);
		}
		else if (($usr_obj = new PanelUser($user['user_name'])) && isset($usr_obj->id))
		{
			$usr_obj->add_meta("role", $p['user_role']);
			Message::Success("Successfully created user \"" . $user['user_name'] . "\"");
		}
		else
		{
			Message::Fail("Failed to create user \"" . $user['user_name'] . "\"");
		}
	}
}
$userlist = [];
Hook::run(HOOKTYPE_GET_USER_LIST, $userlist);

?>
Click on a username to view more information.
<br><br>
<div id="Users">
	<div class="row">
		<?php if (current_user_can(PERMISSION_MANAGE_USERS)) { ?>
		<div class="col-sm-3">
			<form method="post">
			<div class="btn btn-primary" data-toggle="modal" data-target="#myModal">Add New User</div>
			<button type="button" class="btn btn-danger" data-toggle="modal" data-target="#myModal2">Delete selected</button>
		</div>
		<?php } ?>
	</div>
<br>
</table>
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="confirmModalCenterTitle" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
			<h5 class="modal-title" id="myModalLabel">Add new Admin Panel user</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			<span aria-hidden="true">&times;</span></button>		
		</div>
		<div class="modal-body">
			<div class="input-group mb-3">
				<label for="name_add"  name="user_add" id="user_add">Username
					<input name="user_add" id="user_add" class="form-control curvy" type="text"></label>
			</div>
			<div class="input-group mb-3">
				<label for="password" id="user_add">Password
					<input name="password" id="password" class="form-control curvy" type="password"></label>
			</div>
			<div class="input-group mb-3">
				<label for="user_role" id="user_add">Role
				<select name="user_role" class="custom-select form-control" id="user_role" style="width:170%">
					<?php
						foreach(get_panel_user_roles_list() as $s => $l)
							echo "<option value=\"".h($s)."\">".h($s)."</option>";
					?>
				</select>
			</div>
			<div class="input-group mb-3">
				<label for="user_email" id="user_add">Email
					<input name="user_email" id="user_email" class="form-control curvy" type="text"></label>
			</div>
			<div class="input-group mb-3">
				<label for="add_first_name" id="user_add">First Name
					<input name="add_first_name" id="add_first_name" class="form-control curvy" type="text"></label>
			</div>
			<div class="input-group mb-3">
				<label for="password" id="user_add">Last Name
					<input name="add_last_name" id="add_last_name" class="form-control curvy" type="text"></label>
			</div>
			<div class="input-group mb-3">
				<label for="password" id="user_add">Info /Bio
					<textarea name="user_bio" class="form-control curvy" aria-label="With textarea"></textarea></label>
			</div>
		</div>
						
		<div class="modal-footer">
			<button id="CloseButton" type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
			<button type="submit" name="do_add_user" class="btn btn-danger">Add User</button>
			
		</div>
		</div>
	</div>
	</div>
	</div>

</form>
	<table class="container-xxl table table-sm table-responsive caption-top table-striped">
	<thead class="table-primary">
	<form method="post">
	<th scope="col"><input type="checkbox" label='selectall' onClick="toggle_tkl(this)" /></th>
	<th scope="col">Username</th>
	<th scope="col">Role</th>
	<th scope="col">First Name</th>
	<th scope="col">Last Name</th>
	<th scope="col">Email</th>
	<th scope="col">Created</th>
	<th scope="col">Bio</th>
	<th scope="col">Last login</th>
	
	</thead>
	<tbody>
	<?php
		foreach($userlist as $user)
		{
			
			echo "<tr>";
			echo "<td scope=\"col\"><input type=\"checkbox\" value=\"" .h($user->id) . "\" name=\"userch[]\"></td>";
			echo "<td scope=\"col\"><a href=\"".h(get_config("base_url")."settings/user-edit.php?id=".urlencode($user->id))."\">".h($user->username)."</a></td>";
			echo "<td scope=\"col\"><code>".((isset($user->user_meta['role'])) ? h($user->user_meta['role']) : "")."</code></td>";
			echo "<td scope=\"col\">".h($user->first_name)."</td>";
			echo "<td scope=\"col\">".h($user->last_name)."</td>";
			$email = (string)$user->email;
			if (filter_var($email, FILTER_VALIDATE_EMAIL))
				echo "<td scope=\"col\"><a href=\"mailto:".h($email)."\">".h($email)."</a></td>";
			else
				echo "<td scope=\"col\">".h($email)."</td>";
			echo "<td scope=\"col\"><code>".h($user->created)."</code></td>";
			echo "<td scope=\"col\">".h($user->bio)."</td>";
			$last = (isset($user->user_meta['last_login'])) ? "<code>".h($user->user_meta['last_login']) . "</code> <span class=\"badge rounded-pill badge-dark\">".h(how_long_ago($user->user_meta['last_login']))."</span>" : "none";
			echo "<td scope=\"col\">$last</td>";
			echo "</tr>\n";
		}
	?></tbody></table>
	<?php if (current_user_can(PERMISSION_MANAGE_USERS)) { ?>
		<p><button type="button" class="btn btn-danger" data-toggle="modal" data-target="#myModal2">
	Delete selected
	</button></p>
	<?php } ?>
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
			<button type="submit" action="post" name="delete_user" class="btn btn-danger">Delete</button>
			
		</div>
		</div>
	</div>
	</div></form></div></div><br></div>
<?php
require_once '../inc/footer.php'; ?>
