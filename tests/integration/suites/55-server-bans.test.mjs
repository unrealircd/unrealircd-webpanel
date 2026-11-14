import { describe, test } from 'node:test';
import assert from 'node:assert/strict';
import { as, alerts, noPhpErrors } from '../lib.mjs';

const b64 = (s) => Buffer.from(s).toString('base64');

describe('Server bans', () => {
  test('the Server Bans page loads', async () => {
    const r = await (await as('admin')).get('server-bans/');
    noPhpErrors(r.text);
    assert.match(r.text, /Server Bans Overview/);
  });
  test('add a G-Line; it is listed with its raw mask and reason', async () => {
    const a = await as('admin');
    const r = await a.submit('server-bans/index.php', { do_add_ban: '1', edit_existing: '', ban_host: '*@gline.example', ban_type: 'gline', ban_duration: '1d', ban_reason: 'spam & <abuse>' }, 'server-bans/');
    assert.ok(alerts(r.text).includes('Ban added on *@gline.example'));
    const b = (await a.json('api/server-bans.php')).find(x => x.Mask === '*@gline.example');
    assert.ok(b);
    assert.equal(b.Reason, 'spam & <abuse>');
    assert.equal(b.Type, 'G-Line');
  });
  test('a host without "@" gets "*@" in front', async () => {
    const a = await as('admin');
    const r = await a.submit('server-bans/index.php', { do_add_ban: '1', edit_existing: '', ban_host: 'nohost.example', ban_type: 'kline', ban_duration: '', ban_reason: 'k' }, 'server-bans/');
    assert.ok(alerts(r.text).includes('Ban added on *@nohost.example'));
  });
  test('a soft ban gets "%" in front', async () => {
    const a = await as('admin');
    const r = await a.submit('server-bans/index.php', { do_add_ban: '1', edit_existing: '', ban_host: '*@soft.example', ban_type: 'gline', ban_soft: 'on', ban_duration: '1h', ban_reason: 's' }, 'server-bans/');
    assert.ok(alerts(r.text).includes('Ban added on %*@soft.example'));
  });
  test('edit a ban (change the reason)', async () => {
    const a = await as('admin');
    const r = await a.submit('server-bans/index.php', { do_add_ban: '1', edit_existing: 'gline:*@gline.example', ban_host: '*@gline.example', ban_type: 'gline', ban_duration: '1d', ban_reason: 'changed reason' }, 'server-bans/');
    assert.ok(alerts(r.text).includes('Ban successfully modified: *@gline.example'));
    assert.equal((await a.json('api/server-bans.php')).find(x => x.Mask === '*@gline.example').Reason, 'changed reason');
  });
  test('missing host or type is refused', async () => {
    const r = await (await as('admin')).submit('server-bans/index.php', { do_add_ban: '1', ban_host: '', ban_type: 'gline' }, 'server-bans/');
    assert.ok(alerts(r.text).includes('Unable to add Server Ban: No host or ban type selected'));
  });
  test('remove server bans', async () => {
    const a = await as('admin');
    const r = await a.submit('server-bans/index.php', { 'tklch[]': [b64('*@gline.example') + ',' + b64('gline'), b64('*@nohost.example') + ',' + b64('kline')] }, 'server-bans/');
    assert.ok(alerts(r.text).includes('gline has been removed for *@gline.example'));
    assert.ok(alerts(r.text).includes('kline has been removed for *@nohost.example'));
    const masks = (await a.json('api/server-bans.php')).map(x => x.Mask);
    assert.ok(!masks.includes('*@gline.example'));
  });
  test('Name Bans: add and remove', async () => {
    const a = await as('admin');
    const add = await a.submit('server-bans/name-bans.php', { tkl_add: 'BadNick*', banlen_w: '0w', banlen_d: '1d', banlen_h: '0d', ban_reason: 'reserved' });
    assert.ok(alerts(add.text).includes('Name Ban set against "BadNick*": reserved'));
    assert.match(add.text, /<td scope="col">BadNick\*<\/td>/);
    const del = await a.submit('server-bans/name-bans.php', { 'tklch[]': [b64('BadNick*')] });
    assert.ok(alerts(del.text).includes('Name Ban has been removed for BadNick*'));
  });
  test('Ban Exceptions: add and remove', async () => {
    const a = await as('admin');
    const add = await a.submit('server-bans/ban-exceptions.php', { tkl_add: '*@trusted.example', 'bantype[]': ['k', 'G'], banlen_w: '0w', banlen_d: '0d', banlen_h: '0d', ban_reason: 'trusted' });
    assert.ok(alerts(add.text).includes('Ban Exception set against "*@trusted.example": trusted'));
    assert.match(add.text, /K-Line<\/span><span class="rounded-pill badge badge-info">G-Line/);
    const del = await a.submit('server-bans/ban-exceptions.php', { 'tklch[]': [b64('*@trusted.example') + ',' + b64('except')] });
    assert.ok(alerts(del.text).includes('Ban Exception has been removed for *@trusted.example'));
  });
  test('Spamfilter: add and remove', async () => {
    const a = await as('admin');
    const add = await a.submit('server-bans/spamfilter.php', { sf_add: '*buy cheap*', matchtype: 'simple', target_usermsg: 'on', target_chanmsg: 'on', sf_bantype: 'block', banlen_w: '0w', banlen_d: '0d', banlen_h: '0d', ban_reason: 'spam' });
    assert.ok(alerts(add.text).includes('Added spamfilter entry "*buy cheap*" [match type: simple] [targets: pc] [reason: spam]'));
    const box = add.text.match(/value="([^"]+)" name="sf\[\]"[^>]*>[\s\S]{0,200}?\*buy cheap\*/);
    assert.ok(box, 'listed with a checkbox');
    const del = await a.submit('server-bans/spamfilter.php', { 'sf[]': [box[1]] });
    assert.ok(alerts(del.text).includes('Spamfilter on *buy cheap* has been removed'));
  });
  test('Spamfilter: no target or no action is refused', async () => {
    const r = await (await as('admin')).submit('server-bans/spamfilter.php', { sf_add: 'x', matchtype: 'simple', sf_bantype: 'block' });
    assert.ok(alerts(r.text).includes('No target was specified'));
  });
  test('a Read-Only user cannot add or remove bans', async () => {
    const reader = await as('reader');
    const r = await reader.submit('server-bans/index.php', { do_add_ban: '1', ban_host: '*@reader.example', ban_type: 'gline', ban_reason: 'x' }, 'server-bans/');
    assert.ok(alerts(r.text).includes('Could not add: Permission denied'));
    const n = await reader.submit('server-bans/name-bans.php', { tkl_add: 'x', ban_reason: 'x' });
    assert.ok(alerts(n.text).includes('Could not add name ban(s): Permission denied'));
    const s = await reader.submit('server-bans/spamfilter.php', { sf_add: 'x', matchtype: 'simple', target_usermsg: 'on', sf_bantype: 'block' });
    assert.ok(alerts(s.text).includes('Could not add Spamfilter entry: Permission denied'));
  });
});
