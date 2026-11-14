<?php
use PHPUnit\Framework\TestCase;

/** Sessions and CSRF tokens (inc/session.php) */
final class SessionTest extends TestCase
{
	protected function setUp(): void
	{
		$_SESSION = [];
		$_POST = [];
		unset($_SERVER['HTTP_X_CSRF_TOKEN']);
	}

	public function test_plain_id_from_another_app_is_not_a_login(): void
	{
		$_SESSION = ['id' => 1];
		$this->assertNull(panel_session_uid());
	}

	public function test_own_session_key_is_a_login(): void
	{
		$_SESSION = [PANEL_SESSION_UID => 7];
		$this->assertSame(7, panel_session_uid());
	}

	public function test_login_still_known_after_session_write_close(): void
	{
		/* api/common_api.php closes the session early; permission checks after that must still work */
		$_SESSION = [PANEL_SESSION_UID => 9];
		session_write_close();
		try {
			$this->assertSame(9, panel_session_uid());
		} finally {
			@session_start();
		}
	}

	public function test_cookie_name_is_panel_specific(): void
	{
		$this->assertMatchesRegularExpression('/^UnrealIRCdPanel_[0-9a-f]{10}$/', panel_session_name());
		$this->assertNotSame('PHPSESSID', panel_session_name());
	}

	public function test_csrf_token_is_random_and_stable(): void
	{
		$t = panel_csrf_token();
		$this->assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $t);
		$this->assertSame($t, panel_csrf_token());
	}

	public function test_request_without_token_is_refused(): void
	{
		panel_csrf_token();
		$this->assertFalse(panel_csrf_valid());
	}

	public function test_request_with_wrong_token_is_refused(): void
	{
		panel_csrf_token();
		$_POST['csrf_token'] = str_repeat('0', 64);
		$this->assertFalse(panel_csrf_valid());
		$_POST['csrf_token'] = ['array'];
		$this->assertFalse(panel_csrf_valid());
	}

	public function test_token_in_form_or_header_is_accepted(): void
	{
		$t = panel_csrf_token();
		$_POST['csrf_token'] = $t;
		$this->assertTrue(panel_csrf_valid());
		$_POST = [];
		$_SERVER['HTTP_X_CSRF_TOKEN'] = $t;
		$this->assertTrue(panel_csrf_valid());
	}

	public function test_session_without_token_never_matches_empty(): void
	{
		$_SESSION = [];
		$_POST['csrf_token'] = '';
		$this->assertFalse(panel_csrf_valid());
	}

	public function test_only_state_changing_methods_need_a_token(): void
	{
		foreach (['GET' => false, 'HEAD' => false, 'OPTIONS' => false, 'POST' => true, 'PUT' => true, 'DELETE' => true, 'PATCH' => true] as $m => $need)
		{
			$_SERVER['REQUEST_METHOD'] = $m;
			$this->assertSame($need, panel_request_needs_csrf(), $m);
		}
	}

	public function test_idle_session_is_logged_out_on_the_server(): void
	{
		$_SESSION = [PANEL_SESSION_UID => 3, 'session_timeout' => 600, 'last-activity' => time() - 601];
		panel_session_enforce_timeout();
		$this->assertNull(panel_session_uid());
		$this->assertTrue($_SESSION['timed_out']);
	}

	public function test_active_session_stays(): void
	{
		$_SESSION = [PANEL_SESSION_UID => 3, 'session_timeout' => 600, 'last-activity' => time() - 30];
		panel_session_enforce_timeout();
		$this->assertSame(3, panel_session_uid());
	}

	public function test_logout_keeps_a_session_for_the_next_login_token(): void
	{
		/* PR #69 broke login after logout/timeout by destroying the session */
		$_SESSION = [PANEL_SESSION_UID => 3, 'csrf_token' => 'old'];
		panel_session_logout();
		$this->assertSame(PHP_SESSION_ACTIVE, session_status());
		$this->assertNull(panel_session_uid());
		$t = panel_csrf_token();
		$this->assertNotSame('old', $t);
		$_POST['csrf_token'] = $t;
		$this->assertTrue(panel_csrf_valid());
	}
}
