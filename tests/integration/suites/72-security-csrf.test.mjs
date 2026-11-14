// Security: requests that another website could make the browser send.
// They carry the session cookie but no CSRF token, and must change nothing.
import { describe, test } from 'node:test';
import assert from 'node:assert/strict';
import { as, enc, Client } from '../lib.mjs';

const XSITE = { headers: { origin: 'https://evil.example', referer: 'https://evil.example/' } };

describe('Security: forged requests', () => {
  test('creating a panel user without the token is refused', async () => {
    const a = await as('admin');
    const r = await a.post('settings/index.php', { do_add_user: '1', user_add: 'forged', password: 'Pwn3d!pass', user_role: 'Super-Admin',
      user_email: 'x@example.test', add_first_name: '', add_last_name: '', user_bio: '' }, XSITE);
    assert.equal(r.status, 403);
    assert.doesNotMatch((await a.get('settings/index.php')).text, />forged</);
  });
  test('adding an RPC server without the token is refused', async () => {
    const a = await as('admin');
    const r = await a.post('settings/rpc-servers.php', { do_add_server: '1', edit_existing: '', rpc_displayname: 'forged', rpc_host: '127.0.0.1', rpc_port: '8600', rpc_user: 'x', rpc_password: 'y' }, XSITE);
    assert.equal(r.status, 403);
    assert.doesNotMatch((await a.get('settings/rpc-servers.php')).text, /forged/);
  });
  test('changing a topic without the token is refused', async () => {
    const a = await as('admin');
    const r = await a.post('channels/details.php?chan=' + enc('#evil'), { update_topic: 'true', set_topic: 'forged topic' }, XSITE);
    assert.equal(r.status, 403);
    assert.doesNotMatch((await a.get('channels/details.php?chan=' + enc('#evil'))).text, /forged topic/);
  });
  test('API calls without the X-CSRF-Token header are refused', async () => {
    const r = await (await as('admin')).post('api/set_rpc_server.php', { server: 'Test server' }, XSITE);
    assert.equal(r.status, 403);
    assert.match(JSON.parse(r.text).error, /CSRF/);
  });
  test('a wrong token is refused as well', async () => {
    const r = await (await as('admin')).post('settings/index.php', { do_add_user: '1', user_add: 'forged2', password: 'x', user_role: 'Read-Only', csrf_token: 'f'.repeat(64) });
    assert.equal(r.status, 403);
  });
  test('the token of another session does not work', async () => {
    const other = new Client(); await other.login();
    const tok = Client.csrf((await other.get('')).text);
    const r = await (await as('admin')).post('settings/index.php', { do_add_user: '1', user_add: 'forged3', password: 'x', user_role: 'Read-Only', csrf_token: tok });
    assert.equal(r.status, 403);
  });
  test('a plain link cannot log you out', async () => {
    const c = new Client(); await c.login();
    await c.get('login/?logout=true');
    assert.equal((await c.get('settings/')).status, 200);
  });
  test('opening the installer of a configured panel does not log you out', async () => {
    const c = new Client(); await c.login();
    await c.get('settings/install.php');
    assert.equal((await c.get('settings/')).status, 200);
  });
  test('plugins cannot be installed or removed with a plain GET', async () => {
    const r = await (await as('admin')).get('api/plugin.php?uninstall=file_db');
    assert.equal(r.status, 405);
    assert.match(r.text, /Use POST/);
  });
  test('the upgrade cannot be started with a plain GET', async () => {
    const r = await (await as('admin')).get('api/upgrade.php');
    assert.equal(r.status, 405);
  });
  test('every POST form on the panel pages carries the token', async () => {
    const a = await as('admin');
    for (const p of ['', 'users/', 'channels/details.php?chan=' + enc('#evil'), 'servers/', 'server-bans/', 'server-bans/name-bans.php',
      'server-bans/spamfilter.php', 'settings/', 'settings/rpc-servers.php', 'settings/user-edit.php', 'settings/user-role-edit.php']) {
      const html = (await a.get(p)).text;
      /* the page header adds the token to every form; check that the token and the script are there */
      assert.ok(Client.csrf(html), `${p || '/'} has a CSRF token`);
      assert.match(html, /function add_token\(form\)/, `${p || '/'} adds it to forms`);
    }
  });
});
