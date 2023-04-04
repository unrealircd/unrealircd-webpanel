<?php

function generate_html_whois($user)
{
    global $rpc;
    ?>

    <table class="table-sm table-responsive caption-top table-hover">
        <tbody>
            <tr>
                <th>Nick</th>
                <td colspan="2"><code><?php echo htmlspecialchars($user->name); ?></code></td>
            </tr><tr>
                <th>User ID (UID)</th>
                <td colspan="2"><code><?php echo htmlspecialchars($user->id); ?></code></td>
            </tr><tr>
                <th>Real Host</th>
                <td colspan="2"><code><?php echo htmlspecialchars($user->hostname); ?></code></td>
            </tr><tr>
                <th>IP</th>
                <td colspan="2"><code><?php echo htmlspecialchars($user->ip); ?></code>
                <?php
                if ($cc = (isset($user->geoip->country_code)) ? strtolower($user->geoip->country_code) : "")
                {
                   ?>  <img src="https://flagcdn.com/48x36/<?php echo htmlspecialchars($cc); ?>.png"
                            width="20"
                            height="15">
                    <?php } ?>
                    <a href="<?php echo htmlspecialchars(BASE_URL."tools/ip-whois.php?ip=$user->ip"); ?>"><button class="btn-sm btn-primary">WHOIS IP</button></a>
                </td>
            </tr><tr>
                <th>Ident</th>
                <td colspan="2"><code><?php echo htmlspecialchars($user->user->username); ?></code></td>
            </tr><tr>
                <th>GECOS / Real Name</th>
                <td colspan="2"><code><?php echo htmlspecialchars($user->user->realname); ?></code></td>
            </tr><tr>
                <th>Virtual Host</th>
                <td colspan="2"><code><?php echo (isset($user->user->vhost)) ? htmlspecialchars($user->user->vhost) : ""; ?></code></td>
            </tr><tr>
                <th>Connected to</th>
                <?php $serverlkup = $rpc->server()->get($user->user->servername); ?>
			   
                <td colspan="2"><a href="<?php echo BASE_URL."servers/details.php?server=$serverlkup->id"; ?>"><code><?php echo htmlspecialchars($user->user->servername); ?></code></td>

            </tr>
            <tr>
                <th>Logged in as</th>
                <td colspan="2"><code><?php echo (isset($user->user->account)) ? "<a href=\"".BASE_URL."users/?account=".htmlspecialchars($user->user->account)."\">".htmlspecialchars($user->user->account)."</a>" : ""; ?></code></td>
            </tr>
                

        </tbody>
    </table>

    <?php
}
function generate_html_usersettings($user)
{
    ?>

    <table class="table-sm table-responsive caption-top table-hover">
        <tbody>
           <?php
                for ($i=0; ($mode = (isset($user->user->modes[$i])) ? $user->user->modes[$i] : NULL); $i++)
                {
               
                    if ($mode == "o")
                    {
                        ?>
                            <tr>
                                <th>Oper</th>
                                <td>
                                    <table class="table-sm table-responsive caption-top table-hover">
                                        <tr>
                                            <td>Oper Login</td>
                                            <td><code><?php
                                            $operlogin = (isset($user->user->operlogin)) ? $user->user->operlogin : "";
                                             echo htmlspecialchars($operlogin); 
                                             ?></code></td>
                                        </tr>
                                        <tr>
                                            <td>Oper Class</td>
                                            <td><?php echo (isset($user->user->operclass)) ? "<span class=\"rounded-pill badge badge-info\">".htmlspecialchars($user->user->operclass)."</span>" : "<span class=\"rounded-pill badge badge-info\">None</span>"; ?></td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        <?php
                    }
                    elseif ($mode == "S")
                    {
                        ?>
                            <tr>
                                <th>Service Bot</th>
                                <td>This user is a Services Bot.</td>
                            </tr>
                        <?php
                    }
                    elseif ($mode == "d")
                    {
                        ?>
                            <tr>
                                <th>Deaf</th>
                                <td>The user ignores channel messages.</td>
                            </tr>
                        <?php
                    }
                    elseif ($mode == "i")
                    {
                        ?>
                            <tr>
                                <th>Invisible</th>
                                <td>The user wont be shown in /WHO searches.</td>
                            </tr>
                        <?php
                    }
                    elseif ($mode == "p")
                    {
                        ?>
                            <tr>
                                <th>Private channels</th>
                                <td>Channels are hidden in /WHOIS outputs.</td>
                            </tr>
                        <?php
                    }
                    elseif ($mode == "r")
                    {
                        ?>
                            <tr>
                                <th>Registered Nick</th>
                                <td>The user's nick is registered in services.</td>
                            </tr>
                        <?php
                    }
                    elseif ($mode == "s")
                    {
                        ?>
                            <tr>
                                <th>Server Notices</th>
                                <td>The user is receiving server notices.</td>
                            </tr>
                        <?php
                    }
                    elseif ($mode == "t")
                    {
                        ?>
                            <tr>
                                <th>Virtual Host</th>
                                <td>The user is using a custom hostmask.</td>
                            </tr>
                        <?php
                    }
                    elseif ($mode == "w")
                    {
                        ?>
                            <tr>
                                <th>Wallops</th>
                                <td>User is listening to <code>/WALLOPS</code> notices from IRC Operators.</td>
                            </tr>
                        <?php
                    }
                    elseif ($mode == "x")
                    {
                        ?>
                            <tr>
                                <th>Hostmask</th>
                                <td>The user is using a hostmask (hiding their IP from non-IRCops).</td>
                            </tr>
                        <?php
                    }
                    elseif ($mode == "z")
                    {
                        ?>
                            <tr>
                                <th>Secure</th>
                                <td>
                                <table class="table-sm table-responsive caption-top table-hover">
                                        <tr>
                                            <td>Cipher</td>
                                            <td><code><?php
                                                $cipher = (isset($user->tls->cipher)) ? $user->tls->cipher : "";
                                                echo htmlspecialchars($cipher);
                                            ?></code></td>
                                        </tr>
                                        <tr>
                                            <td>Cert Fingerprint</td>
                                            <td><?php echo (isset($user->tls->certfp)) ? "<code>".htmlspecialchars($user->tls->certfp)."</code>" : "<span class=\"rounded-pill badge badge-info\">None</span>"; ?></td>
                                        </tr>
                                    </table> 
                                </td>
                            </tr>
                        <?php
                    }
                    elseif ($mode == "B")
                    {
                        ?>
                            <tr>
                                <th>Bot</th>
                                <td colspan="2">User is marked as a Bot.</td>
                            </tr>
                        <?php
                    }
                    elseif ($mode == "D")
                    {
                        ?>
                            <tr>
                                <th>PrivDeaf</th>
                                <td colspan="2">User is rejecting incoming private messages.</td>
                            </tr>
                        <?php
                    }
                    elseif ($mode == "G")
                    {
                        ?>
                            <tr>
                                <th>Filter</th>
                                <td colspan="2">User is filtering Bad Words.</td>
                            </tr>
                        <?php
                    }
                    elseif ($mode == "H")
                    {
                        ?>
                            <tr>
                                <th>Hide IRCop</th>
                                <td colspan="2">User is hiding their IRCop status.</td>
                            </tr>
                        <?php
                    }
                    elseif ($mode == "I")
                    {
                        ?>
                            <tr>
                                <th>Hide Idle</th>
                                <td colspan="2">User is hiding their idle time.</td>
                            </tr>
                        <?php
                    }
                    elseif ($mode == "R")
                    {
                        ?>
                            <tr>
                                <th>RegOnly Messages</th>
                                <td colspan="2">The user is only accepting private messages from registered users.</td>
                            </tr>
                        <?php
                    }
                    elseif ($mode == "T")
                    {
                        ?>
                            <tr>
                                <th>Deny CTCPs</th>
                                <td colspan="2">The user denies CTCP requests.</td>
                            </tr>
                        <?php
                    }
                    elseif ($mode == "W")
                    {
                        ?>
                            <tr>
                                <th>View /WHOIS</th>
                                <td colspan="2">User is receiving notifications when someone does a <code>/WHOIS</code> on them.</td>
                            </tr>
                        <?php
                    }
                    elseif ($mode == "Z")
                    {
                        ?>
                            <tr>
                                <th>Deny Insecure Messages</th>
                                <td colspan="2">User is only accepting messages from users using a secure connection.</td>
                            </tr>
                        <?php
                    }
                }


           ?>
        </tbody>
    </table>

    <?php
}


