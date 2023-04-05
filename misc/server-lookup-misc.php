<?php


function generate_html_servermodes($server)
{
    ?>
    <table class="table-sm table-responsive caption-top table-hover">
    <thead>
        <th>Name</th>
        <th>Mode</th>
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
                    <th><code><?php echo $mode; ?></code></th>
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
    if (isset($server->server->ulined) && $server->server->ulined)
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

function generate_html_usermodes($server)
{
    $modes = $server->server->features->usermodes;
    echo "<table class=\"table-sm table-responsive caption-top table-hover\">";
    for ($i=0; ($mode = (isset($modes[$i])) ? $modes[$i] : NULL); $i++)
    {
    
        if ($mode == "o")
        {
            ?>
                <tr>
                    <th>Oper</th>
                    <td>
                        User is an IRC Operator.
                    </td>
                </tr>
            <?php
        }
        elseif ($mode == "S")
        {
            ?>
                <tr>
                    <th>Service Bot</th>
                    <td>
                    User is a Services Bot.
                    </td>
                </tr>
            <?php
        }
        elseif ($mode == "d")
        {
            ?>
                <tr>
                    <th>Deaf</th>
                    <td>User is ignoring channel messages.</td>
                </tr>
            <?php
        }
        elseif ($mode == "i")
        {
            ?>
                <tr>
                    <th>Invisible</th>
                    <td>Not shown in /WHO searches.</td>
                </tr>
            <?php
        }
        elseif ($mode == "p")
        {
            ?>
                <tr>
                    <th>Private channels</th>
                    <td>Channels hidden in /WHOIS outputs.</td>
                </tr>
            <?php
        }
        elseif ($mode == "r")
        {
            ?>
                <tr>
                    <th>Registered Nick</th>
                    <td>User is using a registered nick.</td>
                </tr>
            <?php
        }
        elseif ($mode == "s")
        {
            ?>
                <tr>
                    <th>Server Notices</th>
                    <td>User is receiving server notices.</td>
                </tr>
            <?php
        }
        elseif ($mode == "t")
        {
            ?>
                <tr>
                    <th>Virtual Host</th>
                    <td>Using a custom hostmask.</td>
                </tr>
            <?php
        }
        elseif ($mode == "w")
        {
            ?>
                <tr>
                    <th>Wallops</th>
                    <td>Listening to <code>/WALLOPS</code> notices from IRC Operators.</td>
                </tr>
            <?php
        }
        elseif ($mode == "x")
        {
            ?>
                <tr>
                    <th>Hostmask</th>
                    <td>Using a hostmask (hiding their IP from non-IRCops).</td>
                </tr>
            <?php
        }
        elseif ($mode == "z")
        {
            ?>
                <tr>
                    <th>Secure</th>
                    <td>
                    User is using a secure connection.
                    </td>
                </tr>
            <?php
        }
        elseif ($mode == "B")
        {
            ?>
                <tr>
                    <th>Bot</th>
                    <td colspan="2">
                    User is marked as a Bot.
                    </td>
                </tr>
            <?php
        }
        elseif ($mode == "D")
        {
            ?>
                <tr>
                    <th>PrivDeaf</th>
                    <td colspan="2">
                    User is rejecting incoming private messages.
                    </td>
                </tr>
            <?php
        }
        elseif ($mode == "G")
        {
            ?>
                <tr>
                    <th>Filter</th>
                    <td colspan="2">
                    User is filtering Bad Words.
                    </td>
                </tr>
            <?php
        }
        elseif ($mode == "H")
        {
            ?>
                <tr>
                    <th>Hide IRCop</th>
                    <td colspan="2">
                    User is hiding their IRCop status.
                    </td>
                </tr>
            <?php
        }
        elseif ($mode == "I")
        {
            ?>
                <tr>
                    <th>Hide Idle</th>
                    <td colspan="2">
                    User is hiding their idle time.
                    </td>
                </tr>
            <?php
        }
        elseif ($mode == "R")
        {
            ?>
                <tr>
                    <th>RegOnly Messages</th>
                    <td colspan="2">
                    User is only accepting private messages from registered users.
                    </td>
                </tr>
            <?php
        }
        elseif ($mode == "T")
        {
            ?>
                <tr>
                    <th>Deny CTCPs</th>
                    <td colspan="2">
                    Denying CTCP requests.
                    </td>
                </tr>
            <?php
        }
        elseif ($mode == "W")
        {
            ?>
                <tr>
                    <th>View /WHOIS</th>
                    <td colspan="2">
                    User is receiving notifications when someone does a <code>/WHOIS</code> on them.
                    </td>
                </tr>
            <?php
        }
        elseif ($mode == "Z")
        {
            ?>
                <tr>
                    <th>Deny Insecure Messages</th>
                    <td colspan="2">
                    User is only accepting messages from users using a secure connection.
                    </td>
                </tr>
            <?php
        }
    }
    echo "</table>";
}

function generate_html_extserverinfo($server)
{
   ?>
    <table class="table-sm table-responsive caption-top table-hover">
        <tbody>
            <tr>
                <th>IP</th>
                <td colspan="2"><code><?php echo htmlspecialchars($server->ip); ?></code></td>
            </tr><tr>
                <th>Boot time</th>
                <td colspan="2"><code><?php echo htmlspecialchars($server->server->boot_time); ?></code></td>
            </tr><tr>
                <th>U-Lined</th>
                <td colspan="2"><?php echo ($server->server->ulined) ? "<span class=\"badge rounded-pill badge-success\">Yes</span>" : "<span class=\"badge rounded-pill badge-danger\">No</span>"; ?></td>
            </tr><tr>
                <th>Protocol</th>
                <td colspan="2"><a href="https://www.unrealircd.org/docs/Server_protocol:Protocol_version"><code><?php echo htmlspecialchars($server->server->features->protocol); ?></code></a></td>
            </tr><tr>
                <th>TLS</th>

                <td colspan="2">
                    <table>
                        <tr>
                            <th>Cert Fingerprint</th>
                            <td><?php echo "<span class=\"badge rounded-pill badge-info\">".htmlspecialchars($server->tls->certfp)."</span>"; ?></td>
                        </tr><tr>
                            <th>TLS Cipher</th>
                            <td><?php echo "<span class=\"badge rounded-pill badge-info\">".htmlspecialchars($server->tls->cipher)."</span>"; ?></td>
                        </tr>
                        
                    </table>
                </td>
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
            echo "<td>$source</td>";
            echo "<td>".htmlspecialchars($module->author)."</td>";
            echo "<td>".htmlspecialchars($module->version)."</td>";
        }
    }
        ?>
    </tbody>
    </table>

    <?php
}


function get_unreal_latest_version()
{
    $url = "https://www.unrealircd.org/downloads/list.json";
    $contents = file_get_contents($url);
    if (!$contents)
    {
        Message::Fail("Could not get latest version of UnrealIRCd. Please check again later.");
        return NULL;
    }
    $arr = json_decode($contents, true);
    $biggest = 0;
    foreach($arr as $key => $value)
    {
        if ($key > $biggest)
            $biggest = $key;
    }
    if (!$biggest)
    {
        Message::Fail("Could not get latest version of UnrealIRCd. Please check again later.");
        return NULL;
    }
    return $arr[$biggest]['Stable']['version'];
}