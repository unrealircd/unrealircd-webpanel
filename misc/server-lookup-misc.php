<?php


function generate_html_serverinfo($server)
{
    ?>

    <table class="table-sm table-responsive caption-top table-hover">
        <tbody>
            <tr>
                <th>Name</th>
                <td colspan="2"><code><?php echo $server->name; ?></code></td>
            </tr><tr>
                <th>Server ID (SID)</th>
                <td colspan="2"><code><?php echo $server->id; ?></code></td>
            </tr><tr>
                <th>Host</th>
                <td colspan="2"><code><?php echo $server->hostname; ?></code></td>
            </tr><tr>
                <th>IP</th>
                <td colspan="2"><code><?php echo $server->ip." </code> ";
                if ($cc = (isset($server->geoip->country_code)) ? strtolower($server->geoip->country_code) : "")
                {
                   ?>  <img src="https://flagcdn.com/48x36/<?php echo $cc; ?>.png"
                            width="20"
                            height="15">
                    <?php } ?>
                </td>
            </tr><tr>
                <th>Ident</th>
                <td colspan="2"><code><?php echo $server->user->username; ?></code></td>
            </tr><tr>
                <th>GECOS / Real Name</th>
                <td colspan="2"><code><?php echo $server->user->realname; ?></code></td>
            </tr><tr>
                <th>Virtual Host</th>
                <td colspan="2"><code><?php echo (isset($server->user->vhost)) ? $server->user->vhost : ""; ?></code></td>
            </tr><tr>
                <th>Connected to</th>
                <td colspan="2"><code><?php echo $server->user->servername; ?></code></td>

            </tr>
            <tr>
                <th>Logged in as</th>
                <td colspan="2"><code><?php echo (isset($server->user->account)) ? $server->user->account : ""; ?></code></td>
            </tr>
                

        </tbody>
    </table>

    <?php
}
function generate_html_modlist($srv)
{
    global $rpc;
    $modules = $rpc->server()->module_list($srv->id);
    ?>

    <table class="table table-sm table-responsive table-hover">
    <thead class="table-info">
        <th>Name</th>
        <th>Description</th>
        <th>Source</th>
        <th>Author</th>
        <th>Version</th>
    </thead>
    <tbody>
        <?php
            foreach($modules->list as $module)
            {
                echo "<tr>\n";
                echo "<td><code>$module->name</code></td>";
                $desc = $module->description;
                $short_desc = substr($desc, 0, 70); // truncate to 80 chars
                if (strlen($desc) > strlen($short_desc))
                    $short_desc .= "...";
                echo "<td><span href='#' data-toggle='tooltip' title=\"$desc\">$short_desc</span></td>";
                $source = (!$module->third_party) ? "<div class=\"badge rounded-pill badge-success\">Official</div>" : "<div class=\"badge rounded-pill badge-info\">Third-Party</div>";
                echo "<td>$source</td>";
                echo "<td>$module->author</td>";
                echo "<td>$module->version</td>";
            }
        ?>
    </tbody>
    </table>

    <?php
}