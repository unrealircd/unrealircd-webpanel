<?php

class top_countries_plugin
{
	public $name = "top countries plugin";
	public $author = "Valware";
	public $version = "1.0";
	public $description = "Allows to display a 'Top countries' in the menu";
	public $email = "v.a.pond@outlook.com";

	function __construct()
	{
		Hook::func(HOOKTYPE_NAVBAR, 'top_countries_plugin::add_navbar'); 
	}

	public static function add_navbar(&$pages)
	{
		$page_name = "Top Countries";
		$page_link = "plugins/top_countries_plugin/top_countries.php";
		$pages[$page_name] = $page_link;
	}
}