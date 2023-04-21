<?php

require_once "../common.php";
require_once "../header.php";
do_log($_POST);

$permissions = get_panel_user_permission_list();
$list = get_panel_user_roles_list();
?>

<h4>User Role Editor</h4>

Here, you can easily edit user roles to ensure that your team has the appropriate access and permissions they need.<br>
Some roles are built-in and cannot be deleted or modified.<br><br>
Click a role name to view role permissions.<br><br>

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
<div class="row container">
<p>
  <button style="background-color:darkslateblue;color:white" class="btn mr-4" type="button" data-toggle="collapse" data-target="#addnew_collapse" aria-expanded="false" aria-controls="addnew_collapse">
    Create new User Role
  </button>
</p>
<div class="collapse" id="addnew_collapse">
    <div class="card card-body" style="max-width:550px">
        <div class="mb-3">Creating a new role:</div>
        <div class="row input-group ml-0">
            <div class="input-group-prepend">
                <span class="input-group-text">Role name</span>
            </div>
            <input class="form-control" style="max-width:450px" type="text">
            <div class="input-group-append">
                <button style="background-color:darkslateblue;color:white" class="btn btn-primary">Create role</button>
            </div>
        </div>
        <div class="mt-3 font-italic">Note: You must create a new role before you can add permissions to it.</div>
    </div>
</div></div>
<?php

generate_role_list($list);

require_once "../footer.php";