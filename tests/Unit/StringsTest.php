<?php
use PHPUnit\Framework\TestCase;

/** The small string helpers in misc/strings.php */
final class StringsTest extends TestCase
{
	public function test_split_on_spaces_and_other_delimiters(): void
	{
		$this->assertSame(['a', 'b', 'c'], split('a b c'));
		$this->assertSame(['x', 'y'], split('x,y', ','));
		$this->assertSame([''], split(''));
	}

	public function test_glue_joins_and_skips_removed_items(): void
	{
		$this->assertSame('a b c', glue(['a', 'b', 'c']));
		$this->assertSame('a/c', glue(['a', NULL, 'c'], '/'));
		$this->assertSame('', glue([]));
	}

	public function test_rparv_drops_the_first_word(): void
	{
		$this->assertSame('5 key', rparv('lk 5 key'));
		$this->assertFalse(rparv('nt'));
	}

	public function test_badptr(): void
	{
		$a = ''; $b = NULL; $c = 'x';
		$this->assertTrue(BadPtr($a));
		$this->assertTrue(BadPtr($b));
		$this->assertFalse(BadPtr($c));
	}

	public function test_show_nick_only(): void
	{
		$this->assertSame('nick', show_nick_only('nick!user@host'));
		$this->assertSame('server.name', show_nick_only('server.name'));
	}

	public function test_to_slug(): void
	{
		$this->assertSame('super_admin', to_slug('Super-Admin'));
		$this->assertSame('read_only_role', to_slug('Read Only Role'));
	}

	public function test_strip_control_characters(): void
	{
		$this->assertSame('bold red under', StripControlCharacters("\x02bold\x02 \x0304,01red\x03 \x1Funder\x1F"));
	}

	public function test_strcat_and_strlcat(): void
	{
		$s = 'ab';
		strcat($s, 'cd');
		$this->assertSame('abcd', $s);
		strlcat($s, 'efgh', 6);
		$this->assertSame('abcdef', $s);
	}

	public function test_how_long_ago(): void
	{
		$this->assertSame('2 minutes ago', how_long_ago(date('Y-m-d H:i:s', time() - 125)));
		$this->assertSame('1 hour ago', how_long_ago(date('Y-m-d H:i:s', time() - 3700)));
		$this->assertSame('3 days ago', how_long_ago(date('Y-m-d H:i:s', time() - 3 * 86400 - 10)));
	}

	public function test_time_of_day_is_one_of_three(): void
	{
		$this->assertContains(time_of_day(), ['morning', 'afternoon', 'evening']);
	}
}
