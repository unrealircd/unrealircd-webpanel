<?php


function generate_html_servermodes($server)
{
    include UPATH . "/Classes/class-cmodes.php";
    ?>
    <table class="table-sm table-responsive caption-top table-hover">
    <thead>
        <th>Name</th>
        <th>Description</th>
        <th>Requires</th>
    </thead>
       <?php

       foreach ($server->server->features->chanmodes as $set)
       {
           if (!$set)
               break;
            for ($i = 0; isset($set[$i]); $i++)
            {
                $mode = $set[$i];
                if (isset(IRCList::$cmodes[$mode])) {
                   ?>
                <tr>
                    <th><?php echo IRCList::$cmodes[$mode]['name']; ?></th>
                    <td><?php echo IRCList::$cmodes[$mode]['description']; ?></td>
                    <td><div class="badge rounded-pill badge-dark"><?php echo IRCList::$cmodes[$mode]['requires']; ?></div></td>
                </tr><?php
                }
                else {
                    ?>
                    <tr>
                    <th>Unknown</th>
                    <td>Mode "<?php echo $mode; ?>"</td>
                    <td></td>
                </tr><?php
                }
                
            }
       }
       ?>
       </table><?php
}

function sinfo_conv_version_string($server) : string
{
    $string = (isset($server->server->features->software)) ? $server->server->features->software : "";
    if (strlen($string) && strpos($string,"-"))
    {
        $tok = split($string, "-");
        $return = "<code>" . $tok[1] . "</code> <label class=\"badge label rounded-pill badge-dark\">" . $tok[2] . "</div>";
    }
    if ($server->server->ulined)
        $return .= "<div class=\"label rounded-pill badge-warning\">Services</div>";
    return $return;
}

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
                <th>Info</th>
                <td colspan="2"><code><?php echo $server->server->info; ?></code></td>
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
                <th>Uplink</th>
                <td colspan="2"><code><?php echo "<a href=\"".BASE_URL."servers/details.php?server=".$server->server->uplink."\">".$server->server->uplink."</a>"; ?></code></td>
            </tr><tr>
                <th>User count</th>
                <td colspan="2"><code><?php echo $server->server->num_users; ?></code></td>
            </tr><tr>
                <th>Version</th>
                <td colspan="2"><?php echo sinfo_conv_version_string($server); ?></td>
            </tr>
        </tbody>
    </table>

    <?php
}
function generate_html_modlist($srv)
{
    global $rpc;
    $modules = $rpc->server()->module_list($srv->id);
    if (!$modules || !$modules->list)
    {
        echo $rpc->error;
    } else {
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
        foreach ($modules->list as $module) {
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
    }
        ?>
    </tbody>
    </table>

    <?php
}