function generate_html_userchannels($user)
{
    ?>

    <table class="table-sm table-responsive caption-top table-hover table-striped">
        <thead class="table-info">
            <th>
                Channel
            </th>
            <th >
                Status
            </th>
        </thead>
        <tbody>
            <?php
                foreach($user->user->channels as $chan)
                {
                    ?>
                    <tr>
                        <td><?php echo "<a href=\"".BASE_URL."channels/details.php?chan=".urlencode($chan->name)."\">$chan->name</a>"; ?></td>
                        <td>
                            
                            <?php
                                for ($i = 0; isset($chan->level[$i]); $i++)
                                {
                                    ?><div class="text-nowrap row mb-1"><?php
                                    if ($chan->level[$i] == "v")
                                    {
                                        ?><span class="rounded-pill badge badge-info" value="Voice">Voice</span><?php
                                    }
                                    if ($chan->level[$i] == "h")
                                    {
                                        ?><span class="rounded-pill badge badge-info">Half-Op</span><?php
                                    }
                                    if ($chan->level[$i] == "o")
                                    {
                                        ?><h6><span class="rounded-pill badge badge-info">Operator</span></h6><?php
                                    }
                                    if ($chan->level[$i] == "a")
                                    {
                                        ?><span class="rounded-pill badge badge-info">Admin</span><?php
                                    }
                                    if ($chan->level[$i] == "q")
                                    {
                                        ?><span class="rounded-pill badge badge-info">Owner</span><?php
                                    }
                                    if ($chan->level[$i] == "Y")
                                    {
                                        ?><span class="rounded-pill badge badge-info">OJOIN</span><?php
                                    }
                                  ?></div><?php
                                }
                            ?>
                        </td>
                    </tr>
                    <?php
                }
            ?>
        </tbody>
    </table>

    <?php
}
