<?php

require_once "../inc/common.php";
require_once "../inc/header.php";


?>

<h2>Active Plugins <a class="btn btn-sm btn-primary" href="add-plugin.php">Add New</a></h2>
<br>
Your available plugins. 
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
<?php
require_once "../inc/footer.php";
