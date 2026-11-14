// Runs last: a GZ-Line on a test user also hits every other test client
// (they all come from the same Docker gateway IP). The Z-line is removed again.
import { describe, test, after } from 'node:test';
import assert from 'node:assert/strict';
import { as, alerts } from '../lib.mjs';

async function removeZlines(a) {
  for (const b of await a.json('api/server-bans.php')) if (/Z-Line/i.test(b.Type || '')) {
    const type = /Global/i.test(b.Type) ? 'gzline' : 'zline';
    await a.submit('server-bans/index.php', { 'tklch[]': [Buffer.from(b.Mask).toString('base64') + ',' + Buffer.from(type).toString('base64')] }, 'server-bans/');
  }
}

describe('IRC users', () => {
  after(async () => { try { await removeZlines(await as('admin')); } catch { } });

  test('the Users page shows who and what will be banned before Apply', async () => {
    const p = await (await as('admin')).get('users/');
    assert.match(p.text, /id="ban_confirmation_targets"/);
    assert.match(p.text, /manual-ban-target/);
  });
  test('a GZ-Line from the Users page says which mask was really banned', async () => {
    const a = await as('admin');
    const u = (await a.json('api/users.php')).find(x => x.nick_raw === 'banme');
    assert.ok(u, 'test user banme is online');
    const id = u.Select.match(/value="([^"]+)"/)[1];
    const r = await a.submit('users/index.php', { bantype: 'gzline', 'userch[]': [id], banlen_w: '0w', banlen_d: '0d', banlen_h: '1h', ban_reason: 'test' }, 'users/');
    const msg = alerts(r.text).join();
    assert.match(msg, /banme has been gzlined for .* on \*@\d+\.\d+\.\d+\.\d+: test/);
    const bans = await a.json('api/server-bans.php');
    assert.ok(bans.some(b => /Z-Line/i.test(b.Type) && msg.includes(b.Mask)), 'the message names the mask that is in the ban list');
    await removeZlines(a);
  });
});
