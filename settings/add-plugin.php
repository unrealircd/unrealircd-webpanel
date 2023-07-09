<?php

require_once "../inc/common.php";
require_once "../inc/header.php";
require_once "../Classes/class-plugin-git.php";

if (!current_user_can(PERMISSION_MANAGE_PLUGINS))
    die("Access denied");

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

<script>

    const ibtns = document.querySelectorAll(".btn-install-plugin");
    ibtns.forEach((ib) => {
        ib.addEventListener('click', (e) => {
            if (ib.innerHTML !== "Install" && ib.innerHTML !== "Uninstall") // some point between, don't do anything
            {}
            else if (ib.innerHTML == "Install") // install button pressed!
            {
                let req = requestInstall(ib.id.slice(0,-7))
                if (req == true)
                {
                    ib.classList.replace("btn-primary", "btn-secondary");
                    ib.innerHTML = "Installing...";
                }
                else
                {
                    let uhoh = new bsModal("Error", "Could not install: "+req, "", null, false, true);
                }
            }
            else if (ib.innerHTML == "Uninstall")
            {
                let req = requestInstall(ib.id.slice(0,-7), true); // true = uninstall
                if (req == true)
                {
                    ib.classList.replace("btn-outline-danger", "btn-secondary");
                    ib.innerHTML = "Uninstalling...";
                }
                else
                {
                    let uhoh = new bsModal("Error", "Could not uninstall: "+req, "", null, false, true);
                }
            }
        });
    })
    const installed = document.querySelectorAll(".installed");
    installed.forEach((el) => {
        let btn = document.getElementById(el.id + 'install');
        btn.classList.replace("btn-primary", "btn-outline-danger");
        btn.innerHTML = "Uninstall";
    });



    const infoButtons = document.querySelectorAll('.more-info');
    infoButtons.forEach((el) => {
        el.addEventListener('click', (event) => {
            create_plugin_info_modal(el.id);
            
        });
    });
</script>
