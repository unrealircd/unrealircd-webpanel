<?php
require_once "../inc/common.php";
require_once "../inc/header.php";

$canEdit = current_user_can(PERMISSION_MANAGE_USERS);
function _ce($can){
    echo ($can) ? "" : "disabled";
}
if (isset($_POST['submit']) && $canEdit)
{
    $dbug = (isset($config['debug']) && $config['debug']) ? true : false;
    $config['debug'] = (isset($_POST['debug_mode'])) ? true : false;
    if ($config['debug'] != $dbug) // we just toggled
        Message::Info("Debug Mode is now ".(($config['debug']) ? "enabled" : "disabled"));
    write_config();
    unset($_POST['debug'], $_POST['submit']);
    Hook::run(HOOKTYPE_GENERAL_SETTINGS_POST, $_POST);
}

do_log("\$_POST", $_POST);
?>
<h4>General Settings</h4>
<br>
<form method="post">
<div class="card" style="padding-left:20px;padding-right:20px;padding-top:5px;padding-bottom:10px;max-width:fit-content">
    <h6>Debug Mode</h6>
    <div class="custom-control custom-switch">
        <input name="debug_mode" type="checkbox" class="custom-control-input" id="debug_mode" <?php _ce($canEdit); echo ($config['debug'] == true) ? " checked" : ""; ?>>
        <label class="custom-control-label" for="debug_mode">Enable Debug Mode (Developers Only)</label>
    </div>
    <i>Enabling this will likely make your webpanel more difficult to use</i>
</div>

<?php $a = []; Hook::run(HOOKTYPE_GENERAL_SETTINGS, $a); ?>
<br><br>
<button type="post" name="submit" class="btn btn-primary">Save</div>
</form>
<?php
require_once "../inc/footer.php";