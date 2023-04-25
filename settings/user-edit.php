<?php

require_once "../inc/common.php";
require_once "../inc/header.php";
do_log($_POST);

$us = unreal_get_current_user();
$id = (isset($_GET['id'])) ? $_GET['id'] : $us->id;
$edit_user = new PanelUser(NULL, $id);
$can_edit_profile = (user_can($us, PERMISSION_MANAGE_USERS) || $edit_user->id == $us->id) ? true : false;
$caneditprofile = ($can_edit_profile) ? "" : "disabled";
$caneditpermissions = (user_can($us, PERMISSION_MANAGE_USERS)) ? true : false;
$can_edit = ($caneditpermissions) ? "" : "disabled";
$postbutton = (isset($_POST['update_user'])) ? true : false;
$roles_list = get_panel_user_roles_list();

if ($postbutton && isset($_POST['user_role']) && $caneditpermissions)
{
    if ($_POST['user_role'] != $edit_user->user_meta['role'])
    {
        $edit_user->add_meta("role", $_POST['user_role']);
        Message::Success("Updated the role of $edit_user->username");
    }
}

if ($postbutton && $can_edit_profile)
{
    // Goes via core:
    $array['update_fname'] = (isset($_POST['first_name']) && strlen($_POST['first_name'])) ? $_POST['first_name'] : false;
    $array['update_lname'] = (isset($_POST['last_name']) && strlen($_POST['last_name'])) ? $_POST['last_name'] : false;
    $array['update_bio'] = (isset($_POST['bio']) && strlen($_POST['bio'])) ? $_POST['bio'] : false;
    $array['update_email'] = (isset($_POST['email']) && strlen($_POST['email'])) ? $_POST['email'] : false;
    $array['update_pass'] = (isset($_POST['password']) && strlen($_POST['password'])) ? $_POST['password'] : false;
    $array['update_pass_conf'] = (isset($_POST['passwordconfirm']) && strlen($_POST['passwordconfirm'])) ? $_POST['passwordconfirm'] : false;
    // Goes via meta:
    $session_timeout = (isset($_POST['session_timeout']) && strlen($_POST['session_timeout'])) ? $_POST['session_timeout'] : 3600;

    if (!$array['update_pass'])
    {
        unset($array['update_pass']);
        unset($array['update_pass_conf']);
    }
    elseif ($array['update_pass'] == $array['update_pass_conf'])
    {
        $array['update_pass_conf'] = PanelUser::password_hash($array['update_pass_conf']);
        unset($array['update_pass']);
    }
    else
    {
        Message::Fail("Could not update password: Passwords did not match");
        unset($array['update_pass']);
        unset($array['update_pass_conf']);
    }
    $edit_user->update_core_info($array);
    $edit_user->add_meta("session_timeout", $session_timeout);
    $edit_user = new PanelUser($edit_user->username);
}
?>
<h4>Edit User: "<?php echo $edit_user->username; ?>"</h4>
<br>
<form method="post" action="user-edit.php?id=<?php echo $edit_user->id; ?>" autocomplete="off" enctype="multipart/form-data">

<div class="input-group mb-3">
    <div class="input-group-prepend">
        <span class="input-group-text" style="width: 175px;">Username</span>
    </div><input disabled type="text" class="form-control" name="username" id="username" placeholder="<?php echo $edit_user->username; ?>">
</div>

<div class="input-group mb-3">
    <div class="input-group-prepend">
        <span class="input-group-text" style="width: 175px;">Role</span>
    </div><select name="user_role" <?php echo $can_edit; ?> class="custom-select" id="user_role">
                <?php
                    foreach($roles_list as $s => $l)
                    {
                        $selected = ($s == $edit_user->user_meta['role']) ? "selected=\"selected\"" : "";
                        echo "<option value=\"$s\" $selected>$s</option>";
                    }
                ?>
            </select>
</div>



<div class="input-group mb-3">
    <div class="input-group-prepend">
        <span class="input-group-text" style="width: 175px;">First Name</span>
    </div><input <?php echo $caneditprofile; ?> type="text" class="form-control" name="first_name" id="first_name" placeholder="<?php echo $edit_user->first_name; ?>">
</div>


<div class="input-group mb-3">
    <div class="input-group-prepend">
        <span class="input-group-text" style="width: 175px;">Last Name</span>
    </div><input <?php echo $caneditprofile; ?> type="text" class="form-control" name="last_name" id="last_name" placeholder="<?php echo $edit_user->last_name; ?>">
</div>


<div class="input-group mb-3">
    <div class="input-group-prepend">
        <span class="input-group-text" style="width: 175px;">Bio</span>
    </div><textarea <?php echo $caneditprofile; ?> class="form-control" name="bio" id="username"><?php echo $edit_user->bio; ?></textarea>
</div>


<div class="input-group mb-3">
    <div class="input-group-prepend">
        <span class="input-group-text" style="width: 175px;">Email</span>
    </div><input <?php echo $caneditprofile; ?> type="text" class="form-control" name="email" id="email" autocomplete="off" value="<?php echo $edit_user->email; ?>">
</div>

<div class="input-group mb-3">
    <div class="input-group-prepend">
        <span class="input-group-text" style="width: 175px;">Session timeout</span>
    </div><input <?php echo $caneditprofile; ?> type="text" class="form-control" name="session_timeout" id="session_timeout" autocomplete="off" value="<?php echo $edit_user->user_meta['session_timeout'] ?? 3600; ?>">
</div>

<div class="input-group mb-3">
    <div class="input-group-prepend">
        <span class="input-group-text" style="width: 175px;">New Password</span>
    </div><input <?php echo $caneditprofile; ?> type="password" class="form-control" name="password" id="password" autocomplete="off">
</div><div class="input-group mb-3">
    <div class="input-group-prepend">
        <span class="input-group-text" style="width: 175px;">Confirm Password</span>
    </div><input <?php echo $caneditprofile; ?> type="password" class="form-control" name="passwordconfirm" id="passwordconfirm" autocomplete="off">
</div>

<br>
<button type="submit" name="update_user" class="btn btn-primary">Save Changes</button><br>
</form>
<?php
require_once "../inc/footer.php";
