<?php

require_once "../common.php";
require_once "../header.php";
do_log($_POST);

$us = unreal_get_current_user();
$id = (isset($_GET['id'])) ? $_GET['id'] : $us->id;
$edit_user = new PanelUser(NULL, $id);
$can_edit_profile = (user_can($us, PERMISSION_MANAGE_USERS) || $edit_user->id == $us->id) ? true : false;
$caneditpermissions = (user_can($us, PERMISSION_MANAGE_USERS)) ? true : false;
$can_edit = ($caneditpermissions) ? "" : "disabled";
$postbutton = (isset($_POST['update_user'])) ? true : false;
$permissions = (isset($_POST['permissions'])) ? $_POST['permissions'] : [];
$edit_perms = (isset($edit_user->user_meta['permissions'])) ? unserialize($edit_user->user_meta['permissions']) : [];

/* Check if they can edit their permissions and if the permissions have indeed been changed */
if ($postbutton && is_array($permissions) && $caneditpermissions
        && $permissions != $edit_perms)
{
    foreach ($permissions as $p)
        if (!in_array($p, $edit_perms))
            $edit_user->add_permission($p);

    foreach($edit_perms as $p)
        if (!in_array($p, $permissions))
            $edit_user->delete_permission($p);

    Message::Success("Permissions for <strong>$edit_user->username</strong> have been updated");
}

if ($postbutton && $can_edit_profile)
{
    $array['update_fname'] = (isset($_POST['first_name']) && strlen($_POST['first_name'])) ? $_POST['first_name'] : false;
    $array['update_lname'] = (isset($_POST['last_name']) && strlen($_POST['last_name'])) ? $_POST['last_name'] : false;
    $array['update_bio'] = (isset($_POST['bio']) && strlen($_POST['bio'])) ? $_POST['bio'] : false;
    $array['update_email'] = (isset($_POST['email']) && strlen($_POST['email'])) ? $_POST['email'] : false;
    $array['update_pass'] = (isset($_POST['password']) && strlen($_POST['password'])) ? $_POST['password'] : false;
    $array['update_pass_conf'] = (isset($_POST['passwordconfirm']) && strlen($_POST['passwordconfirm'])) ? $_POST['passwordconfirm'] : false;

    if (!$array['update_pass'])
    {
        unset($array['update_pass']);
        unset($array['update_pass_conf']);
    }
    elseif ($array['update_pass'] == $array['update_pass_conf'])
    {
        $array['update_pass_conf'] = password_hash($array['update_pass_conf'], PASSWORD_ARGON2ID);
        unset($array['update_pass']);
    }
    else
    {
        Message::Fail("Could not update password: Passwords did not match");
        unset($array['update_pass']);
        unset($array['update_pass_conf']);
    }
    $edit_user->update_core_info($array);
    $edit_user = new PanelUser($edit_user->username);
}
?>
<h4>Edit User: "<?php echo $edit_user->username; ?>"</h4>
<br>
<form method="post" action="user-edit.php?id=<?php echo $edit_user->id; ?>" autocomplete="off" enctype="multipart/form-data">
<?php if ($can_edit_profile) { ?>
<a class="btn btn-<?php echo (user_can($us, PERMISSION_MANAGE_USERS)) ? "danger" : "info"; ?>" data-toggle="collapse" href="#collapseExample" role="button" aria-expanded="false" aria-controls="collapseExample">
<?php echo (user_can($us, PERMISSION_MANAGE_USERS)) ? "Edit" : "View"; ?> Permissions
</a>
<div class="collapse" id="collapseExample">
    <br>
  <div class="card card-body">
    <h6>Here are all the things <?php echo $edit_user->username; ?> can do</h6>
    <?php generate_panel_user_permission_table($edit_user); ?>
  </div>
</div>
<?php } ?>
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
    </div><input <?php echo $can_edit; ?> type="text" class="form-control" name="email" id="email" autocomplete="off" value="<?php echo $edit_user->email; ?>">
</div>

<div class="input-group mb-3">
    <div class="input-group-prepend">
        <span class="input-group-text" style="width: 150px;">New Password</span>
    </div><input <?php echo $can_edit; ?> type="password" class="form-control" name="password" id="password" autocomplete="off">
</div><div class="input-group mb-3">
    <div class="input-group-prepend">
        <span class="input-group-text" style="width: 150px;">Confirm Password</span>
    </div><input <?php echo $can_edit; ?> type="password" class="form-control" name="passwordconfirm" id="passwordconfirm" autocomplete="off">
</div>

<br>
<button type="submit" name="update_user" class="btn btn-primary">Save Changes</button><br>
</form>
<?php
require_once "../footer.php";
