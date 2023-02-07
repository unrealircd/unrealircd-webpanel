<?php

require_once "../common.php";
require_once "../header.php";

do_log($_POST, $_GET, $_FILES);

$us = unreal_get_current_user();
$id = (isset($_GET['id'])) ? $_GET['id'] : $us->id;
$edit_user = new PanelUser(NULL, $id);
$can_edit = (current_user_can(PERMISSION_MANAGE_USERS) || $edit_user->id == $us->id) ? "" : "disabled";

?>
<h4>Edit User: "<?php echo $edit_user->username; ?>"</h4>
<br><br>
<form method="post" action="user-edit.php?id=<?php echo $edit_user->id; ?>" autocomplete="off" enctype="multipart/form-data">

<div class="input-group mb-3">
    <div class="input-group-prepend">
        <span class="input-group-text" style="width: 100px;">@</span>
    </div><input disabled type="text" class="form-control" name="username" id="username" placeholder="<?php echo $edit_user->username; ?>">
</div>

<div class="input-group mb-3">
    <div class="input-group-prepend">
        <span class="input-group-text" style="width: 100px;">First Name</span>
    </div><input <?php echo $can_edit; ?> type="text" class="form-control" name="first_name" id="first_name" placeholder="<?php echo $edit_user->first_name; ?>">
</div>


<div class="input-group mb-3">
    <div class="input-group-prepend">
        <span class="input-group-text" style="width: 100px;">Last Name</span>
    </div><input <?php echo $can_edit; ?> type="text" class="form-control" name="last_name" id="last_name" placeholder="<?php echo $edit_user->last_name; ?>">
</div>


<div class="input-group mb-3">
    <div class="input-group-prepend">
        <span class="input-group-text" style="width: 100px;">Bio</span>
    </div><textarea <?php echo $can_edit; ?> class="form-control" name="bio" id="username"><?php echo $edit_user->bio; ?></textarea>
</div>


<div class="input-group mb-3">
    <div class="input-group-prepend">
        <span class="input-group-text" style="width: 100px;">Email</span>
    </div><input <?php echo $can_edit; ?> type="text" class="form-control" name="email" id="email" autocomplete="off">
</div>

<div class="input-group mb-3">
    <div class="input-group-prepend">
        <span class="input-group-text" style="width: 100px;">Password</span>
    </div><input <?php echo $can_edit; ?> type="password" class="form-control" name="password" id="password" autocomplete="off">
    <div class="input-group-append">
		<br><button type="submit" name="update_pass" class="btn btn-primary">Update Password</button>
	</div>
</div>

<br>
<button type="submit" name="update_user" class="btn btn-primary">Update User</button><br><p>
<h6>Note: This button will not update your password.<br>
Please use the 'Update Password' button on the Password field for this instead.</h6></p>
</form>