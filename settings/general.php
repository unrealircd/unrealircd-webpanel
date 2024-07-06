<?php
require_once "../inc/common.php";
require_once "../inc/header.php";

$canEdit = current_user_can(PERMISSION_MANAGE_USERS);
function _ce($can){
    echo ($can) ? "" : "disabled";
}
if (isset($_POST['submit']) && $canEdit)
{
    $hibp = (!isset($config['hibp']) || $config['hibp']) ? true : false;
    $config['hibp'] = isset($_POST['hibp']) ? true : false;
    if ($config['hibp'] != $hibp) // we just toggled
        Message::Info("Checking passwords against data breaches is now is now ".(($config['hibp']) ? "enabled" : "disabled"));

    $dbug = (isset($config['debug']) && $config['debug']) ? true : false;
    $config['debug'] = isset($_POST['debug_mode']) ? true : false;
    if ($config['debug'] != $dbug) // we just toggled
        Message::Info("Debug Mode is now ".(($config['debug']) ? "enabled" : "disabled"));

    write_config();
    unset($_POST['debug'], $_POST['submit'], $_POST['hibp']);
    Hook::run(HOOKTYPE_GENERAL_SETTINGS_POST, $_POST);
}

do_log("\$_POST", $_POST);
?>
<h4>General Settings</h4>
<br>
<form method="post">
<div class="card m-1" style="padding-left:20px;padding-right:20px;padding-top:5px;padding-bottom:10px;max-width:fit-content">
    <h6>Password Data Leak Checks</h6>
    <div class="custom-control custom-switch">
        <input name="hibp" type="checkbox" class="custom-control-input" id="hibp" <?php _ce($canEdit); echo (!isset($config['hibp']) || $config['hibp'] == true) ? " checked" : ""; ?>>
        <label class="custom-control-label" for="hibp">Checks a users password on login against known data leaks (<a href="https://haveibeenpwned.com">Have I Been Pwned</a>)</label>
    </div>
    <i>This check is made everytime someone successfully logs into the webpanel or when they update their password.</i>
</div>
<div class="card m-1" style="padding-left:20px;padding-right:20px;padding-top:5px;padding-bottom:10px;max-width:fit-content">
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