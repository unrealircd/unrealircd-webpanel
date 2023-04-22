<?php

require_once "../common.php";
require_once "../header.php";

if (!current_user_can(PERMISSION_MANAGE_USERS))
{
    echo "<h4>Access denied</h4>";
    die();
}
$permissions = get_panel_user_permission_list();
$list = get_panel_user_roles_list();

/**
 * Add a new role
 */
$errors = [];
$success = [];



if (isset($_POST['add_role_name']) && $role_name = $_POST['add_role_name'])
{
    foreach ($list as $name => $u) // don't add it if it already exists
    {
        if (!strcmp(to_slug($name),to_slug($role_name)))
        {
            $errors[] = "Cannot create role \"$role_name\": A role with that name already exists.";
            break;
        }
    }
    if (empty($errors)) // so far so good
    {
        $msg = "Added user role \"$role_name\"";
        $permissions = [];
        if (isset($_POST['use_dup_role']) && $dup = $_POST['dup_role']) // if they're duplicating a role
        {
            $permissions = $list[$dup];
            $msg .= ", a duplicate of \"$dup\"";
        }
        $settings = DbSettings::get();
        $clean_perms = [];
            foreach($permissions as $k => $v)
                $clean_perms[] = $v;

        $settings['user_roles'][$role_name] = $clean_perms;
        DbSettings::set('user_roles', $settings['user_roles']);
        $success[] = $msg;
        $list = get_panel_user_roles_list(); // refresh
        
    }
}

elseif (isset($_POST['del_role_name']) && $role_name = $_POST['del_role_name'])
{
    $found = 0;
    foreach ($list as $name => $u) // don't add it if it already exists
    {
        if (!strcmp(to_slug($name),to_slug($role_name)))
        {
            $found = 1;
            break;
        }
    }
    if ($found) // so far so good
    {
        $settings = DbSettings::get();
        unset($settings['user_roles'][$role_name]);
        DbSettings::set('user_roles', $settings['user_roles']);
        $success[] = "Successfully deleted role \"$role_name\"";
        $list = get_panel_user_roles_list(); // refresh
    }
    else
        $errors[] = "Could not delete role \"$role_name\": Role does not exist.";
}
?>


<div class="container-xxl row justify-content-between">

<div class="col">
    <h4>User Role Editor</h4>
    <?php if (!empty($errors)) Message::Fail($errors); if (!empty($success)) Message::Success($success); ?>
    Roles are user categories where each has it's own set of permissions.<br>
    Here, you can easily add and edit User Roles to ensure that your team has the appropriate access and permissions they need.<br>
    Once you've created a role, you can assign it to a user on your panel, and they will have the permissions assigned to their role.<br><br>
    <div class="font-italic">Some roles are built-in and cannot be deleted or modified, specifically "<code>Super Admin</code>" and "<code>Read Only</code>"</div><br><br>
    Click a role name to view role permissions.
</div>
<div class="col" id="addnew_collapse">
<form method="post">
    <div class="card card-body" style="max-width:550px">
        <h5>Create New Role</h5>
        <div class="font-italic mb-3">You must create a new role before you can add permissions to it.</div>
        <div class="row input-group ml-0 mb-2">
            <div class="input-group-prepend">
                <span class="input-group-text" style="width:150px">New Role Name</span>
            </div>
            <input id="add_role_name" name="add_role_name" class="form-control" style="min-width:100px;max-width:450px" type="text">
            

        </div>
        <div class="input-group">
            <div class="input-group-prepend">
                <div style="width:150px" class="input-group-text">
                    <input id="use_dup_role" name="use_dup_role" type="checkbox" class="mr-2">Duplicate Role
                </div>
            </div>
            <select name="dup_role" disabled class="custom-select" id="dup_role" style="min-width:100px;max-width:450px">
                <option value="0" selected>None</option>
                <?php
                    foreach($list as $s => $l)
                        echo "<option value=\"$s\">$s</option>";
                ?>
            </select>
        </div>
        <div class="mt-2 text-right">
            <button type="submit" disabled id="role_submit" style="background-color:darkslateblue;color:white" class="btn btn-primary">Create Role</button>
        </div>
        
</form>
    </div>
</div>
</div>
<style>

#permlist #roles_accord .card .card-header .btn-header-link:after {
  content: "\f106";
  font-family: 'Font Awesome 5 Free';
  font-weight: 900;
  float: right;
}

#permlist #roles_accord .card .card-header .btn-header-link.collapsed:after {
  content: "\f107";
}

</style>


<script>
    const add_role_name = document.getElementById("add_role_name");
    const use_dup = document.getElementById("use_dup_role");
    const dup_role = document.getElementById("dup_role");
    const role_submit = document.getElementById("role_submit");

    use_dup.addEventListener('click', e => {
        if (use_dup.checked) {
            dup_role.disabled = false;
        } else {
            dup_role.value = "0";
            dup_role.disabled = true;
        }
    });

    add_role_name.addEventListener('input', e => {
        if (!add_role_name.value.length)
            role_submit.disabled = true;
        else
            role_submit.disabled = false;
    });
</script>
<?php

generate_role_list($list);


require_once "../footer.php";