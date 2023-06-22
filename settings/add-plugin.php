<?php

require_once "../inc/common.php";
require_once "../inc/header.php";
require_once "../Classes/class-plugin-git.php";

$p = new PluginRepo();
?>

<h2>Add New Plugin</h2>
<br>

<?php
    if ($p) {
        echo "
        Welcome to our lively plugins hub, where creativity takes center stage.<br>
        We've got two fantastic plugins to kick things off (one practical, one for a playful twist).<br>
        Join us on this exciting journey and unlock new possibilities for your website!<br><br>";   
        $p->do_list();
    } else {
        echo "Oops! Could not find plugins list. This is an upstream error, which means there is nothing wrong<br>
        on your panel, it just means we can't check the plugins information webpage for some reason.<br>
        Nothing to worry about! Try again later!";
    }
    require_once "../inc/footer.php";

?>

<!-- Remove when page is finished -->
<script>
const modal = bsModal("Important", "This is a work in progress.<br><br>Please do not expect anything to work properly on this page",
"<div class=\"btn btn-primary\" onClick=\"$('#'+modal).modal('hide')\">Ok</div>", size = null, static = true, show = true, closebutton = true);
</script>