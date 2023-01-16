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
    $return = "";
    $tooltip = "";
    $badge = "";
    $display_string = $string;

    if (strlen($string) && strpos($string,"-"))
    {
        $tok = split($string, "-");
        if (($tok[0] == "UnrealIRCd") && isset($tok[2]))
        {
            if ($tok[2] == "git")
            {
                if (!empty($tok[3]))
                    $badge = "git:".$tok[3];
                else
                    $badge = "git";
                $tooltip = "Installed from GitHub";
                $display_string = $tok[0]."-".$tok[1]."-".$tok[2];
            } else if (substr($tok[2],0,2) == "rc")
            {
                $tooltip = "Release Candidate/Beta Version";
                $badge = "rc";
            } else if (strlen($tok[2]) == 9)
            {
                /* Guess that this is a commit id :D */
                $badge = "git:".$tok[2];
                $tooltip = "Installed from GitHub";
                $display_string = $tok[0]."-".$tok[1];
            }
            $tooltip = htmlspecialchars($tooltip);
            $display_string = htmlspecialchars($display_string);
        }
        $return = "<span data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"$tooltip\"><code>" . $display_string . "</code> <div class=\"badge rounded-pill badge-dark\">$badge</div></a>";
    }
    if ($server->server->ulined)
        $return .= "<div class=\"badge rounded-pill badge-warning\">Services</div>";
    return $return;
}

function generate_html_serverinfo($server)
{
    global $rpc;
    ?>
    <table class="table-sm table-responsive caption-top table-hover">
        <tbody>
            <tr>
                <th>Name</th>
                <td colspan="2"><code><?php echo htmlspecialchars($server->name); ?></code></td>
            </tr><tr>
                <th>Server ID (SID)</th>
                <td colspan="2"><code><?php echo htmlspecialchars($server->id); ?></code></td>
            </tr><tr>
                <th>Info</th>
                <td colspan="2"><code><?php echo htmlspecialchars($server->server->info); ?></code></td>
            </tr><tr>
                <th>Uplink</th>
                <?php $serverlkup = (isset($server->server->uplink)) ? $rpc->server()->get($server->server->uplink) : "<span class=\"badge rounded-pill badge-info\">None</span>"; ?>
                <td colspan="2"><code><?php echo "<a href=\"".BASE_URL."servers/details.php?server=".htmlspecialchars($serverlkup->id)."\">".htmlspecialchars($server->server->uplink)."</a>"; ?></code></td>
            </tr><tr>
                <th>User count</th>
                <td colspan="2"><code><?php echo htmlspecialchars($server->server->num_users); ?></code></td>
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
            echo "<td><code>".htmlspecialchars($module->name)."</code></td>";
            $desc = $module->description;
            $short_desc = substr($desc, 0, 70); // truncate to 80 chars
            if (strlen($desc) > strlen($short_desc))
                $short_desc .= "...";
            echo "<td><span href='#' data-toggle='tooltip' title=\"".htmlspecialchars($desc)."\">".htmlspecialchars($short_desc)."</span></td>";
            $source = (!$module->third_party) ? "<div class=\"badge rounded-pill badge-success\">Official</div>" : "<div class=\"badge rounded-pill badge-info\">Third-Party</div>";
            echo "<td>".htmlspecialchars($source)."</td>";
            echo "<td>".htmlspecialchars($module->author)."</td>";
            echo "<td>".htmlspecialchars($module->version)."</td>";
        }
    }
        ?>
    </tbody>
    </table>

    <?php
}