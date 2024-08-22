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


    if (!empty($_FILES['customFile']['tmp_name']))
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
<style>
.color-circle.selected {
    border: 2px solid #fff; /* Optional: Add a border to highlight selected option */
}

.color-option input[type="radio"]:checked + .color-circle {
    border: 2px solid #fff; /* Optional: Add a border to highlight selected option */
}

.color-option .color-circle {
    width: 30px; /* Adjust size as needed */
    height: 30px; /* Adjust size as needed */
    border-radius: 50%; /* Ensure it's circular */
}

@media (max-width: 600px) {
    .color-option {
        flex-basis: 50%; /* Two items per row on smaller screens */
    }
}

@media (max-width: 400px) {
    .color-option {
        flex-basis: 100%; /* One item per row on very small screens */
    }
}
</style>

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
        <input name="debug_mode" type="checkbox" class="custom-control-input" id="debug_mode" <?php _ce($canEdit); echo (get_config('debug') == true) ? " checked" : ""; ?>>
        <label class="custom-control-label" for="debug_mode">Enable Debug Mode (Developers Only)</label>
    </div>
    <i>Enabling this will likely make your webpanel more difficult to use</i>
</div>
<div class="card m-1" style="padding-left:20px;padding-right:20px;padding-top:5px;padding-bottom:10px;max-width:fit-content">
    <h6>Themes</h6>
    <div class="d-flex" id="color-options">
        <label class="color-option">
            <input type="radio" name="color" value="gradient1" class="color-input" checked>
            <div class="color-circle selected" style="background: linear-gradient(to bottom, #4a0000, #6b0000);"></div>
        </label>
        <label class="color-option">
            <input type="radio" name="color" value="gradient2" class="color-input">
            <div class="color-circle" style="background: linear-gradient(to top right, #3c4858, #1f2833);"></div>
        </label>
        <label class="color-option">
            <input type="radio" name="color" value="gradient3" class="color-input">
            <div class="color-circle" style="background: radial-gradient(circle, #3f0f3f, #1a0643);"></div>
        </label>
        <label class="color-option">
            <input type="radio" name="color" value="gradient4" class="color-input">
            <div class="color-circle" style="background: linear-gradient(to right, #443d6e, #191b3f);"></div>
        </label>
        <label class="color-option">
            <input type="radio" name="color" value="gradient5" class="color-input">
            <div class="color-circle" style="background: linear-gradient(to bottom, #1a1a1a, #333333);"></div>
        </label>
        <label class="color-option">
            <input type="radio" name="color" value="gradient6" class="color-input">
            <div class="color-circle" style="background: linear-gradient(135deg, #4d194d, #1a0643);"></div>
        </label>
        <label class="color-option">
            <input type="radio" name="color" value="gradient7" class="color-input">
            <div class="color-circle" style="background: linear-gradient(to bottom right, #1a1a1a, #333333);"></div>
        </label>
        <label class="color-option">
            <input type="radio" name="color" value="gradient8" class="color-input">
            <div class="color-circle" style="background: linear-gradient(to bottom right, #331a33, #191b3f);"></div>
        </label>
        <!-- Extend with additional gradients -->
        <label class="color-option">
            <input type="radio" name="color" value="gradient9" class="color-input">
            <div class="color-circle" style="background: linear-gradient(to bottom, #400000, #800000);"></div>
        </label>
        <label class="color-option">
            <input type="radio" name="color" value="gradient10" class="color-input">
            <div class="color-circle" style="background: linear-gradient(to top right, #363636, #1f1f1f);"></div>
        </label>
        <label class="color-option">
            <input type="radio" name="color" value="gradient11" class="color-input">
            <div class="color-circle" style="background: radial-gradient(circle, #730073, #400040);"></div>
        </label>
        <label class="color-option">
            <input type="radio" name="color" value="gradient12" class="color-input">
            <div class="color-circle" style="background: linear-gradient(to right, #4c4c99, #1a1a66);"></div>
        </label>
        <label class="color-option">
            <input type="radio" name="color" value="gradient13" class="color-input">
            <div class="color-circle" style="background: linear-gradient(to bottom, #2e2e2e, #4d4d4d);"></div>
        </label>
        <label class="color-option">
            <input type="radio" name="color" value="gradient14" class="color-input">
            <div class="color-circle" style="background: linear-gradient(135deg, #732673, #400040);"></div>
        </label>
        <label class="color-option">
            <input type="radio" name="color" value="gradient15" class="color-input">
            <div class="color-circle" style="background: linear-gradient(to bottom right, #2e2e2e, #4d4d4d);"></div>
        </label>
        <label class="color-option">
            <input type="radio" name="color" value="gradient16" class="color-input">
            <div class="color-circle" style="background: linear-gradient(to bottom right, #662266, #330033);"></div>
        </label>
        <label class="color-option">
            <input type="radio" name="color" value="gradient17" class="color-input">
            <div class="color-circle" style="background: linear-gradient(to bottom right, #993399, #660066);"></div>
        </label>
        <label class="color-option">
            <input type="radio" name="color" value="gradient18" class="color-input">
            <div class="color-circle" style="background: linear-gradient(to bottom right, #4c4c4c, #666666);"></div>
        </label>
        <label class="color-option">
            <input type="radio" name="color" value="gradient19" class="color-input">
            <div class="color-circle" style="background: linear-gradient(to bottom right, #660066, #330033);"></div>
        </label>
        <label class="color-option">
            <input type="radio" name="color" value="gradient20" class="color-input">
            <div class="color-circle" style="background: linear-gradient(to bottom right, #993399, #660066);"></div>
        </label>
        <label class="color-option">
            <input type="radio" name="color" value="gradient21" class="color-input">
            <div class="color-circle" style="background: linear-gradient(to bottom right, #4c4c4c, #666666);"></div>
        </label>
        <label class="color-option">
            <input type="radio" name="color" value="gradient22" class="color-input">
            <div class="color-circle" style="background: linear-gradient(to bottom right, #4c4c99, #191966);"></div>
        </label>
        <label class="color-option">
            <input type="radio" name="color" value="gradient23" class="color-input">
            <div class="color-circle" style="background: linear-gradient(to bottom right, #4c4c99, #191966);"></div>
        </label>
        <label class="color-option">
            <input type="radio" name="color" value="gradient24" class="color-input">
            <div class="color-circle" style="background: linear-gradient(to bottom right, #330000, #191966);"></div>
        </label>
        <label class="color-option">
            <input type="radio" name="color" value="gradient25" class="color-input">
            <div class="color-circle" style="background: linear-gradient(to bottom right, #4c4c99, #191966);"></div>
        </label>
        <label class="color-option">
            <input type="radio" name="color" value="gradient26" class="color-input">
            <div class="color-circle" style="background: linear-gradient(to bottom right, #330000, #191966);"></div>
        </label>
        <label class="color-option">
            <input type="radio" name="color" value="gradient27" class="color-input">
            <div class="color-circle" style="background: linear-gradient(to bottom right, #ffccff, #ff66ff);"></div>
        </label>
        <label class="color-option">
            <input type="radio" name="color" value="gradient28" class="color-input">
            <div class="color-circle" style="background: linear-gradient(to bottom right, #ff66ff, #ff99ff);"></div>
        </label>
        <label class="color-option">
            <input type="radio" name="color" value="gradient29" class="color-input">
            <div class="color-circle" style="background: linear-gradient(to bottom right, #ff66ff, #ffccff);"></div>
        </label>
        <label class="color-option">
            <input type="radio" name="color" value="gradient30" class="color-input">
            <div class="color-circle" style="background: linear-gradient(to bottom right, #ffccff, #ff66ff);"></div>
        </label>
    </div>
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