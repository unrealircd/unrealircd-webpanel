<?php
$conn = NULL;

require_once "../../common.php";
require_once "../../header.php";
require_once "SQL/sql.php";
require_once "SQL/user.php";
do_log($_POST);

if (isset($_POST))
{
    $p = $_POST;
    
}
var_dump($_POST);

$conn = sqlnew();
$result = $conn->query("SELECT user_id FROM " . SQL_PREFIX . "users");

if (!$result) // impossible
{
    die("Something went wrong.");
}

$userlist = [];
while($row =  $result->fetch())
{
    $userlist[] = new SQLA_User(NULL, $row['user_id']);
}
?>
<h4>Panel Access Overview</h4>

Click on a username to view more information.
<br><br>
<div id="Users">
	
            <form method="post">
<p><button type="button" class="btn btn-primary" data-toggle="modal" data-target="#myModal">
			Add New User
	</button></p></table>
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
		
                <div class="align_label">Username: </div> <input class="curvy" type="text" id="user_add" name="user_add"><br>
                <div class="align_label">Password: </div> <input class="curvy" type="password" name="password" id="password"><br>
                <div class="align_label">Confirm: </div> <input class="curvy" type="password" name="confirm_password" id="confirm_password"><br>
                <div class="align_label">First Name: </div> <input class="curvy" type="text" name="add_first_name" id="add_first_name"><br>
                <div class="align_label">Last Name: </div> <input class="curvy" type="text" name="add_last_name" id="add_last_name"><br>
                <div class="align_label">Info/Bio: </div> <input class="curvy" type="text" name="add_bio" id="add_bio"><br>
        </div>
						
		<div class="modal-footer">
			<button id="CloseButton" type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
			<button type="submit" class="btn btn-danger">Add User</button>
			
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
	</thead>
	<tbody>
	<?php
		foreach($userlist as $user)
		{
			
			echo "<td scope=\"col\"><input type=\"checkbox\" value='" .base64_encode($user->id) . "' name=\"sqluser[]\"></td>";
			echo "<td scope=\"col\">".$user->username."</td>";
			echo "<td scope=\"col\">".$user->first_name."</td>";
			echo "<td scope=\"col\">".$user->last_name."</td>";
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
