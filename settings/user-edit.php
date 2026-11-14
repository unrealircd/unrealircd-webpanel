<?php

require_once "../inc/common.php";
require_once "../inc/header.php";
/* Note: do not do_log($_POST) here, it contains passwords */

$us = unreal_get_current_user();
$id = (isset($_GET['id']) && is_numeric($_GET['id'])) ? (int)$_GET['id'] : $us->id;
$edit_user = new PanelUser(NULL, $id);
if (!$edit_user->id)
{
    Message::Fail("Could not find that user");
    require_once "../inc/footer.php";
    die;
}
$can_edit_profile = (user_can($us, PERMISSION_MANAGE_USERS) || $edit_user->id == $us->id) ? true : false;
$caneditprofile = ($can_edit_profile) ? "" : "disabled";
$caneditpermissions = (user_can($us, PERMISSION_MANAGE_USERS)) ? true : false;
$can_edit = ($caneditpermissions) ? "" : "disabled";
$postbutton = (isset($_POST['update_user'])) ? true : false;
$roles_list = get_panel_user_roles_list();
$current_role = $edit_user->user_meta['role'] ?? NULL;

if ($postbutton && isset($_POST['user_role']) && $caneditpermissions)
{
    if (!is_string($_POST['user_role']) || !array_key_exists($_POST['user_role'], $roles_list))
    {
        /* Only existing roles can be given. This is also what happens when
         * the user has a role that no longer exists and the form is saved
         * without choosing one: keep the old (now powerless) role then.
         */
        if ($_POST['user_role'] !== $current_role)
            Message::Fail("Could not update the role: unknown role");
    }
    else if ($_POST['user_role'] !== $current_role)
    {
        $edit_user->add_meta("role", $_POST['user_role']);
        $edit_user->delete_meta("permissions");
        Message::Success("Updated the role of $edit_user->username");
    }
}

if ($postbutton && $can_edit_profile)
{
    $str = function($name) {
        return (isset($_POST[$name]) && is_string($_POST[$name]) && strlen($_POST[$name])) ? $_POST[$name] : false;
    };
    // Goes via core:
    $array['update_fname'] = $str('first_name');
    $array['update_lname'] = $str('last_name');
    $array['update_bio'] = $str('bio');
    $array['update_email'] = $str('email');
    $array['update_pass'] = $str('password');
    $array['update_pass_conf'] = $str('passwordconfirm');
    // Goes via meta:
    $session_timeout = $str('session_timeout');
    $session_timeout = ($session_timeout !== false && is_numeric($session_timeout)) ? (int)$session_timeout : 3600;
    if ($session_timeout < 60)
        $session_timeout = 60;

    if (!$array['update_pass'])
    {
        unset($array['update_pass']);
        unset($array['update_pass_conf']);
    }
    elseif ($array['update_pass'] === $array['update_pass_conf'])
    {
        $array['update_pass_conf'] = PanelUser::password_hash($array['update_pass_conf']);
        $edit_user->delete_meta("hibp");
        $edit_user->HIBP(sha1($array['update_pass']));
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
    $current_role = $edit_user->user_meta['role'] ?? NULL;
}
?>
<h4>Edit User: "<?php echo h($edit_user->username); ?>"</h4>
<br>
<form method="post" action="user-edit.php?id=<?php echo h(urlencode($edit_user->id)); ?>" autocomplete="off" enctype="multipart/form-data">

<div class="input-group mb-3">
    <div class="input-group-prepend">
        <span class="input-group-text" style="width: 175px;">Username</span>
    </div><input disabled type="text" class="form-control" name="username" id="username" placeholder="<?php echo h($edit_user->username); ?>">
</div>

<div class="input-group mb-3">
    <div class="input-group-prepend">
        <span class="input-group-text" style="width: 175px;">Role</span>
    </div><select name="user_role" <?php echo $can_edit; ?> class="custom-select" id="user_role">
                <?php
                    /* If the current role does not exist (anymore), show that,
                     * instead of silently selecting the first role (Super-Admin).
                     */
                    if (!array_key_exists((string)$current_role, $roles_list))
                        echo "<option value=\"".h($current_role)."\" selected=\"selected\">".h($current_role === NULL ? "(no role)" : $current_role." (unknown role)")."</option>";
                    foreach($roles_list as $s => $l)
                    {
                        $selected = ($s === $current_role) ? "selected=\"selected\"" : "";
                        echo "<option value=\"".h($s)."\" $selected>".h($s)."</option>";
                    }
                ?>
            </select>
</div>



<div class="input-group mb-3">
    <div class="input-group-prepend">
        <span class="input-group-text" style="width: 175px;">First Name</span>
    </div><input <?php echo $caneditprofile; ?> type="text" class="form-control" name="first_name" id="first_name" placeholder="<?php echo h($edit_user->first_name); ?>">
</div>


<div class="input-group mb-3">
    <div class="input-group-prepend">
        <span class="input-group-text" style="width: 175px;">Last Name</span>
    </div><input <?php echo $caneditprofile; ?> type="text" class="form-control" name="last_name" id="last_name" placeholder="<?php echo h($edit_user->last_name); ?>">
</div>


<div class="input-group mb-3">
    <div class="input-group-prepend">
        <span class="input-group-text" style="width: 175px;">Bio</span>
    </div><textarea <?php echo $caneditprofile; ?> class="form-control" name="bio" id="username"><?php echo h($edit_user->bio); ?></textarea>
</div>


<div class="input-group mb-3">
    <div class="input-group-prepend">
        <span class="input-group-text" style="width: 175px;">Email</span>
    </div><input <?php echo $caneditprofile; ?> type="text" class="form-control" name="email" id="email" autocomplete="off" value="<?php echo h($edit_user->email); ?>">
</div>

<div class="input-group mb-3">
    <div class="input-group-prepend">
        <span class="input-group-text" style="width: 175px;">Session timeout</span>
    </div><input <?php echo $caneditprofile; ?> type="text" class="form-control" name="session_timeout" id="session_timeout" autocomplete="off" value="<?php echo h($edit_user->user_meta['session_timeout'] ?? 3600); ?>">
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
