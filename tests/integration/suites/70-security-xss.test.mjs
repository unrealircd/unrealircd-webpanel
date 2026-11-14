// Security: data from IRC users, links and forms is shown as text, never run as HTML.
// (The pages where JavaScript inserts data are tested in a browser: 90-browser.test.mjs.)
import { describe, test } from 'node:test';
import assert from 'node:assert/strict';
import { as, enc, alerts, Client } from '../lib.mjs';

/* The planted markup, as live tags (escaped, it shows as &lt;img ... and does not match) */
const RAW = /<img[\s/]+src(=x|\/onerror)|<svg\/onload=/i;
function safe(html, what) {
  const m = html.match(new RegExp('.{0,60}(' + RAW.source + ').{0,60}', 'i'));
  assert.ok(!m, `${what}: markup from outside got into the page: ${m && m[0]}`);
}

describe('Security: script injection', () => {
  test('channel ban masks with markup are escaped (~text, +e)', async () => {
    const r = await (await as('admin')).get('channels/details.php?chan=' + enc('#evil'));
    safe(r.text, 'channel page');
    const v = r.text.match(/value="(~text:[^"]*)" name="cb_checkboxes/i);
    assert.ok(v, 'the ~text ban is listed');
    /* UnrealIRCd stores the text pattern as *...* */
    assert.match(v[1], /&#039;&gt;&lt;img\/src\/onerror=top\.XB=1&gt;\*?$/i);
  });
  test('a channel key and +L target with markup are escaped', async () => {
    const r = await (await as('admin')).get('channels/details.php?chan=' + enc('#evil'));
    assert.match(r.text, /&lt;svg\/onload=top\.XK=1&gt;/);
  });
  test('a channel topic with markup is escaped', async () => {
    const r = await (await as('admin')).get('channels/details.php?chan=' + enc('#evil'));
    assert.match(r.text, /value="&lt;img src=x onerror=top\.XT=1&gt; topic"/);
  });
  test('a channel name with markup is escaped, on its page and in user details', async () => {
    const a = await as('admin');
    safe((await a.get('channels/details.php?chan=' + enc('#<svg/onload=top.XC=1>'))).text, 'channel page');
    safe((await a.get('users/details.php?nick=chanop')).text, 'user details');
  });
  for (const [page, param] of [['users/details.php', 'nick'], ['servers/details.php', 'server'], ['channels/details.php', 'chan'], ['tools/ip-whois.php', 'ip']]) {
    test(`a link to ${page}?${param}=<markup> shows the markup as text`, async () => {
      const r = await (await as('reader')).get(`${page}?${param}=${enc('"><img src=x onerror=top.XG=1>')}`);
      safe(r.text, page);
      assert.match(r.text, /&quot;&gt;&lt;img src=x onerror=top\.XG=1&gt;/);
    });
  }
  test('the login page escapes its redirect parameter', async () => {
    const r = await new Client().get('login/?redirect=' + enc('"><img src=x onerror=top.XL=1>'));
    safe(r.text, 'login page');
  });
  test('form values shown back on the page are escaped (server name filter)', async () => {
    const r = await (await as('reader')).submit('servers/index.php', { sf_name: '<img src=x onerror=top.XP=1>' }, 'servers/');
    safe(r.text, 'servers page');
    assert.ok(alerts(r.text).includes('Listing servers which match name: "<img src=x onerror=top.XP=1>"'));
  });
  test('api/test_rpc_server.php always answers JSON', async () => {
    const r = await (await as('admin')).api('api/test_rpc_server.php', { tls_verify: 'false', host: '<img src=x onerror=top.XH=1>', port: '1', user: 'a', password: 'b', edit_existing: '' });
    assert.match(r.headers.get('content-type'), /application\/json/);
    assert.equal(r.headers.get('x-content-type-options'), 'nosniff');
  });
  test('RPC server names never end up in javascript: links or onclick code', async () => {
    const a = await as('admin');
    const name = `x');alert(1);//<b>`;
    await a.submit('settings/rpc-servers.php', { do_add_server: '1', edit_existing: '', rpc_displayname: name, rpc_host: '127.0.0.1', rpc_port: '8600', rpc_user: 'x', rpc_password: 'y' });
    const p = await a.get('settings/rpc-servers.php');
    assert.doesNotMatch(p.text, /href="javascript:edit_rpc_server|onclick="confirm_delete/);
    assert.ok(!p.text.includes(name), 'the name appears unescaped');
    assert.ok(p.text.includes('x&#039;);alert(1);//&lt;b&gt;'), 'the name is shown escaped');
    const nav = await a.get('');
    assert.doesNotMatch(nav.text, /href="javascript:change_active_server/);
    assert.ok(!nav.text.includes(name));
    await a.submit('settings/rpc-servers.php', { do_del_server: '1', del_server_name: name });
  });
  test('profile fields with markup are escaped on the Accounts page', async () => {
    const a = await as('admin');
    await a.submit('settings/index.php', { do_add_user: '1', user_add: 'xssprofile', password: 'Pr0file!pass', user_role: 'Read-Only',
      user_email: '"><b>mail', add_first_name: '<i>first</i>', add_last_name: '<u>last</u>', user_bio: '<s>bio</s>' });
    const p = await a.get('settings/index.php');
    for (const tag of ['<i>first', '<u>last', '<s>bio', '"><b>mail']) assert.ok(!p.text.includes(tag), tag);
    assert.match(p.text, /&lt;i&gt;first&lt;\/i&gt;/);
  });
  test('role names with markup are escaped in the Role Editor', async () => {
    const a = await as('admin');
    await a.submit('settings/user-role-edit.php', { add_role_name: '<b>role' });
    const p = await a.get('settings/user-role-edit.php');
    assert.ok(!p.text.includes('<b>role'));
    assert.match(p.text, /&lt;b&gt;role/);
    await a.submit('settings/user-role-edit.php', { del_role_name: '<b>role' });
  });
  test('server ban reasons with markup are returned as data, not HTML', async () => {
    const a = await as('admin');
    await a.submit('server-bans/index.php', { do_add_ban: '1', edit_existing: '', ban_host: '*@xss.example', ban_type: 'gline', ban_duration: '1h', ban_reason: '<img src=x onerror=top.XN=1>' }, 'server-bans/');
    const b = (await a.json('api/server-bans.php')).find(x => x.Mask === '*@xss.example');
    assert.equal(b.Reason, '<img src=x onerror=top.XN=1>', 'raw in the JSON: the page escapes it when it shows it');
    assert.match(b.Select, /^<input type="checkbox" value="[A-Za-z0-9+/=,]+" name="tklch\[\]">$/);
    await a.submit('server-bans/index.php', { 'tklch[]': [Buffer.from('*@xss.example').toString('base64') + ',' + Buffer.from('gline').toString('base64')] }, 'server-bans/');
  });
  test('name ban, exception and spamfilter tables escape what they show', async () => {
    const a = await as('admin');
    await a.submit('server-bans/name-bans.php', { tkl_add: '<b>nb', ban_reason: '<i>r</i>' });
    const p = await a.get('server-bans/name-bans.php');
    assert.ok(!p.text.includes('<b>nb') && !p.text.includes('<i>r</i>'));
    await a.submit('server-bans/name-bans.php', { 'tklch[]': [Buffer.from('<b>nb').toString('base64')] });
  });
  test('IP WHOIS refuses names that are not IP addresses (their WHOIS data is chosen by the owner)', async () => {
    const r = await (await as('reader')).get('tools/ip-whois.php?ip=' + enc('example.com'));
    assert.ok(alerts(r.text).includes('Lookup failed: that is not a valid IP address'));
  });
});
