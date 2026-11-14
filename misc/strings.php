<?php

/** Escape a value for use in HTML, both in element content and in
 * (quoted) attribute values. Always use this for any data that
 * comes from IRC, from the RPC server, from the user or from the URL.
 * Invalid UTF-8 is replaced instead of making the whole string vanish.
 */
function h($str) : string
{
	if ($str === NULL || is_array($str) || is_object($str))
		return '';
	return htmlspecialchars((string)$str, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, 'UTF-8');
}

/** Encode a value for use inside a JavaScript string or expression
 * in an inline <script> block (also safe inside HTML attributes
 * when combined with h()).
 */
function js_value($value) : string
{
	return json_encode($value, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE | JSON_UNESCAPED_SLASHES);
}

/** Check that a value is a plain hostname or IP address,
 * for use as the RPC server host (in a wss://host:port URL).
 */
function is_valid_rpc_host($host) : bool
{
	if (!is_string($host) || ($host === ""))
		return false;
	if (filter_var($host, FILTER_VALIDATE_IP) !== false)
		return true;
	return (bool)preg_match('/^(?=.{1,253}$)([A-Za-z0-9_]([A-Za-z0-9_-]{0,61}[A-Za-z0-9_])?\.)*[A-Za-z0-9_]([A-Za-z0-9_-]{0,61}[A-Za-z0-9_])?$/D', $host); /* D: no trailing newline */
}

/** Escape an IRC name (channel, nick) for display and make lookalike names
 * stand out (same as irc_name_html() in js/unrealircd-admin.js):
 * invisible characters are shown as their code point, and names that mix
 * Latin with Cyrillic or Greek, or contain invalid UTF-8 (which UnrealIRCd
 * shows as U+FFFD), get a warning badge.
 */
function irc_name_html($name) : string
{
	$name = (string)$name;
	if (!mb_check_encoding($name, 'UTF-8'))
	{
		/* Replace invalid bytes with U+FFFD (like UnrealIRCd's JSON does), not '?' */
		$old = mb_substitute_character();
		mb_substitute_character(0xFFFD);
		$name = mb_convert_encoding($name, 'UTF-8', 'UTF-8');
		mb_substitute_character($old);
	}
	$out = preg_replace_callback('/[\p{Cf}\p{Cc}\x{115F}\x{1160}\x{3164}\x{FFA0}\x{2800}]/u', function ($m) {
		return "\x01".sprintf("U+%04X", mb_ord($m[0], 'UTF-8'))."\x02";
	}, $name);
	if ($out === null)
		$out = $name;
	$out = h($out);
	$out = preg_replace('/\x01(U\+[0-9A-F]+)\x02/', '<span class="badge badge-warning" title="Invisible character">$1</span>', $out);
	if (str_contains($name, "\u{FFFD}"))
		$out .= ' <span class="badge badge-danger" title="This name contains bytes that are not valid UTF-8. The panel cannot address it reliably.">invalid UTF-8</span>';
	if (preg_match('/\p{Latin}/u', $name) && preg_match('/[\p{Cyrillic}\p{Greek}]/u', $name))
		$out .= ' <span class="badge badge-warning" title="This name mixes Latin with Cyrillic or Greek letters, it may imitate another name">mixed scripts</span>';
	return $out;
}

/** Only allow redirects to a path on this panel.
 * Rejects absolute URLs, scheme-relative ones (//host), backslashes and
 * any control character (browsers drop TAB/CR/LF, so "/\t/evil.example"
 * would otherwise turn into "//evil.example").
 */
function panel_safe_redirect($target, $base) : string
{
	$base = (string)$base;
	$base_path = parse_url((string)$base, PHP_URL_PATH);
	if (!is_string($base_path) || ($base_path === ""))
		$base_path = "/";
	if (!is_string($target) || ($target === ""))
		return $base;
	if (preg_match('/[\x00-\x20\x7F\\\\]/', $target))
		return $base;
	if (($target[0] !== '/') || str_starts_with($target, '//'))
		return $base;
	if (!str_starts_with($target, $base_path))
		return $base;
	return $target;
}

function to_slug($str)
{
	return str_replace(['-', ' '], '_', strtolower($str));
}

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
		/* Only skip removed (NULL) and empty items. Do not use !$str here,
		 * that would also drop the string "0", eg a channel key of 0.
		 */
		if ($str === NULL || $str === false || $str === '')
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

/** This function takes a string, tokenizes
 * it by a space (chr 32), removes the first
 * word/token, and returns the result.
 * Mostly used for string manipulation around
 * the source.
 * 
 * Syntax:
 * rparv(String $sentence)
 * 
 * Returns:
 * string|false
 */
function rparv($string)
{
	$parv = split($string);
	$first = strlen($parv[0]) + 1;
	$string = substr($string, $first);
	if ($string)
		return $string;
	return false;
}

/* Taken from https://www.aviran.org/stripremove-irc-client-control-characters/
 * We may want to re-base it off our UnrealIRCd's one though.
 */
function StripControlCharacters($text)
{
    $controlCodes = array(
        '/(\x03(?:\d{1,2}(?:,\d{1,2})?)?)/',    // Color code
        '/\x02/',                               // Bold
        '/\x0F/',                               // Escaped
        '/\x16/',                               // Italic
        '/\x1F/'                                // Underline
    );
    return preg_replace($controlCodes,'',$text);
}
