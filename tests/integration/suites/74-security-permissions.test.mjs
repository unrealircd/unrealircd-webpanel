// Security: what a Read-Only panel account must not be able to do.
import { describe, test } from 'node:test';
import assert from 'node:assert/strict';
import { as } from '../lib.mjs';

describe('Security: permissions', () => {
  test('a Read-Only user cannot use the stored RPC password through test_rpc_server', async () => {
    const r = await (await as('reader')).api('api/test_rpc_server.php', { tls_verify: 'false', host: 'ircd', port: '8600', user: 'adminpanel', password: '****************', edit_existing: 'Test server' });
    assert.equal(r.status, 403);
  });
  test('the stored RPC password is never sent to a different host, even for an admin', async () => {
    const r = await (await as('admin')).api('api/test_rpc_server.php', { tls_verify: 'false', host: '127.0.0.1', port: '8600', user: 'adminpanel', password: '****************', edit_existing: 'Test server' });
    assert.match(JSON.parse(r.text).error, /host or port was changed/);
  });
  test('a Read-Only user cannot change the default RPC server', async () => {
    const r = await (await as('reader')).api('api/set_rpc_server.php', { server: 'Test server' });
    assert.equal(r.status, 403);
  });
  test('a Read-Only user does not get the RPC server switcher (an admin does)', async () => {
    const a = await as('admin');
    await a.submit('settings/rpc-servers.php', { do_add_server: '1', edit_existing: '', rpc_displayname: 'Other', rpc_host: 'ircd', rpc_port: '8600', rpc_user: 'adminpanel', rpc_password: 'test-rpc-password' });
    try {
      assert.match((await a.get('')).text, /class="dropdown-item rpc-server-switch" href="#" data-server="Other"/);
      const r = await (await as('reader')).get('');
      assert.doesNotMatch(r.text, /class="dropdown-item rpc-server-switch"/);
      assert.match(r.text, /<div class="dropdown-item text-muted">Other<\/div>/);
    } finally {
      await a.submit('settings/rpc-servers.php', { do_del_server: '1', del_server_name: 'Other' });
    }
  });
  test('a Read-Only user cannot start an upgrade', async () => {
    const r = await (await as('reader')).api('api/upgrade.php', {});
    assert.equal(r.status, 403);
  });
  test('a Read-Only user cannot open the plugin manager or its API', async () => {
    const reader = await as('reader');
    assert.match((await reader.get('settings/plugins.php')).text, /Access denied/);
    assert.match((await reader.get('settings/add-plugin.php')).text, /Access denied/);
    assert.match((await reader.get('api/plugin.php')).text, /Access denied/);
  });
  test('a Read-Only user cannot open the Role Editor', async () => {
    assert.match((await (await as('reader')).get('settings/user-role-edit.php')).text, /Access denied/);
  });
  test('a Read-Only user cannot add panel users', async () => {
    const reader = await as('reader');
    await reader.submit('settings/index.php', { do_add_user: '1', user_add: 'sneaky', password: 'Sn3aky!pass', user_role: 'Super-Admin', user_email: 's@x.test', add_first_name: '', add_last_name: '', user_bio: '' });
    assert.doesNotMatch((await (await as('admin')).get('settings/index.php')).text, />sneaky</);
  });
  test('a Read-Only user cannot add RPC servers', async () => {
    const reader = await as('reader');
    await reader.submit('settings/rpc-servers.php', { do_add_server: '1', edit_existing: '', rpc_displayname: 'sneaky', rpc_host: '127.0.0.1', rpc_port: '8600', rpc_user: 'x', rpc_password: 'y' });
    assert.doesNotMatch((await (await as('admin')).get('settings/rpc-servers.php')).text, /sneaky/);
  });
});
