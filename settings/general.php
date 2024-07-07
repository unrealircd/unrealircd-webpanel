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


    if (!empty($_FILES['customFile']))
    {
        $cwd = getcwd();
        $a = split($cwd,'/');
        $a[sizeof($a) - 1] = NULL;
        $cwd = glue($a,'/');
        $target_dir = "$cwd/img/";
        $target_file = "/$target_dir/wallpaper.jpg";
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
        $check = getimagesize($_FILES["customFile"]["tmp_name"]);
        $errs = [];

        if($check == false)
        {
            $errs[] = "File is not an image.";
            $uploadOk = 0;
        }
        else if ($_FILES["customFile"]["size"] > 500000) {
            $errs[] = "File is too large.";
            $uploadOk = 0;
        }
        elseif($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
        && $imageFileType != "gif" )
        {
            $errs[] = "Only JPG, JPEG, PNG & GIF files are allowed.";
            $uploadOk = 0;
        }
        if ($uploadOk != 0) {
            if (file_exists($target_file))
                unlink($target_file);
            move_uploaded_file($_FILES["customFile"]["tmp_name"], $target_file);
            Message::Success("Updloaded file: $target_file");
        }
        else Message::Fail($errs);
    }

    write_config();
    unset($_POST['debug'], $_POST['submit'], $_POST['hibp']);
    Hook::run(HOOKTYPE_GENERAL_SETTINGS_POST, $_POST);
}

do_log("\$_POST", $_POST);
do_log("\$_FILES", $_FILES);
?>
<h4>General Settings</h4>
<br>
<form method="post" enctype="multipart/form-data">
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
<script>
    const iframe =document.getElementById('frame');
    iframe.contentWindow.location.reload(true);
</script>

<style>
#wrap { 
    border: none;
    padding: 0;
margin:0;}
#frame { transform:none; zoom: 0.8; -moz-transform: scale(0.8); -moz-transform-origin: 0 0; transform-origin: 3%; border:none;}
</style>
<div class="card mb-2" style="padding-left:20px;padding-right:20px;padding-top:5px;padding-bottom:10px;max-width:fit-content;max-height:fit-content">
    <h6>Overview Background Image</h6>
    <div class="" id="wrap">
        <label class="form-label" for="customFile">Upload an image</label>
        <input type="file" class="form-control" name="customFile" id="customFile" /> Might not show current image if caching is used. You'll notice the changes soon.

        <iframe width="100%" height="500vh" id="frame" style="pointer-events: none;position:relative;margin-top:10px" src="<?php echo get_config('base_url'); ?>">Loading Live Preview</iframe>
    </div>
</div>
<?php $a = []; Hook::run(HOOKTYPE_GENERAL_SETTINGS, $a); ?>
<br><br>
<button type="post" name="submit" class="btn btn-primary">Save</div>
</form>
<?php
require_once "../inc/footer.php";