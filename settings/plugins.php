<?php

require_once "../inc/common.php";
require_once "../inc/header.php";


?>

<h2>Active Plugins <a class="btn btn-sm btn-primary" href="add-plugin.php">Add New</a></h2>
<br>
Your installed plugins: 
<br>
<table class="container-xxl table table-sm table-responsive caption-top table-striped">
	<thead class="table-primary">
	<form method="post">
    <th scope="col"><input type="checkbox" label='selectall' onClick="toggle_tkl(this)" /></th>
	<th scope="col">Plugin Name</th>
	<th scope="col">Version</th>
	<th scope="col">Description</th>
	<th scope="col">Author</th>
    <th scope="col">Contact</th>
    <th scope="col">Uninstall</th>
    
	</thead>
	<tbody>
        <?php
            foreach(Plugins::$list as $plugin)
            {
                echo "<tr>";
                echo "<th scope=\"col\"><input type=\"checkbox\" label='selectall' onClick=\"toggle_tkl(this)\" /></th>";
                echo "<td scope=\"col\" onClick=\"create_plugin_info_modal('".$plugin->handle."')\">".$plugin->name."</td>";
                echo "<td scope=\"col\"><code>".$plugin->version."</code></td>";
                echo "<td scope=\"col\">".$plugin->description."</td>";
                echo "<td scope=\"col\">".$plugin->author."</td>";
                echo "<td scope=\"col\"><a href='mailto:$plugin->email'>".$plugin->email."</a></td>";
                echo "<td width=\"110\" scope=\"col\"><div id=\"".$plugin->handle."install\" class='text-center btn-sm btn-danger btn-install-plugin'>Uninstall</div></td>";
                echo "</tr>";
            }
        ?>
    </tbody>
</table>

<script>
const ibtns = document.querySelectorAll(".btn-install-plugin");
    ibtns.forEach((ib) => {
        ib.addEventListener('click', (e) => {
            console.log(ib.id);
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
</script>

<?php
require_once "../inc/footer.php";
