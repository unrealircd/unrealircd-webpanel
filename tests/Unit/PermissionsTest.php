<?php
use PHPUnit\Framework\TestCase;

/** Roles and permissions (Classes/class-paneluser.php) */
final class PermissionsTest extends TestCase
{
	private function user(array $meta): PanelUser
	{
		$u = (new ReflectionClass(PanelUser::class))->newInstanceWithoutConstructor();
		$u->id = 5;
		$u->username = 'someone';
		$u->user_meta = $meta;
		return $u;
	}

	protected function setUp(): void
	{
		global $config;
		$config['user_roles'] = [
			'Moderator' => [PERMISSION_BAN_USERS],
			'Empty' => NULL, /* what saving a role with nothing ticked used to store */
		];
	}

	public function test_super_admin_can_everything(): void
	{
		$this->assertTrue(user_can($this->user(['role' => 'Super-Admin']), PERMISSION_MANAGE_USERS));
	}

	public function test_read_only_can_nothing(): void
	{
		$this->assertFalse(user_can($this->user(['role' => 'Read-Only']), PERMISSION_BAN_USERS));
	}

	public function test_custom_role_gets_only_its_permissions(): void
	{
		$u = $this->user(['role' => 'Moderator']);
		$this->assertTrue(user_can($u, PERMISSION_BAN_USERS));
		$this->assertFalse(user_can($u, PERMISSION_MANAGE_USERS));
	}

	public function test_unknown_role_gets_nothing(): void
	{
		$this->assertFalse(user_can($this->user(['role' => 'Deleted role']), PERMISSION_BAN_USERS));
	}

	public function test_role_with_null_permissions_does_not_crash(): void
	{
		/* used to throw a TypeError on every page for users with that role */
		$this->assertFalse(user_can($this->user(['role' => 'Empty']), PERMISSION_BAN_USERS));
	}

	public function test_old_permissions_meta_without_role_gives_nothing(): void
	{
		/* the early-2023 "default" account had no role, only this meta */
		$u = $this->user(['permissions' => serialize([PERMISSION_MANAGE_USERS])]);
		$this->assertFalse(user_can($u, PERMISSION_MANAGE_USERS));
	}

	public function test_no_default_user_code_left_in_backends(): void
	{
		$root = dirname(__DIR__, 2);
		foreach (['plugins/file_db/file_db.php', 'plugins/sql_db/sql_db.php'] as $f)
		{
			$src = file_get_contents("$root/$f");
			$this->assertStringNotContainsString("DEFAULT_USER['password']", $src, $f);
		}
	}

	public function test_new_users_are_stored_unescaped(): void
	{
		/* escaping happens on output; storing escaped values made names
		 * with '&' unable to log in and showed &amp;amp; */
		$src = file_get_contents(dirname(__DIR__, 2) . '/Classes/class-paneluser.php');
		$fn = substr($src, strpos($src, 'function create_new_user'), 1500);
		$this->assertStringNotContainsString('htmlspecialchars(', $fn);
	}
}
