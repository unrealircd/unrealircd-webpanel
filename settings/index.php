<?php
$conn = NULL;

require_once "../common.php";
require_once "../header.php";
do_log($_POST);




?>
<h4>Panel Settings Overview</h4>

<?php

if (isset($_POST))
{
	$p = $_POST;
	if (isset($p['delete_user']) && current_user_can(PERMISSION_MANAGE_USERS))
	{
		$info = [];
		foreach ($p['userch'] as $id)
		{
			$user = new PanelUser(NULL, $id);
			$us = unreal_get_current_user();
			$deleted = delete_user($id, $info);
			if ($us->id == $user->id) // if it's the current user
			{
				session_destroy();
				header("Location: " . get_config("base_url") . "plugins/sql_auth/login.php");
				die();
			}
			$msg = ($deleted = 1) ? "Message::Success" : "Message::Fail";
		}
		$msg($info);
		unset($info);
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
<br>
<h5>Panel Access</h5>
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
					<input style="width: 170%;" name="user_add" id="user_add" class="form-control curvy" type="text"></label>
			</div>
			<div class="input-group mb-3">
				<label for="password" id="user_add">Password
					<input style="width: 170%;" name="password" id="password" class="form-control curvy" type="password"></label>
			</div>
			<div class="input-group mb-3">
				<label for="user_email" id="user_add">Email
					<input style="width: 170%;" name="user_email" id="user_email" class="form-control curvy" type="text"></label>
			</div>
			<div class="input-group mb-3">
				<label for="add_first_name" id="user_add">First Name
					<input style="width: 170%;" name="add_first_name" id="add_first_name" class="form-control curvy" type="text"></label>
			</div>
			<div class="input-group mb-3">
				<label for="password" id="user_add">Last Name
					<input style="width: 170%;" name="add_last_name" id="add_last_name" class="form-control curvy" type="text"></label>
			</div>
			<div class="input-group mb-3">
				<label for="password" id="user_add">Info /Bio
					<textarea style="width: 170%;" name="user_bio" class="form-control curvy" aria-label="With textarea"></textarea></label>
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
			
			echo "<td scope=\"col\"><input type=\"checkbox\" value='" .$user->id . "' name=\"userch[]\"></td>";
			echo "<td scope=\"col\"><a href=\"".get_config("base_url")."settings/user-edit.php?id=$user->id\">$user->username</a></td>";
			echo "<td scope=\"col\">".$user->first_name."</td>";
			echo "<td scope=\"col\">".$user->last_name."</td>";
			echo "<td scope=\"col\"><a href=\"mailto:$user->email\">$user->email</a></td>";
			echo "<td scope=\"col\"><code>".$user->created."</code></td>";
			echo "<td scope=\"col\">".$user->bio."</td>";
			$last = (isset($user->user_meta['last_login'])) ? "<code>".$user->user_meta['last_login'] . "</code> <span class=\"badge rounded-pill badge-dark\">".how_long_ago($user->user_meta['last_login'])."</span>" : "none";
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
require_once '../footer.php'; ?>
