import { describe, test } from 'node:test';
import assert from 'node:assert/strict';
import { as, alerts, noPhpErrors, Irc, sleep } from '../lib.mjs';

describe('IRC users', () => {
  test('the Users page loads', async () => {
    const r = await (await as('admin')).get('users/');
    assert.equal(r.status, 200);
    noPhpErrors(r.text);
    assert.match(r.text, /Users Overview/);
  });
  test('the user list API lists the online users', async () => {
    const list = await (await as('admin')).json('api/users.php');
    const nicks = list.map(u => u.nick_raw);
    for (const n of ['chanop', 'httpuser', 'gecosuser']) assert.ok(nicks.includes(n), n);
    const u = list.find(x => x.nick_raw === 'chanop');
    assert.match(u.Nick, /details\.php\?nick=/);
    assert.match(u.Select, /name="userch\[\]"/);
    assert.match(u['Connected to'], /irc\.test/);
  });
  test('the user list API escapes IRC data in its HTML columns', async () => {
    const list = await (await as('admin')).json('api/users.php');
    for (const u of list) for (const k of ['Nick', 'Host/IP', 'Account', 'Oper', 'Connected to', 'Reputation'])
      assert.doesNotMatch(String(u[k]), /<img|<svg|onerror/i, `${u.nick_raw}.${k}`);
  });
  test('user details show the user\'s info and channels', async () => {
    const r = await (await as('admin')).get('users/details.php?nick=chanop');
    noPhpErrors(r.text);
    assert.match(r.text, /User Lookup for &quot;chanop&quot;/);
    assert.match(r.text, /<code>chanop<\/code>/);
    assert.match(r.text, /channels\/details\.php\?chan=%23evil/);
    assert.match(r.text, /irc\.test/);
  });
  test('user details show the realname as text', async () => {
    const r = await (await as('admin')).get('users/details.php?nick=gecosuser');
    assert.match(r.text, /&lt;img src=x onerror=top\.XU=1&gt;/);
    assert.doesNotMatch(r.text, /<img src=x onerror=top\.XU=1>/);
  });
  test('an unknown nick says the user was not found', async () => {
    const r = await (await as('admin')).get('users/details.php?nick=nobody-online');
    assert.ok(alerts(r.text).includes('Could not find user: "nobody-online"'));
  });
  test('kill a user from the Users page', async () => {
    const victim = new Irc('killme'); await victim.connect();
    const closed = new Promise(r => victim.sock.once('close', r));
    const a = await as('admin');
    const list = await a.json('api/users.php');
    const id = list.find(u => u.nick_raw === 'killme').Select.match(/value="([^"]+)"/)[1];
    const r = await a.submit('users/index.php', { bantype: 'kill', 'userch[]': [id], ban_reason: 'test kill', banlen_w: '0w', banlen_d: '0d', banlen_h: '0d' }, 'users/');
    assert.match(alerts(r.text).join(), /killme.*has been killed: test kill/);
    await Promise.race([closed, sleep(10000).then(() => assert.fail('the IRC connection was not closed'))]);
  });
  test('a Read-Only user sees the list but cannot apply actions', async () => {
    const reader = await as('reader');
    const list = await reader.json('api/users.php');
    assert.ok(list.length > 0);
    const id = list[0].Select.match(/value="([^"]+)"/)[1];
    const before = list.length;
    await reader.submit('users/index.php', { bantype: 'kill', 'userch[]': [id], ban_reason: 'nope' }, 'users/');
    assert.equal((await reader.json('api/users.php')).length, before);
  });
});
