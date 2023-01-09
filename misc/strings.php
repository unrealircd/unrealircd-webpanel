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