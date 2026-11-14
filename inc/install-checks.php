<?php
/* Checks about the install state of the panel. No dependencies, so the
 * unit tests (tests/Unit) can load this file on its own.
 */

/* Pages that are exempt from the config or login check say so themselves,
 * by defining a constant before including this file:
 * - PANEL_PAGE_INSTALLER: settings/install.php and api/installation.php
 * - PANEL_PAGE_LOGIN: login/index.php
 * (Matching on the file name is not safe: an install directory named
 *  eg. /var/www/adminlogin made the front page match "login/index.php".)
 */
function page_requires_no_config()
{
	return defined('PANEL_PAGE_INSTALLER');
}

function page_requires_no_login()
{
	return defined('PANEL_PAGE_LOGIN') || page_requires_no_config();
}

/** Files in config/ and data/ (and the panel root) that a web server could
 * hand out as plain text: copies of the secret files with another extension,
 * like editor backups. Returns a list of paths relative to the panel root.
 */
function panel_find_exposed_secret_copies(?string $root = NULL) : array
{
	$root = $root ?? UPATH;
	$found = [];
	foreach (["config", "data"] as $dir)
	{
		$list = @scandir($root."/$dir");
		if (!$list)
			continue;
		foreach ($list as $f)
		{
			if (($f === ".") || ($f === "..") || is_dir($root."/$dir/$f"))
				continue;
			/* The files that belong here: PHP files (executed, never shown),
			 * and our own .htaccess / .gitignore.
			 */
			if (in_array($f, [".htaccess", ".gitignore"], true))
				continue;
			if (str_ends_with($f, ".php") && !str_starts_with($f, "."))
				continue;
			$found[] = "$dir/$f";
		}
	}
	foreach (["config.php~", "config.php.bak", "config.php.old", "config.php.orig", ".config.php.swp"] as $f)
		if (file_exists($root."/$f"))
			$found[] = $f;
	return $found;
}

/** Has the panel been set up? Also true for the pre-2023 config.php
 * in the panel root, which read_config_file() still loads.
 * The installer must refuse to run when this returns true.
 */
function panel_is_configured(?string $root = NULL) : bool
{
	$root = $root ?? UPATH;
	return file_exists($root."/config/config.php") || file_exists($root."/config.php");
}
