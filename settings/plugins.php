<?php

require_once "../common.php";
require_once "../header.php";


?>

<h2>Active Plugins</h2>
<br>
To load and unload plugins, see the <code>PLUGINS</code> section of your <code>config.php</code><br>
<br>
<table class="container-xxl table table-sm table-responsive caption-top table-striped">
	<thead class="table-primary">
	<form method="post">
	<th scope="col">Plugin Name</th>
	<th scope="col">Handle</th>
	<th scope="col">Version</th>
	<th scope="col">Description</th>
	<th scope="col">Author</th>
    <th scope="col">Contact</th>
    
	</thead>
	<tbody>
        <?php
            foreach(Plugins::$list as $plugin)
            {
                echo "<tr>";
                echo "<td scope=\"col\">".$plugin->name."</td>";
                echo "<td scope=\"col\"><code>".$plugin->handle."</code></td>";
                echo "<td scope=\"col\"><code>".$plugin->version."</code></td>";
                echo "<td scope=\"col\">".$plugin->description."</td>";
                echo "<td scope=\"col\">".$plugin->author."</td>";
                echo "<td scope=\"col\"><a href='mailto:$plugin->email'>".$plugin->email."</a></td>";
                echo "</tr>";
            }
        ?>
    </tbody>
</table>