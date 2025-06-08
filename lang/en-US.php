<?php
/*
 * These translation files have been specially created for the UnrealIRCd Web Panel interface.
 * The goal is to help users manage the panel in the Turkish language with ease.
 * All texts have been translated from the original English version of the UnrealIRCd Web Panel.
 * No official support from UnrealIRCd is provided by the developer/translator of this file.
 *
 * For more information, please refer to the UnrealIRCd Web Panel documentation:
 * https://www.unrealircd.org/docs/
 *
 * Translation by: [Valware]
 * Date: [08.06.2025]
 */
 
return [
	'language_name' => 'English',
	// Login Page
    'login_title' => 'Log in to use Admin Panel',
    'username_empty' => 'Username cannot be empty',
    'password_empty' => 'Password cannot be empty',
    'user_login_fail' => 'Incorrect login',
    'user_login_timeout' => 'Your session has timed out. Please login again to continue',
    'user_login_no_id' => 'Nothing to logout from',
    'user_login_missing' => 'Couldn\'t log you in: Missing credentials',
    'user_login_logged' => 'You have been logged out',
    'login_button' => 'Log-In',
	
	// Menu
	'menu_overview' => 'Overview',
	'menu_users' => 'Users',
	'menu_channels' => 'Channels',
	'menu_servers' => 'Servers',
	'a_menu_servers_bans' => 'Server Bans',
	'menu_server_ban' => 'Server Bans',
	'menu_name_bans' => 'Name Bans',
	'menu_ban_exceptions' => 'Ban Exceptions',
	'menu_spamfilter' => 'Spamfilter',
	'menu_logs' => 'Logs',
	'a_menu_tools' => 'Tools',
	'menu_ip_whois' => 'Ip Whois',
	'a_menu_settings' => 'Settings',
	'menu_general_settings' => 'General Settings',
	'menu_rpc_servers' => 'RPC Servers',
	
	// Requirements
	'requirements_php_version' => 'This webserver is using PHP version %s, but we require at least PHP 8.0.0.<br>' .
		'If you already installed PHP 8 but are still seeing this error, then it means ' .
		'apache/nginx/... is loading an older PHP version. Eg. on Debian/Ubuntu you need ' .
		'<code>apt-get install libapache2-mod-php8.2</code> (or a similar version) and ' .
		'<code>apt-get remove libapache2-mod-php7.4</code> (or a similar version). ' .
		'You may also need to choose again the PHP module to load in apache via <code>a2enmod php8.2</code>',
	'requirements_extensions_missing_title' => 'The following PHP module(s) need to be loaded:',
	'requirements_extensions_missing_cmd' => 'You need to install/enable these PHP packages and restart the webserver.<br>' .
		'If you are on Debian/Ubuntu then run <code>%s</code> and restart your webserver (eg: <code>systemctl restart apache2</code> for apache).',
	'requirements_config_file_notice_1' => 'This config file is written automatically by the UnrealIRCd webpanel.',
	'requirements_config_file_notice_2' => 'You are not really supposed to edit it manually.',
	'requirements_write_config_rename_error' => 'Could not write (rename) to file %s.<br>',
	'requirements_config_write_error' => 'Could not write to temporary config file %s.<br>We need write permissions on the config/ directory!<br>',
	'requirements_write_config_weird' => 'Error while running write_config_file() -- weird!',
	'requirements_write_config_fwrite' => 'Error writing to config file %s (on fwrite).<br>',
	'requirements_write_config_fclose' => 'Error writing to config file %s (on close).<br>',
	
	// RPC Servers
	'rpc_servers_title' => 'RPC Servers',
	'rpc_servers_description_1' => 'You can configure which JSON-RPC server(s) the panel can connect to.',
	'rpc_servers_description_2' => 'You normally only need one server, but it can be useful to have multiple servers, so you can switch to a secondary server in case the primary server goes down.',
	'rpc_servers_link_panel_info' => "Let's get your panel linked to UnrealIRCd. Read <u><a href=\"https://www.unrealircd.org/docs/UnrealIRCd_webpanel#Configuring_UnrealIRCd\" target=\"_blank\">the UnrealIRCd instructions</a></u> and then click <i>Add Server</i> below.",
	'rpc_servers_delete_failed' => 'Delete failed: could not find server',
	'rpc_servers_already_exists' => 'Server with that name already exists',
	'rpc_servers_not_exist' => 'Editing a server that does not exist!?',
	'rpc_servers_added' => 'RPC Server successfully added.',
	'rpc_servers_modified' => 'RPC Server successfully modified.',
	'rpc_add_server_title' => 'Add RPC Server',
	'rpc_add_display_name' => 'Display name',
	'rpc_add_display_name_help' => 'A short display name for in the RPC server list.',
	'rpc_add_default_server' => 'Default server',
	'rpc_add_default_server_help' => 'Make this the default (primary) server that will be used for connections.',
	'rpc_add_default_hostname' => 'Hostname or IP',
	'rpc_add_default_hostname_help' => "The hostname or IP address of your UnrealIRCd server. You should use <code>127.0.0.1</code> for the same machine.",
	'rpc_add_server_port' => "Server Port",
	'rpc_add_server_port_help' => "The port which you designated for RPC connections in your <code>unrealircd.conf</code>",
	'rpc_add_certificate' => "Verify SSL/TLS certificate",
	'rpc_tls_verify_cert_help' => "Can only be used with hostnames, don't enable this for 127.0.0.1.",
	'rpc_add_username' => "Username",
	'rpc_add_username_help' => "The name of your <code>rpc-user</code> block as defined in your <code>unrealircd.conf</code>",
	'rpc_add_password' => "Password",
	'rpc_add_cancel' => "Cancel",
	'rpc_add_servers' => 'Add Server',
	'rpc_add_server_add' => 'Add Server',
	'rpc_add_server_edit' => 'Edit',
	'rpc_add_error_notice_1' => 'RPC Server error',
	'rpc_add_error_notice_2' => 'The RPC Server failed to connect. Check your settings and try again.',
	'rpc_add_error_no' => 'No',
	'rpc_confirm_deletion' => 'Confirm deletion',
	'rpc_confirm_deletion_notice' => 'Are you sure you want to delete this server?',
	'rpc_confirm_deletion_cancel' => 'Cancel',
	'rpc_confirm_delete_server' => 'Delete Server',
	'rpc_display_name' => 'Display name',
	'rpc_display_hostname' => 'Hostname',
	'rpc_display_port' => 'Port',
	'rpc_display_rpcuser' => 'RPC User',

	// Other
	'other_base_url' => 'The base_url was not found in your config. Setup went wrong?',
	'other_auth_provided' => 'No authentication plugin loaded. You must load either sql_db, file_db, or a similar auth plugin!',
];
