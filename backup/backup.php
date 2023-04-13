<?php
/*
    The idea is to execute a cron task every 1 minute :
    php /home/<account>/unrealircd-webpanel/backup/backup.php

    And it creates the following SQL tables:

        unreal_irc_users, equivalent to the anope_user table (or anope_db_user)
        unreal_irc_channels, equivalent to the anope_chan table

    It would also be necessary to create the following:

        unreal_irc_spamfilter
        unreal_irc_top_countries
        unreal_irc_servers
        unreal_irc_channels
        unreal_irc_name_bans
        unreal_irc_ison (for unreal_irc_channels and unreal_irc_users)

    This way, it would be possible to display the desired statistics on the websites.

    The only thing that bothers me is that I would like it to be real-time by executing it every 1 second, but I'm afraid it would overload the web server php, causing it to slow down or self-DDOS.

    What do you think?
*/

require_once "../common.php";
require_once "../connection.php";
require_once "connection.php";
require_once "unreal_irc_users.php";
require_once "unreal_irc_channels.php";

