<?php
/* Bootstrap for the unit tests (vendor/bin/phpunit from the panel directory).
 * Loads only the files that have no side effects. inc/common.php is never
 * loaded: it reads the config, connects to the database and starts a session.
 */
if (PHP_SAPI !== 'cli')
	die("Tests run from the command line only\n");

/* A scratch "panel root" for tests that look at files (install checks) */
define('UPATH', sys_get_temp_dir() . '/panel-unit-' . getmypid());
@mkdir(UPATH, 0700, true);
$config = ['secrets' => ['pepper' => 'unit-test-pepper', 'key' => rtrim(base64_encode(str_repeat("k", 32)), '=')]];

$src = dirname(__DIR__);
require_once $src . '/vendor/autoload.php';
require_once $src . '/misc/strings.php';
require_once $src . '/Classes/class-message.php';
require_once $src . '/Classes/class-hook.php';
require_once $src . '/Classes/class-paneluser.php';
require_once $src . '/inc/install-checks.php';
require_once $src . '/misc/ip-whois-misc.php';
require_once $src . '/Classes/class-cmodes.php';
require_once $src . '/misc/channel-lookup-misc.php';

/* The session functions need an active session; start it now,
 * before PHPUnit prints anything (headers_sent() would block it later).
 */
ini_set('session.save_path', UPATH);
require_once $src . '/inc/session.php';
panel_session_begin();

function get_config($setting)
{
	global $config;
	$item = $config;
	foreach (explode("::", $setting) as $x)
	{
		if (!isset($item[$x]))
			return NULL;
		$item = $item[$x];
	}
	return $item;
}

register_shutdown_function(function () {
	foreach (glob(UPATH . '/{,.}*', GLOB_BRACE) ?: [] as $f)
		if (is_file($f)) @unlink($f);
});
