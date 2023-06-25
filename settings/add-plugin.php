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
            console.log("Button clicked! " +ib.innerHTML);
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

    function requestInstall(name, uninstall = false)
    {
        let inst = (uninstall) ? "uninstall" : "install";
        var xhr = new XMLHttpRequest();

        xhr.onload = function() {
            if (xhr.status === 200) {
                var response = JSON.parse(xhr.responseText);
                console.log(response.success);
                let install_button = document.getElementById(name+'install');
                if (response.success !== undefined)
                {
                    if (install_button)
                    {
                        install_button.innerHTML = (inst == "uninstall") ? "Install" : "Uninstall";
                        install_button.classList.replace('btn-secondary', (inst == "uninstall") ? 'btn-primary' : 'btn-outline-danger');
                        let icomplete = bsModal(((inst == "uninstall") ? "Uninstall" : "Install") + " Plugin", response.success,"<div id=\""+name+"closebtn\" class=\"btn btn-danger\">Close</div>", null, true, true, false);
                        let closebtn = document.getElementById(name+"closebtn");
                        closebtn.addEventListener('click', e => {
                            location.reload();
                        });
                    }
                }
                else
                {
                    if (install_button)
                    {
                        install_button.innerHTML = (inst == "uninstall") ? "Uninstall" : "Install";
                        install_button.classList.replace('btn-secondary', (inst == "uninstall") ? 'btn-outline-danger' : 'btn-primary');
                        let icomplete = bsModal(((inst == "uninstall") ? "Uninstall" : "Install") + " Plugin", response.error,"", null, false, true);
                        let closebtn = document.getElementById(name+"closebtn");
                        closebtn.addEventListener('click', e => {
                            location.reload();
                        });
                    }
                }
            }
        };

        xhr.open('GET', BASE_URL + 'api/plugin.php?'+inst+'=' + name, true);
        xhr.send();
        return true;
    }

    function create_info_modal(modname)
    {
        fetch(BASE_URL + 'api/plugin.php')
        .then(response => response.json()) // Parse the response as JSON
        .then(data => {
            for (let i = 0; data[i]; i++)
            {
                if (data[i].name == modname)
                {
                    const modal = bsModal(
                        "<i>Information about " + data[i].title + "</i>", // title
                        "<div class=\"" + data[i].name + "_screenshots\"><i class=\"fa fa-spinner\" aria-hidden=\"true\"></i></div><div class=\"" + data[i].name + "_description\"><i class=\"fa fa-spinner\" aria-hidden=\"true\"></i></div>",
                        "<div id=\""+modname+"closebtn\" class=\"btn btn-danger\">Close</div>", null, true, true, false
                    );
                    let modalclose = document.getElementById(modal);
                    modalclose.addEventListener('click', (e) => {
                        $("#"+modal).modal('hide');
                    });
                    console.log(modal + '-body');
                    boobs = document.getElementById(modal + '-body');
                    boobs.innerHTML = data[i].description;
                }
            }
        })
        .catch(error => {
            // Handle any errors that occur during the request
            console.error('Error:', error);
        });
    }

    const infoButtons = document.querySelectorAll('.more-info');
    infoButtons.forEach((el) => {
        el.addEventListener('click', (event) => {
            create_info_modal(el.id);
            
        });
    });
</script>
