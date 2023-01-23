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
