<?php
use PHPUnit\Framework\TestCase;

/** A stand-in for the RPC connection, recording what the panel sends */
final class FakeRpc
{
	public array $sent = [];
	public array $lists = ['bans' => [], 'ban_exemptions' => [], 'invite_exceptions' => []];
	public $error = NULL;
	public $errno = 0;
	/** how many parameters the "server" applies per mode line (UnrealIRCd: 12) */
	public int $max = 12;
	public function channel() { return $this; }
	public function set_mode($chan, $modes, $params)
	{
		$this->sent[] = [$chan, $modes, $params];
		$field = ['b' => 'bans', 'e' => 'ban_exemptions', 'I' => 'invite_exceptions'][$modes[1]];
		foreach (array_slice(explode(' ', $params), 0, $this->max) as $p)
			$this->lists[$field] = array_values(array_filter($this->lists[$field], fn($e) => $e->name !== $p));
		return true; /* like UnrealIRCd: always true */
	}
	public function get($name, $detail = 3)
	{
		return (object)array_merge(['name' => $name], $this->lists);
	}
}

/** Channel page helpers (misc/channel-lookup-misc.php) and the mode list (IRCList) */
final class ChannelHelpersTest extends TestCase
{
	protected function setUp(): void
	{
		IRCList::setmodes(['beI', 'kLf', 'l', 'psmntirzMQNRTOVKDdGPZSCc']);
	}

	private function rpcWith(array $bans): FakeRpc
	{
		global $rpc;
		$rpc = new FakeRpc();
		foreach ($bans as $b)
			$rpc->lists['bans'][] = (object)['name' => $b, 'set_by' => 'x', 'set_at' => 'now'];
		return $rpc;
	}

	public function test_setmodes_splits_a_comma_list(): void
	{
		IRCList::setmodes('beI,k,l,imnt');
		$this->assertSame(['beI', 'k', 'l', 'imnt'], IRCList::$uplink);
	}

	public function test_mode_groups(): void
	{
		$this->assertSame(1, get_chmode_group('b'));
		$this->assertSame(2, get_chmode_group('k'));
		$this->assertSame(3, get_chmode_group('l'));
		$this->assertSame(4, get_chmode_group('m'));
		$this->assertSame(0, get_chmode_group('9'));
	}

	public function test_lookup_known_and_unknown_modes(): void
	{
		$this->assertSame('Ban', IRCList::lookup('b')['name']);
		$this->assertSame('Unknown mode', IRCList::lookup('9')['name']);
	}

	public function test_group_modes_puts_unknown_letters_in_other(): void
	{
		IRCList::setmodes(['beI', 'k', 'l', 'mntY']);
		$this->assertStringContainsString('Y', group_modes()['Other']);
	}

	public function test_sort_paramed_modes(): void
	{
		$this->assertSame(['k' => 'secret', 'l' => '10'], sort_paramed_modes('ntkl', 'secret 10'));
	}

	public function test_sort_paramed_modes_with_key_zero(): void
	{
		$this->assertSame(['k' => '0', 'l' => '5'], sort_paramed_modes('kl', '0 5'));
	}

	public function test_sort_user_list_by_rank(): void
	{
		$u = fn($n, $l = NULL) => (object)($l === NULL ? ['name' => $n] : ['name' => $n, 'level' => $l]);
		$sorted = sort_user_list([$u('plain'), $u('voice', 'v'), $u('owner', 'q'), $u('op', 'o'), $u('half', 'h'), $u('admin', 'a')]);
		$this->assertSame(['owner', 'admin', 'op', 'half', 'voice', 'plain'], array_map(fn($x) => $x->name, $sorted));
	}

	public function test_mode_table_shows_parameters_as_text(): void
	{
		ob_start();
		generate_html_chansettings((object)['modes' => 'ntk <svg/onload=alert(1)>']);
		$out = ob_get_clean();
		$this->assertStringContainsString('Key', $out);
		$this->assertStringNotContainsString('<svg', $out);
		$this->assertStringContainsString('&lt;svg/onload=alert(1)&gt;', $out);
	}

	public function test_ban_table_puts_masks_in_quoted_escaped_values(): void
	{
		$this->rpcWith(["~text:block:'><img/src/onerror=alert(1)>"]);
		ob_start();
		generate_chanbans_table((object)['name' => '#c']);
		$out = ob_get_clean();
		$this->assertStringNotContainsString('<img', $out);
		$this->assertStringContainsString('value="~text:block:&#039;&gt;&lt;img/src/onerror=alert(1)&gt;"', $out);
	}

	public function test_removing_bans_sends_masks_unescaped(): void
	{
		$rpc = $this->rpcWith(['a&b!*@*', '"q"!*@*']);
		$errors = [];
		ob_start();
		$ok = _do_chan_item_delete((object)['name' => '#c'], 'ban', ['a&b!*@*', '"q"!*@*'], $errors);
		ob_end_clean();
		$this->assertTrue($ok);
		$this->assertSame('a&b!*@* "q"!*@*', $rpc->sent[0][2]);
		$this->assertSame([], $rpc->lists['bans']);
	}

	public function test_removing_many_bans_is_sent_in_batches_of_12(): void
	{
		$masks = array_map(fn($i) => "m$i!*@*", range(1, 30));
		$rpc = $this->rpcWith($masks);
		$errors = [];
		ob_start();
		$ok = _do_chan_item_delete((object)['name' => '#c'], 'ban', $masks, $errors);
		$out = ob_get_clean();
		$this->assertTrue($ok, $out);
		$this->assertCount(3, $rpc->sent);
		foreach ($rpc->sent as $s)
			$this->assertLessThanOrEqual(12, count(explode(' ', $s[2])));
		$this->assertSame([], $rpc->lists['bans']);
		$this->assertStringContainsString('Deleted successfully', $out);
	}

	public function test_entries_that_stay_are_reported_not_called_deleted(): void
	{
		$rpc = $this->rpcWith(['keep!*@*', 'gone!*@*']);
		$rpc->max = 0; /* the server removes nothing, eg an entry with invalid UTF-8 */
		$errors = [];
		ob_start();
		$ok = _do_chan_item_delete((object)['name' => '#c'], 'ban', ['keep!*@*'], $errors);
		$out = ob_get_clean();
		$this->assertFalse($ok);
		$this->assertStringContainsString('could not be removed: keep!*@*', $out);
		$this->assertStringNotContainsString('Deleted successfully', $out);
	}

	public function test_removing_with_nothing_selected(): void
	{
		$this->rpcWith([]);
		$errors = [];
		ob_start();
		$ok = _do_chan_item_delete((object)['name' => '#c'], 'ban', [], $errors);
		$out = ob_get_clean();
		$this->assertFalse($ok);
		$this->assertStringContainsString('Nothing was selected', $out);
	}
}
