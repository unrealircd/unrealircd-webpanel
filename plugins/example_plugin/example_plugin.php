<?php

class example_plugin
{
	public $name = "Example plugin";
	public $author = "Valware";
	public $version = "1.0";
	public $description = "An example plugin to show how to make stuff";

	function __construct()
	{
		Hook::func(HOOKTYPE_NAVBAR, 'example_plugin::add_navbar'); 
	}

	public static function add_navbar(&$pages)
	{
		$page_name = "Example";
		$page_link = "plugins/example_plugin/example.php";
		$pages[$page_name] = $page_link;
	}
}