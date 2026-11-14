<?php
use PHPUnit\Framework\TestCase;

/** The login page's redirect check (F04) */
final class LoginRedirectTest extends TestCase
{
	private const BASE = '/unrealircd-webpanel/';

	public static function refused(): array
	{
		return [
			'absolute URL' => ['https://evil.example/'],
			'scheme relative' => ['//evil.example/'],
			'TAB dropped by browsers' => ["/\t/evil.example/"],
			'newline' => ["/unrealircd-webpanel/\n"],
			'backslash' => ['/\\evil.example/'],
			'backslash inside base' => ['/unrealircd-webpanel/\\\\evil.example'],
			'outside the panel' => ['/other-app/'],
			'javascript' => ['javascript:alert(1)'],
			'markup' => ['"><img src=x onerror=alert(1)>'],
			'space' => ['/unrealircd-webpanel/ x'],
		];
	}

	/** @dataProvider refused */
	public function test_redirect_is_refused(string $target): void
	{
		$this->assertSame(self::BASE, panel_safe_redirect($target, self::BASE));
	}

	public function test_redirect_inside_panel_is_kept(): void
	{
		$this->assertSame('/unrealircd-webpanel/users/details.php?nick=abc', panel_safe_redirect('/unrealircd-webpanel/users/details.php?nick=abc', self::BASE));
	}

	public function test_redirect_with_panel_at_web_root(): void
	{
		$this->assertSame('/channels/', panel_safe_redirect('/channels/', '/'));
		$this->assertSame('/', panel_safe_redirect("/\t/evil.example/", '/'));
		$this->assertSame('/', panel_safe_redirect('//evil.example/', '/'));
	}

	public function test_redirect_with_absolute_base_url(): void
	{
		$base = 'https://panel.example.org/panel/';
		$this->assertSame('/panel/logs/', panel_safe_redirect('/panel/logs/', $base));
		$this->assertSame($base, panel_safe_redirect('https://evil.example/panel/', $base));
	}

	public function test_redirect_of_non_string_is_refused(): void
	{
		$this->assertSame(self::BASE, panel_safe_redirect(['x'], self::BASE));
		$this->assertSame(self::BASE, panel_safe_redirect('', self::BASE));
	}
}
