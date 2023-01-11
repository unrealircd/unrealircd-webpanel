<?php

function generate_html_whois($user)
{
    ?>

    <table class="table table-responsive caption-top table-hover">
        <tbody>
            <tr>
                <th>Nick</th>
                <td colspan="2"><code><?php echo $user->name; ?></code></td>
            </tr><tr>
                <th>UID</th>
                <td colspan="2"><code><?php echo $user->id; ?></code></td>
            </tr><tr>
                <th>Real Host</th>
                <td colspan="2"><code><?php echo $user->hostname; ?></code></td>
            </tr><tr>
                <th>IP</th>
                <td colspan="2"><code><?php echo $user->ip." </code> ";
                if ($cc = (isset($user->geoip->country_code)) ? strtolower($user->geoip->country_code) : "")
                {
                   ?>  <img src="https://flagcdn.com/48x36/<?php echo $cc; ?>.png"
                            width="20"
                            height="15">
                    <?php } ?>
                </td>
            </tr><tr>
                <th>Ident</th>
                <td colspan="2"><code><?php echo $user->user->username; ?></code></td>
            </tr><tr>
                <th>GECOS / Real Name</th>
                <td colspan="2"><code><?php echo $user->user->realname; ?></code></td>
            </tr><tr>
                <th>Virtual Host</th>
                <td colspan="2"><code><?php echo (isset($user->user->vhost)) ? $user->user->vhost : ""; ?></code></td>
            </tr><tr>
                <th>Connected to</th>
                <td colspan="2"><code><?php echo $user->user->servername; ?></code></td>
                <td></td>
            </tr>
            <tr>
                <th>Logged in as</th>
                <td colspan="2"><code><?php echo (isset($user->user->account)) ? $user->user->account : ""; ?></code></td>
            </tr>
                

        </tbody>
    </table>

    <?php
}
function generate_html_usersettings($user)
{
    ?>

    <table class="table table-responsive caption-top table-hover">
        <tbody>
           <?php
                for ($i=0; ($mode = (isset($user->user->modes[$i])) ? $user->user->modes[$i] : NULL); $i++)
                {
               
                    if ($mode == "o")
                    {
                        ?>
                            <tr>
                                <th>Oper</th>
                                <td colspan="2">
                                    <table class="table table-responsive caption-top table-hover">
                                        <tr>
                                            <td>Oper Login</td>
                                            <td><code><?php echo $user->user->operlogin; ?></code></td>
                                        </tr>
                                        <tr>
                                            <td>Oper Class</td>
                                            <td><?php  echo (isset($user->user->operclass)) ? "<span class=\"badge-pill badge-info\">".$user->user->operclass."</span>" : "<span class=\"badge-pill badge-info\">None</span>"; ?></td>
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
                                <td colspan="2">
                                This user is a Services Bot.
                                </td>
                            </tr>
                        <?php
                    }
                    elseif ($mode == "d")
                    {
                        ?>
                            <tr>
                                <th>Deaf</th>
                                <td colspan="2">User is ignoring channel messages.</td>
                            </tr>
                        <?php
                    }
                    elseif ($mode == "i")
                    {
                        ?>
                            <tr>
                                <th>Invisible</th>
                                <td colspan="2">Not shown in /WHO searches.</td>
                            </tr>
                        <?php
                    }
                    elseif ($mode == "p")
                    {
                        ?>
                            <tr>
                                <th>Private channels</th>
                                <td colspan="2">Channels hidden in /WHOIS outputs.</td>
                            </tr>
                        <?php
                    }
                    elseif ($mode == "r")
                    {
                        ?>
                            <tr>
                                <th>Registered Nick</th>
                                <td colspan="2">This user is using a registered nick.</td>
                            </tr>
                        <?php
                    }
                    elseif ($mode == "s")
                    {
                        ?>
                            <tr>
                                <th>Server Notices</th>
                                <td colspan="2">This user is receiving server notices.</td>
                            </tr>
                        <?php
                    }
                    elseif ($mode == "t")
                    {
                        ?>
                            <tr>
                                <th>Virtual Host</th>
                                <td colspan="2">Using a custom hostmask</td>
                            </tr>
                        <?php
                    }
                    elseif ($mode == "w")
                    {
                        ?>
                            <tr>
                                <th>Wallops</th>
                                <td colspan="2">Listening to <code>/WALLOPS</code> notices from IRC Operators.</td>
                            </tr>
                        <?php
                    }
                    elseif ($mode == "x")
                    {
                        ?>
                            <tr>
                                <th>Hostmask</th>
                                <td colspan="2">Using a hostmask (hiding their IP from non-IRCops).</td>
                            </tr>
                        <?php
                    }
                    elseif ($mode == "z")
                    {
                        ?>
                            <tr>
                                <th>Secure</th>
                                <td colspan="2">
                                <table class="table table-responsive caption-top table-hover">
                                        <tr>
                                            <td>Cipher</td>
                                            <td><code><?php echo $user->tls->cipher; ?></code></td>
                                        </tr>
                                        <tr>
                                            <td>Cert Fingerprint</td>
                                            <td><?php echo (isset($user->tls->certfp)) ? "<span class=\"badge-pill badge-info\">".$user->tls->certfp."</span>" : "<span class=\"badge-pill badge-info\">None</span>"; ?></td>
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


           ?>
        </tbody>
    </table>

    <?php
}