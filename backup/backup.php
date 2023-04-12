<?php
/*
    A file named backup.php is used to backup the list of connected users in UnrealIRCd into a MySQL table nommed "unreal_irc_users" which is created automatically if it does not exist.
    This backup is more detailed than the one created by 'anope_user'. 

    To set up the backup, add something like this to your crontab:
    php /home/<account>/unrealircd-webpanel/backup/backup.php
    and run this command every 5 minutes or 1 minute.

    In this file I also wanted to make that creates the following new tables:
    - unreal_irc_top_countries
    - unreal_irc_spamfilter
    - unreal_irc_servers
    - unreal_irc_channels
    - unreal_irc_name_bans
*/

require_once "../common.php";
require_once "../connection.php";
require_once "connection.php";
require_once "unreal_irc_users.php";

