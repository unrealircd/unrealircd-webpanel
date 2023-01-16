<?php

if (!defined('UPATH'))
	die("Access denied");


class Log
{
	function __construct()
	{

		/*
		if (!is_dir("log/"))
			mkdir("log/");
		$filename = "log/".date("Y-m-d")."log";
		if (!file_exists($filename))
		{
			$open = fopen($filename, 'w');
			$close = fclose($open);
		} */
	}
	function it(...$string)
	{
		foreach($string as $str)
		{
			if (defined('UNREALIRCD_DEBUG') && UNREALIRCD_DEBUG) {
				highlight_string(var_export($str, true));
			}
		}
	}
}

function do_log(...$strings)
{
	$log = new Log();
	$log->it($strings);
}

function get_date($year, $month, $day, $hour, $minute)
{
	return "$year-$month-$day" . "T$hour-$minute" . "Z";
}