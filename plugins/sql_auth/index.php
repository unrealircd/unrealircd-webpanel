<?php
$conn = NULL;

require_once "../../common.php";
require_once "../../header.php";
require_once "SQL/sql.php";
require_once "SQL/user.php";
do_log($_POST);




?>
<h4>Panel Access Overview</h4>
<?php
    if (isset($_POST))
    {
		// TODO:  Validation and stuff
        $p = $_POST;
		if (isset($p['delete_user']))
		{
			$info = [];
			foreach ($p['userch'] as $id)
			{
				$user = new SQLA_User(NULL, $id);
				$us = unreal_get_current_user();
				$deleted = delete_user($id, $info);
				if ($us->id == $user->id) // if it's the current user
				{
					session_destroy();
					header("Location: " . BASE_URL . "plugins/sql_auth/login.php");
					die();
				}
				$msg = ($deleted = 1) ? "Message::Success" : "Message::Fail";
			}
			$msg($info);
			unset($info);
		}

        if (isset($p['do_add_user']))
        {
			$user = [];
			$user['user_name'] = $p['user_add'];
			$user['user_pass'] = $p['password'];
			$user['fname'] = $p['add_first_name'];
			$user['lname'] = $p['add_last_name'];
			$user['user_bio'] = $p['user_bio'];
			create_new_user($user);
			if (($usr_obj = new SQLA_User($p['user_name'])) && !$usr_obj->id)
			{
				Message::Success("Successfully created user \"" . $user['user_name'] . "\"");
			}
			else
			{
				Message::Fail("Failed to create user \"" . $user['user_name'] . "\"");
			}
		}
    }
	$conn = sqlnew();
	$result = $conn->query("SELECT user_id FROM " . SQL_PREFIX . "users");
	$userlist = [];
	while($row =  $result->fetch())
	{
		$userlist[] = new SQLA_User(NULL, $row['user_id']);
	}

	if (!$result) // impossible
	{
		die("Something went wrong.");
	}

?>
Click on a username to view more information.
<br><br>
<div id="Users">
	
            <form method="post">
<p><button type="button" class="btn btn-primary" data-toggle="modal" data-target="#myModal">
			Add New User
	</button></p>
</table>
	<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="confirmModalCenterTitle" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
		<div class="modal-header">
			<h5 class="modal-title" id="myModalLabel">Add new Admin Panel user</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			<span aria-hidden="true">&times;</span>
			</button>
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
                <label for="add_first_name" id="user_add">First Name
                    <input style="width: 170%;" name="add_first_name" id="add_first_name" class="form-control curvy" type="text"></label>
            </div><div class="input-group mb-3">
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
</form>
	<table class="container-xxl table table-sm table-responsive caption-top table-striped">
	<thead class="table-primary">
	<form method="post">
	<th scope="col"><input type="checkbox" label='selectall' onClick="toggle_tkl(this)" /></th>
	<th scope="col">Username</th>
	<th scope="col">First Name</th>
	<th scope="col">Last Name</th>
    <th scope="col">Created</th>
    <th scope="col">Bio</th>
    
	</thead>
	<tbody>
	<?php
		foreach($userlist as $user)
		{
			
			echo "<td scope=\"col\"><input type=\"checkbox\" value='" .$user->id . "' name=\"userch[]\"></td>";
			echo "<td scope=\"col\">".$user->username."</td>";
			echo "<td scope=\"col\">".$user->first_name."</td>";
			echo "<td scope=\"col\">".$user->last_name."</td>";
			echo "<td scope=\"col\">".$user->created."</td>";
			echo "<td scope=\"col\">".$user->bio."</td>";
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
			<button type="submit" action="post" name="delete_user" class="btn btn-danger">Delete</button>
			
		</div>
		</div>
	</div>
	</div></form></div></div>

<?php require_once '../../footer.php'; ?>
