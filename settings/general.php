<?php
require_once "../inc/common.php";
require_once "../inc/header.php";

$canEdit = current_user_can(PERMISSION_MANAGE_USERS);
function _ce($can){
    echo ($can) ? "" : "disabled";
}

?>
<h4>General Settings</h4>
<br>
<h6>Debug Mode</h6>
<div class="form-group form-check">
    <input type="checkbox" class="form-check-input" id="debug_mode" <?php _ce($canEdit) ?>>
    <label class="form-check-label" for="debug_mode">Enable Debug Mode (Developers Only)</label>
</div>


<?php $a = []; Hook::run(HOOKTYPE_GENERAL_SETTINGS, $a); ?>
<br><br>
<div class="btn btn-primary">Save</div>
<?php
require_once "../inc/footer.php";