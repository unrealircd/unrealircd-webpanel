<?php
use PHPUnit\Framework\TestCase;

/** Install state checks (inc/install-checks.php) */
final class InstallChecksTest extends TestCase
{
	private string $root;

	protected function setUp(): void
	{
		$this->root = sys_get_temp_dir() . '/panel-root-' . getmypid() . '-' . bin2hex(random_bytes(4));
		mkdir($this->root . '/config', 0700, true);
		mkdir($this->root . '/data', 0700, true);
		foreach (['config/.htaccess', 'config/.gitignore', 'config/index.php', 'config/compat.php', 'data/.htaccess', 'data/index.php'] as $f)
			touch($this->root . "/$f");
	}

	protected function tearDown(): void
	{
		$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->root, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
		foreach ($it as $f)
			$f->isDir() ? rmdir($f) : unlink($f);
		rmdir($this->root);
	}

	public function test_fresh_install_is_not_configured(): void
	{
		$this->assertFalse(panel_is_configured($this->root));
	}

	public function test_new_config_counts_as_configured(): void
	{
		touch($this->root . '/config/config.php');
		$this->assertTrue(panel_is_configured($this->root));
	}

	public function test_old_root_config_counts_as_configured(): void
	{
		/* the installer used to look only at config/config.php */
		touch($this->root . '/config.php');
		$this->assertTrue(panel_is_configured($this->root));
	}

	public function test_login_exemption_needs_the_constant(): void
	{
		/* not a file name test anymore: an install dir "adminlogin" no longer matches */
		$_SERVER['SCRIPT_FILENAME'] = '/var/www/adminlogin/index.php';
		$this->assertFalse(page_requires_no_login());
		$this->assertFalse(page_requires_no_config());
	}

	public function test_no_warning_on_a_clean_install(): void
	{
		touch($this->root . '/config/config.php');
		touch($this->root . '/data/database.php');
		touch($this->root . '/config/config.tmp.0123456789abcdef.php');
		$this->assertSame([], panel_find_exposed_secret_copies($this->root));
	}

	public function test_backup_copies_are_found(): void
	{
		foreach (['config/config.php~', 'config/config.php.bak', 'config/.config.php.swp', 'data/database.php.orig', 'config.php~'] as $f)
			touch($this->root . "/$f");
		$found = panel_find_exposed_secret_copies($this->root);
		sort($found);
		$this->assertSame(['config.php~', 'config/.config.php.swp', 'config/config.php.bak', 'config/config.php~', 'data/database.php.orig'], $found);
	}
}
