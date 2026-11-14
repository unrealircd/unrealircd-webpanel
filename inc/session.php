<?php
/* Session handling and CSRF protection for the panel.
 *
 * This file has no dependencies, so it can be used by small scripts
 * like api/timeout.php that do not load all of inc/common.php.
 *
 * - The session uses its own cookie name and keeps the logged in user
 *   under its own key (PANEL_SESSION_UID), so a PHP session that some
 *   other application on the same server created (with an 'id' in it)
 *   is never seen as a panel login.
 * - The cookie is HttpOnly, SameSite=Lax, and Secure on HTTPS. Stricter
 *   settings from php.ini (cookie_secure=1, samesite=Strict) are kept.
 * - Strict mode: PHP does not accept session IDs it did not create.
 * - The idle timeout is enforced on the server, not only by the browser.
 * - All state changing requests need a CSRF token (see panel_csrf_check()).
 */

if (!defined('PANEL_SESSION_UID'))
	define('PANEL_SESSION_UID', 'unrealircd_panel_uid');

/** The session cookie name: unique per panel install directory */
function panel_session_name() : string
{
	return "UnrealIRCdPanel_" . substr(hash('sha256', dirname(__DIR__)), 0, 10);
}

function panel_request_is_https() : bool
{
	if (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off')
		return true;
	if (isset($_SERVER['SERVER_PORT']) && ((int)$_SERVER['SERVER_PORT'] === 443))
		return true;
	if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https')
		return true;
	return false;
}

/** Start (or resume) the panel session. Safe to call more than once. */
function panel_session_begin() : bool
{
	if (session_status() === PHP_SESSION_ACTIVE)
		return true;
	if (headers_sent())
		return false;

	ini_set('session.use_strict_mode', '1');
	ini_set('session.use_only_cookies', '1');
	ini_set('session.use_trans_sid', '0');

	$params = session_get_cookie_params();
	$samesite = (isset($params['samesite']) && (strcasecmp($params['samesite'], 'Strict') === 0)) ? 'Strict' : 'Lax';
	$secure = panel_request_is_https() || !empty($params['secure']);
	session_name(panel_session_name());
	session_set_cookie_params([
		'lifetime' => 86400, /* the real (idle) timeout is enforced below, on the server */
		'path' => $params['path'] ?: '/',
		'domain' => $params['domain'] ?? '',
		'secure' => $secure,
		'httponly' => true,
		'samesite' => $samesite,
	]);
	if (!@session_start())
		return false;
	panel_session_enforce_timeout();
	return true;
}

/** The id of the logged in panel user, or NULL */
function panel_session_uid()
{
	/* Note: $_SESSION stays readable after session_write_close(), which the
	 * API scripts call early (so long requests do not block other pages).
	 * Do not test session_status() here, that broke every permission check
	 * made after the session was closed.
	 */
	if (!isset($_SESSION) || !is_array($_SESSION))
		return NULL;
	return $_SESSION[PANEL_SESSION_UID] ?? NULL;
}

/** Log out after the idle timeout, on the server side */
function panel_session_enforce_timeout()
{
	if (!isset($_SESSION[PANEL_SESSION_UID]))
		return;
	$timeout = (int)($_SESSION['session_timeout'] ?? 3600);
	if ($timeout < 60)
		$timeout = 60;
	if (isset($_SESSION['last-activity']) && ((time() - (int)$_SESSION['last-activity']) > $timeout))
	{
		panel_session_logout();
		$_SESSION['timed_out'] = true;
	}
}

/** Mark the session as logged in for user $uid (after a correct password) */
function panel_session_login($uid, int $timeout)
{
	panel_session_begin();
	/* New session ID at login: an ID that was planted before the login
	 * (session fixation) is useless afterwards.
	 */
	session_regenerate_id(true);
	$_SESSION = [];
	$_SESSION[PANEL_SESSION_UID] = $uid;
	$_SESSION['id'] = $uid; /* for older third-party plugins only, never trusted by the panel */
	$_SESSION['session_timeout'] = $timeout;
	$_SESSION['last-activity'] = time();
	$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/** Log out, but keep a (fresh, empty) session so that a new
 * CSRF token can be stored for the login form that follows.
 */
function panel_session_logout()
{
	if (session_status() !== PHP_SESSION_ACTIVE)
		return;
	$_SESSION = [];
	if (!headers_sent())
		session_regenerate_id(true);
}

/** Get the CSRF token of this session, creating one if needed */
function panel_csrf_token() : string
{
	panel_session_begin();
	if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token']))
		$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
	return $_SESSION['csrf_token'];
}

/** HTML for a hidden form field with the CSRF token */
function panel_csrf_field() : string
{
	return '<input type="hidden" name="csrf_token" value="'.htmlspecialchars(panel_csrf_token(), ENT_QUOTES).'">';
}

/** Does this request change state, so that it needs a token? */
function panel_request_needs_csrf() : bool
{
	$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
	return !in_array($method, ['GET', 'HEAD', 'OPTIONS'], true);
}

/** Check the CSRF token of the request (from the form or the X-CSRF-Token header) */
function panel_csrf_valid() : bool
{
	if (session_status() !== PHP_SESSION_ACTIVE)
		return false;
	$expected = $_SESSION['csrf_token'] ?? '';
	if (!is_string($expected) || ($expected === ''))
		return false;
	$given = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
	if (!is_string($given) || ($given === ''))
		return false;
	return hash_equals($expected, $given);
}
