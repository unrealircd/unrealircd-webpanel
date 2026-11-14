import { describe, test } from 'node:test';
import assert from 'node:assert/strict';
import { as, alerts, noPhpErrors, dexec, WEBROOT } from '../lib.mjs';

const RPC = { rpc_host: 'ircd', rpc_port: '8600', rpc_user: 'adminpanel', rpc_password: 'test-rpc-password' };

describe('Settings & RPC servers', () => {
  test('the General Settings page loads and saves', async () => {
    const a = await as('admin');
    const p = await a.get('settings/general.php');
    noPhpErrors(p.text);
    const r = await a.submit('settings/general.php', { submit: '1', debug_mode: 'on' });
    assert.ok(alerts(r.text).includes('Debug Mode is now enabled'));
    const back = await a.submit('settings/general.php', { submit: '1' });
    assert.ok(alerts(back.text).includes('Debug Mode is now disabled'));
  });
  test('the RPC Servers page lists the configured server', async () => {
    const r = await (await as('admin')).get('settings/rpc-servers.php');
    noPhpErrors(r.text);
    assert.match(r.text, /class="rpc-server-edit" data-server="[^"]*Test server[^"]*">Test server<\/a> <span class="badge rounded-pill badge-success">Default/);
  });
  test('test the connection to the RPC server', async () => {
    const r = await (await as('admin')).api('api/test_rpc_server.php', { tls_verify: 'false', host: 'ircd', port: '8600', user: 'adminpanel', password: 'test-rpc-password', edit_existing: '' });
    assert.deepEqual(JSON.parse(r.text), { success: 'Successfully connected' });
  });
  test('a wrong RPC password fails the connection test', async () => {
    const r = await (await as('admin')).api('api/test_rpc_server.php', { tls_verify: 'false', host: 'ircd', port: '8600', user: 'adminpanel', password: 'wrong', edit_existing: '' });
    assert.match(JSON.parse(r.text).error, /Unable to connect/);
  });
  test('add a second RPC server, make it the default, then switch back', async () => {
    const a = await as('admin');
    const add = await a.submit('settings/rpc-servers.php', { do_add_server: '1', edit_existing: '', rpc_displayname: 'Second', ...RPC });
    assert.ok(alerts(add.text).includes('RPC Server successfully added.'));
    assert.equal((await a.api('api/set_rpc_server.php', { server: 'Second' })).text.trim(), 'true');
    assert.match((await a.get('')).text, /data-toggle="dropdown"[^>]*>Second<\/a>/);
    assert.equal((await a.api('api/set_rpc_server.php', { server: 'Test server' })).text.trim(), 'true');
  });
  test('edit an RPC server without retyping its password', async () => {
    const a = await as('admin');
    const r = await a.submit('settings/rpc-servers.php', { do_add_server: '1', edit_existing: 'Second', rpc_displayname: 'Second renamed', ...RPC, rpc_password: '****************' });
    assert.ok(alerts(r.text).includes('RPC Server successfully modified.'));
    const t = await a.api('api/test_rpc_server.php', { tls_verify: 'false', host: 'ircd', port: '8600', user: 'adminpanel', password: '****************', edit_existing: 'Second renamed' });
    assert.match(t.text, /Successfully connected/);
  });
  test('RPC passwords are stored encrypted', async () => {
    assert.equal(dexec(`grep -c 'test-rpc-password' ${WEBROOT}/data/database.php || true`).trim(), '0');
    assert.match(dexec(`grep -o "'rpc_password' => 'secret:" ${WEBROOT}/data/database.php | head -1`), /secret:/);
  });
  test('invalid RPC server details are refused', async () => {
    const r = await (await as('admin')).submit('settings/rpc-servers.php', { do_add_server: '1', edit_existing: '', rpc_displayname: 'Bad', rpc_host: 'bad host/x', rpc_port: '99999', rpc_user: '', rpc_password: 'x' });
    const msg = alerts(r.text).join();
    assert.match(msg, /Invalid hostname or IP/);
    assert.match(msg, /Invalid port/);
    assert.match(msg, /Invalid RPC username/);
  });
  test('a duplicate RPC server name is refused', async () => {
    const r = await (await as('admin')).submit('settings/rpc-servers.php', { do_add_server: '1', edit_existing: '', rpc_displayname: 'Test server', ...RPC });
    assert.match(alerts(r.text).join(), /A server with that name already exists/);
  });
  test('delete an RPC server', async () => {
    const a = await as('admin');
    await a.submit('settings/rpc-servers.php', { do_del_server: '1', del_server_name: 'Second renamed' });
    assert.doesNotMatch((await a.get('settings/rpc-servers.php')).text, /Second renamed/);
  });
  test('the Plugins page and plugin list load for an admin', async () => {
    const a = await as('admin');
    const p = await a.get('settings/plugins.php');
    noPhpErrors(p.text);
    assert.match(p.text, /Active Plugins/);
    assert.doesNotMatch((await a.get('api/plugin.php')).text, /Access denied/);
  });
  test('IP WHOIS: the page loads and refuses things that are not IP addresses', async () => {
    const a = await as('admin');
    noPhpErrors((await a.get('tools/ip-whois.php')).text);
    const r = await a.get('tools/ip-whois.php?ip=example.com');
    assert.ok(alerts(r.text).includes('Lookup failed: that is not a valid IP address'));
  });
});
