<?php

/* This plugin requires SQLAuth minimum version 1.0 */

/** Define our PERMISSION to manage configuration blocks */
define('PERMISSION_CONFIG_BLOCKS', 'config_blocks');

class config_blocks
{
	public $name = "Configuration Blocks";
	public $author = "Valware";
	public $version = "1.0";
	public $description = "Create and host remote config files";
	public $email = "v.a.pond@outlook.com";

	function __construct()
	{
		require_plugin("SQLAuth", "1.0");
		Hook::func(HOOKTYPE_NAVBAR, 'config_blocks::add_navbar');
		Hook::func(HOOKTYPE_USER_PERMISSION_LIST, 'config_blocks::permission_list');

		$this->create_sql_table();
	}

	/** HOOKTYPE_NAVBAR
	 * If the current user has permission to manage config blocks,
	 * add a link to the "Tools" menu about it
	 */
	public static function add_navbar(&$pages)
	{
		$page_name = "Config Blocks";
		$page_link = "plugins/config_blocks/";
		if (current_user_can(PERMISSION_CONFIG_BLOCKS))
			$pages["Tools"][$page_name] = $page_link;
	}

	/** HOOKTYPE_USER_PERMISSION_LIST
	 *  Add a permission in the Panel Users permission list.
	 */
	public static function permission_list(&$list)
	{
		$list["Can manage Remote Configs in Config Blocks"] = PERMISSION_CONFIG_BLOCKS;
	}

	/** Creates the SQL table for storing config block information */
	public static function create_sql_table()
	{
		$conn = sqlnew();
		$conn->query("CREATE TABLE IF NOT EXISTS " . get_config("mysql::table_prefix") . "configblocks (
			block_id int AUTO_INCREMENT NOT NULL,
			block_name VARCHAR(255) NOT NULL,
			block_value VARCHAR(255) NOT NULL,
			added_ts VARCHAR(255),
			added_username VARCHAR(255),
			PRIMARY KEY (block_id)
		)");
	}
	
}