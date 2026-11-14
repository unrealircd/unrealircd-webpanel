<?php
use PHPUnit\Framework\TestCase;

/** Output escaping helpers and the Message class (Script injection) */
final class EscapingTest extends TestCase
{
	public function test_h_escapes_all_html_special_characters(): void
	{
		$this->assertSame('&lt;img src=x onerror=&quot;a&#039;b&quot;&gt; &amp;', h('<img src=x onerror="a\'b"> &'));
	}

	public function test_h_keeps_text_with_invalid_utf8(): void
	{
		/* plain htmlspecialchars() returns "" for invalid UTF-8, hiding the whole value */
		$out = h("ban\xffmask");
		$this->assertStringStartsWith('ban', $out);
		$this->assertStringEndsWith('mask', $out);
	}

	public function test_h_of_null_array_object_is_empty(): void
	{
		$this->assertSame('', h(NULL));
		$this->assertSame('', h(['a']));
		$this->assertSame('', h(new stdClass()));
		$this->assertSame('0', h(0));
	}

	public function test_js_value_cannot_close_the_script_tag(): void
	{
		$js = js_value('</script><script>alert(1)</script>');
		$this->assertStringNotContainsString('</script>', $js);
		$this->assertStringNotContainsString('<', $js);
		$this->assertSame('</script><script>alert(1)</script>', json_decode($js));
	}

	public function test_message_escapes_by_default(): void
	{
		ob_start();
		Message::Fail('Listing servers which match name: "<img src=x onerror=alert(1)>"');
		$out = ob_get_clean();
		$this->assertStringNotContainsString('<img', $out);
		$this->assertStringContainsString('&lt;img src=x onerror=alert(1)&gt;', $out);
	}

	public function test_message_rawhtml_is_not_escaped(): void
	{
		ob_start();
		Message::Success(new RawHTML('<b>bold</b>'), '<i>text</i>');
		$out = ob_get_clean();
		$this->assertStringContainsString('<b>bold</b>', $out);
		$this->assertStringContainsString('&lt;i&gt;text&lt;/i&gt;', $out);
	}

	public function test_message_flattens_nested_arrays(): void
	{
		ob_start();
		Message::Fail('One or more errors occurred:', ['<a>', ['<b>']]);
		$out = ob_get_clean();
		$this->assertStringNotContainsString('Array', $out);
		$this->assertStringContainsString('&lt;a&gt;<br>&lt;b&gt;', $out);
	}

	public function test_whois_table_escapes_values_and_keys(): void
	{
		ob_start();
		generate_ip_whois_table([[ '<k>' => '<img src=x onerror=alert(1)>', 'abuse' => 'a@example.org' ]]);
		$out = ob_get_clean();
		$this->assertStringNotContainsString('<img', $out);
		$this->assertStringNotContainsString('<k>', $out);
		$this->assertStringContainsString('mailto:a@example.org', $out);
	}

	public function test_whois_only_looks_up_ip_addresses(): void
	{
		$this->assertTrue(is_valid_whois_ip('192.0.2.1'));
		$this->assertTrue(is_valid_whois_ip('2001:db8::1'));
		$this->assertFalse(is_valid_whois_ip('example.com'));
		$this->assertFalse(is_valid_whois_ip("192.0.2.1\r\nhelp"));
		$this->assertFalse(is_valid_whois_ip(['1.2.3.4']));
	}

	public function test_as_name_goes_through_h(): void
	{
		/* misc/user-lookup-misc.php prints h("$asname ($asn)") */
		$this->assertSame('&lt;b&gt;ACME&lt;/b&gt; (64496)', h('<b>ACME</b> (64496)'));
	}
}
