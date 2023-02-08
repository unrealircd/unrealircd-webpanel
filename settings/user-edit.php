<?php

require_once "../common.php";
require_once "../header.php";
do_log($_POST, $_GET, $_FILES);

$us = unreal_get_current_user();
$id = (isset($_GET['id'])) ? $_GET['id'] : $us->id;
$edit_user = new PanelUser(NULL, $id);
$can_edit = (user_can($us, PERMISSION_MANAGE_USERS) || $edit_user->id == $us->id) ? "" : "disabled";

?>
<h4>Edit User: "<?php echo $edit_user->username; ?>"</h4>
<br><br>
<form method="post" action="user-edit.php?id=<?php echo $edit_user->id; ?>" autocomplete="off" enctype="multipart/form-data">
<a class="btn btn-<?php echo (user_can($us, PERMISSION_MANAGE_USERS)) ? "danger" : "primary"; ?>" data-toggle="collapse" href="#collapseExample" role="button" aria-expanded="false" aria-controls="collapseExample">
<?php echo (user_can($us, PERMISSION_MANAGE_USERS)) ? "Edit" : "View"; ?> Permissions
</a>
<div class="collapse" id="collapseExample">
    <br>
  <div class="card card-body">
    <h6>Here are all the things <?php echo $edit_user->username; ?> can do</h6>
    <?php generate_panel_user_permission_table($edit_user); ?>
  </div>
</div>
<br><br>
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
        <span class="input-group-text" style="width: 150px;">New Password</span>
    </div><input <?php echo $can_edit; ?> type="password" class="form-control" name="password" id="password" autocomplete="off">
</div><div class="input-group mb-3">
    <div class="input-group-prepend">
        <span class="input-group-text" style="width: 150px;">Confirm Password</span>
    </div><input <?php echo $can_edit; ?> type="password" class="form-control" name="password" id="password" autocomplete="off">
</div>

<br>
<button type="submit" name="update_user" class="btn btn-primary">Save Changes</button><br>
</form>