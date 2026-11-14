<?php
use PHPUnit\Framework\TestCase;

/** Handling of IRC data: names, mode parameters, RPC host (Channel handling) */
final class IrcDataTest extends TestCase
{
	public function test_irc_name_html_escapes_markup(): void
	{
		$out = irc_name_html('#<svg/onload=alert(1)>');
		$this->assertStringNotContainsString('<svg', $out);
		$this->assertStringContainsString('#&lt;svg/onload=alert(1)&gt;', $out);
	}

	public function test_zero_width_space_is_shown(): void
	{
		$out = irc_name_html("#pay\u{200B}pal");
		$this->assertStringContainsString('U+200B', $out);
		$this->assertStringNotContainsString("\u{200B}", $out);
	}

	public function test_bidi_control_is_shown(): void
	{
		$this->assertStringContainsString('U+202E', irc_name_html("#abc\u{202E}def"));
	}

	public function test_mixed_latin_cyrillic_gets_a_badge(): void
	{
		$this->assertStringContainsString('mixed scripts', irc_name_html("#p\u{0430}ypal"));
		$this->assertStringNotContainsString('mixed scripts', irc_name_html('#paypal'));
		$this->assertStringNotContainsString('mixed scripts', irc_name_html("#\u{043F}\u{0440}\u{0438}\u{0432}\u{0435}\u{0442}"));
	}

	public function test_invalid_utf8_name_gets_a_badge(): void
	{
		$this->assertStringContainsString('invalid UTF-8', irc_name_html("#bad\u{FFFD}"));
		$this->assertStringContainsString('invalid UTF-8', irc_name_html("#bad\xff"));
	}

	public function test_glue_keeps_the_string_zero(): void
	{
		$this->assertSame('0 5', glue(['0', '5']));
		$this->assertSame('a b', glue([NULL, 'a', '', 'b']));
	}

	public function test_mode_parameters_with_key_zero(): void
	{
		/* What sort_paramed_modes() does with "+kl 0 5" */
		$params = '0 5';
		$p = split($params);
		$this->assertSame('0', $p[0]);
		$p[0] = NULL;
		$this->assertSame('5', glue($p));
	}

	public function test_rpc_host_validation(): void
	{
		foreach (['127.0.0.1', 'irc.example.org', 'ircd', '2001:db8::1', 'a-b_c.example'] as $ok)
			$this->assertTrue(is_valid_rpc_host($ok), $ok);
		foreach (['', '<img src=x>', 'host/path', 'host:1', "host\n", 'a b', ['x']] as $bad)
			$this->assertFalse(is_valid_rpc_host($bad), json_encode($bad));
	}
}
