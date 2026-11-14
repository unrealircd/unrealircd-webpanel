import { describe, test, before, after } from 'node:test';
import assert from 'node:assert/strict';
import { as, enc, alerts, noPhpErrors, Irc, sleep } from '../lib.mjs';

const CH = '#suite';
const url = (ch = CH) => 'channels/details.php?chan=' + enc(ch);
let op, guest;

describe('Channels', () => {
  before(async () => {
    op = new Irc('suiteop'); await op.connect();
    await op.join(CH);
    op.raw(`TOPIC ${CH} :hello from the test`);
    guest = new Irc('suiteguest'); await guest.connect();
    await guest.join(CH);
    await sleep(500);
  });
  after(() => { op?.close(); guest?.close(); });

  test('the Channels page loads', async () => {
    const r = await (await as('admin')).get('channels/');
    noPhpErrors(r.text);
    assert.match(r.text, /Channels Overview/);
  });
  test('the channel list API gives raw names, user counts, modes and topics', async () => {
    const list = await (await as('admin')).json('api/channels.php');
    const c = list.find(x => x.Name === CH);
    assert.ok(c, 'channel listed');
    assert.equal(c.Users, 2);
    assert.equal(c.Topic, 'hello from the test');
    assert.match(c.Modes, /^\+/);
    assert.match(c.Created, /^\d{4}-\d\d-\d\d$/);
  });
  test('channel names with "&" are listed as they are (not escaped)', async () => {
    const names = (await (await as('admin')).json('api/channels.php')).map(x => x.Name);
    assert.ok(names.includes('#a&b'));
    assert.ok(names.includes('#a&amp;b'));
  });
  test('opening "#a&b" by its listed name shows that channel, not its look-alike', async () => {
    const r = await (await as('admin')).get(url('#a&b'));
    assert.match(r.text, /I am the REAL channel/);
  });
  test('channel details show topic, users and modes', async () => {
    const r = await (await as('admin')).get(url());
    noPhpErrors(r.text);
    assert.match(r.text, /value="hello from the test"/);
    assert.match(r.text, /suiteop/);
    assert.match(r.text, /suiteguest/);
    assert.match(r.text, /badge-warning'>Op</);
  });
  test('an unknown channel says it was not found', async () => {
    const r = await (await as('admin')).get(url('#does-not-exist'));
    assert.ok(alerts(r.text).includes('Could not find channel: "#does-not-exist"'));
  });
  test('change the topic', async () => {
    const r = await (await as('admin')).submit(url(), { update_topic: 'true', set_topic: 'new topic & <more>' });
    assert.ok(alerts(r.text).includes(`The topic for ${CH} has been updated to be: "new topic & <more>"`));
    await op.wait(/ TOPIC #suite :new topic & <more>/, 10000, false).catch(() => {});
    assert.ok(op.lines.some(l => / TOPIC #suite :new topic & <more>/.test(l)), 'IRC saw the new topic');
  });
  for (const [field, mode, label] of [['add_chban', 'b', 'ban'], ['add_chex', 'e', 'exception'], ['add_chinv', 'I', 'invite']]) {
    test(`add and remove a channel ${label} (+${mode})`, async () => {
      const a = await as('admin');
      const mask = `t${mode}${Date.now() % 1000}!*@*`;
      const add = await a.submit(url(), { [field]: mode, ban_nick: mask, bantype_sel_action: '', bantype_sel_type: '', bantype_sel_ex: '' });
      assert.ok(alerts(add.text).includes(`The mode was set successfully: ${mode} ${mask}`));
      assert.ok((await op.list(CH, mode)).includes(mask), 'set on IRC');
      const box = { b: 'cb_checkboxes[]', e: 'ce_checkboxes[]', I: 'ci_checkboxes[]' }[mode];
      const del = { b: 'delete_sel_ban', e: 'delete_sel_ex', I: 'delete_sel_inv' }[mode];
      const r = await a.submit(url(), { [del]: '1', [box]: [mask] });
      assert.ok(alerts(r.text).includes('Deleted successfully'));
      assert.ok(!(await op.list(CH, mode)).includes(mask), 'removed on IRC');
    });
  }
  test('add a timed, extended ban (mute an account)', async () => {
    const a = await as('admin');
    const until = new Date(Date.now() + 2 * 3600 * 1000);
    const local = new Date(until.getTime() - until.getTimezoneOffset() * 60000).toISOString().slice(0, 16);
    const add = await a.submit(url(), { add_chban: 'b', ban_nick: 'someacct', bantype_sel_action: 'Quiet (Mute)', bantype_sel_type: 'Match Account', bantype_sel_ex: local });
    assert.match(alerts(add.text).join(), /The mode was set successfully: b ~time:\d+:~quiet:~account:someacct/);
  });
  test('after changing a list, the page opens on that list', async () => {
    const r = await (await as('admin')).submit(url(), { delete_sel_ban: '1', 'cb_checkboxes[]': ['nothing!*@*'] });
    assert.match(r.text, /\$\('\.nav-tabs a\[href="#chanbans"\]'\)\.tab\('show'\)/);
    const plain = await (await as('admin')).get(url());
    assert.match(plain.text, /\$\('\.nav-tabs a\[href="#chanmodes"\]'\)\.tab\('show'\)/);
  });
  test('a mask with "&" can be removed', async () => {
    op.raw(`MODE ${CH} +b a&b!*@*`); await sleep(500);
    const r = await (await as('admin')).submit(url(), { delete_sel_ban: '1', 'cb_checkboxes[]': ['a&b!*@*'] });
    assert.ok(alerts(r.text).includes('Deleted successfully'));
    assert.ok(!(await op.list(CH)).includes('a&b!*@*'));
  });
  test('removing 15 bans at once removes all 15', async () => {
    const bulk = Array.from({ length: 15 }, (_, i) => `bulk${i}!*@*`);
    op.raw(`MODE ${CH} +bbbbbbbbbbbb ` + bulk.slice(0, 12).join(' '));
    op.raw(`MODE ${CH} +bbb ` + bulk.slice(12).join(' '));
    await sleep(1000);
    assert.equal((await op.list(CH)).filter(x => x.startsWith('bulk')).length, 15);
    const r = await (await as('admin')).submit(url(), { delete_sel_ban: '1', 'cb_checkboxes[]': bulk });
    assert.ok(alerts(r.text).includes('Deleted successfully'));
    assert.equal((await op.list(CH)).filter(x => x.startsWith('bulk')).length, 0);
  });
  test('an entry that cannot be removed (invalid UTF-8) is reported, not called deleted', async () => {
    op.rawBytes(Buffer.concat([Buffer.from(`MODE ${CH} +b x`), Buffer.from([0xff]), Buffer.from('y!*@*')]));
    await sleep(500);
    const a = await as('admin');
    const p = await a.get(url());
    const val = p.text.match(/value="(x[^"]*y!\*@\*)" name="cb_checkboxes/)[1].replace(/&amp;/g, '&');
    const r = await a.submit(url(), { delete_sel_ban: '1', 'cb_checkboxes[]': [val] });
    assert.match(alerts(r.text).join(), /could not be removed/);
    assert.ok(!alerts(r.text).includes('Deleted successfully'));
  });
  test('change channel modes with the mode editor', async () => {
    const a = await as('admin');
    const r = await a.submit(url(), { 'newmodes[]': ['n', 't', 'm', 'l'], 'paramed_modes[l]': '20' });
    assert.ok(alerts(r.text).includes('Channel settings have been successfully updated'));
    const list = await a.json('api/channels.php');
    assert.match(list.find(x => x.Name === CH).ModesFull, /^\+[a-zA-Z]*m[a-zA-Z]* 20$/);
  });
  test('a key of "0" is shown as 0, and the limit after it stays right', async () => {
    const r = await (await as('admin')).get(url('#keyzero'));
    assert.match(r.text, /name="paramed_modes\[k\]"\s*id="k" value="0"/);
    assert.match(r.text, /name="paramed_modes\[l\]"\s*id="l" value="5"/);
  });
  test('kick-ban a user from the channel page', async () => {
    const a = await as('admin');
    const kicked = guest.wait(/ KICK #suite suiteguest/, 15000, false);
    const r = await a.submit(url(), { kickban: 'suiteguest' });
    /* DEFAULT_CHAN_DETAIL_QUICK_ACTION_TIME in inc/defines.php, 60 minutes */
    assert.ok(alerts(r.text).includes('User "suiteguest" has been banned for 60 minutes.'), JSON.stringify(alerts(r.text)));
    await kicked;
  });
  test('a channel whose name has invalid UTF-8 is shown read-only, with a warning', async () => {
    const r = await (await as('admin')).get(url('#bad�'));
    assert.match(alerts(r.text).join(), /not valid UTF-8/);
    assert.match(r.text, /id="editlol" class="btn btn-sm btn-primary" disabled/);
  });
  test('a topic with invalid UTF-8 gets a warning before saving', async () => {
    const r = await (await as('admin')).get(url('#topic'));
    assert.match(alerts(r.text).join(), /This topic contains bytes that are not valid UTF-8/);
  });
  test('look-alike channel names are marked', async () => {
    const a = await as('admin');
    assert.match((await a.get(url('#pay​pal'))).text, /U\+200B/);
    assert.match((await a.get(url('#pаypal'))).text, /mixed scripts/);
    assert.doesNotMatch((await a.get(url('#paypal'))).text, /mixed scripts|U\+200B/);
  });
  test('a Read-Only user cannot change a channel', async () => {
    const r = await (await as('reader')).submit(url(), { update_topic: 'true', set_topic: 'reader topic' });
    assert.ok(alerts(r.text).includes('You do not have permission to manage channels.'));
  });
});
