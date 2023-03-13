<?php

/** Splits up a string by a space
 * (chr 32)
 *
 * Syntax:
 * split($string)
 * 
 * Returns:
 * array $tokens
 */
function split($str, $delimiter = " ") : Array
{
	return explode($delimiter,$str);
}

/**
 * 
 * @param mixed $array
 * @param mixed $delimiter
 * @return string
 */
function glue($array, $delimiter = " ")
{
	$string = "";
	foreach($array as $str)
	{
		if (!$str)
			continue;
		$string .= $str.$delimiter;
	}
	return trim($string,$delimiter);
}

/**
 * Gets the relative path of the filename
 * @param mixed $filename
 * @return string
 */
function get_relative_path($filename)
{
    $relativepath = split($filename, "/");
    foreach($relativepath as &$tok)
    {
        $isFinal = ($tok == "html") ? 1 : 0;
        $tok = NULL;
        if ($isFinal)
            break;
    }
    $relativepath = glue($relativepath,"/");
    return $relativepath;
}

/**
 * Returns a `nick` if the string was in the syntax:
 * nick!ident@host
 * @param mixed $str
 * @return mixed
 */
function show_nick_only($str)
{
	$x = strpos($str, "!");
	if ($x !== false)
		$str = substr($str, 0, $x);
	return $str;
}

/**
 * Figures out how long ago a given time was
 * returns string example:
 *  - "32 minutes ago"
 *  - "5 hours ago"
 *  - "12 seconds ago"
 */
function how_long_ago($timestamp)
{
	$now = time();
	$diff = $now - strtotime($timestamp);
	$units = array(
		31536000 => 'year',
		2592000 => 'month',
		604800 => 'week',
		86400 => 'day',
		3600 => 'hour',
		60 => 'minute',
		1 => 'second'
	);

	foreach ($units as $unit => $text) {
		if ($diff < $unit) continue;
		$numberOfUnits = floor($diff / $unit);
		return $numberOfUnits.' '.$text.(($numberOfUnits>1)?'s':'').' ago';
	}
}

/**
 * Uses system time.
 * Returns:
 * 	- evening
 *  - morning
 *  - afternoon
 */
function time_of_day()
{
	$timeofday = "day"; // in case something went wrong? lol
	$hour = date("H");
	if ($hour >= 18 || $hour < 4)
		$timeofday = "evening";
	else if ($hour >= 4 && $hour < 12)
		$timeofday = "morning";
	else if ($hour >= 12 && $hour < 18)
		$timeofday = "afternoon";

	return $timeofday;
}


/**
 * Concatenate a string to a string
 */
function strcat(&$targ,$string) : void
{ $targ .= $string; }


/**
 * Concatenate a string to a string and limits the string to a certain length
 */
function strlcat(&$targ,$string,$size) : void
{
	strcat($targ,$string);
	$targ = mb_substr($targ,0,$size);
}


/**
 * Prefixes a string to a string
 */
function strprefix(&$targ,$string) : void
{ $targ = $string.$targ; }


/**
 * Prefixes a string to a string and limits the string to a certain length
 */
function strlprefix(&$targ,$string,$size) : void
{
	if (sizeof($targ) >= $size)
		return;

	strprefix($targ,$string);
	$targ = mb_substr($targ,0,$size);
}

/** Checks if the token provided is a bad pointer, by reference
 * Returns Bool value true if it IS bad
 *
 * Syntax:
 * BadPtr($variable)
 * 
 * Returns:
 * @
*/
function BadPtr(&$tok)
{
	if (!isset($tok) || empty($tok) || !$tok || strlen($tok) == 0)
		return true;
	return false;
}