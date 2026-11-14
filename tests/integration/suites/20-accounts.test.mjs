import { describe, test } from 'node:test';
import assert from 'node:assert/strict';
import { Client, as, alerts, noPhpErrors } from '../lib.mjs';

async function userId(a, name) {
  const list = await a.get('settings/index.php');
  const m = list.text.match(new RegExp(`user-edit\\.php\\?id=(\\d+)">${name}<`));
  return m ? m[1] : null;
}
async function createUser(a, name, role = 'Read-Only', extra = {}) {
  return a.submit('settings/index.php', { do_add_user: '1', user_add: name, password: 'Acc0unt!pass', user_role: role,
    user_email: `${name}@example.test`, add_first_name: 'First', add_last_name: 'Last', user_bio: 'bio', ...extra });
}

describe('Accounts & roles', () => {
  test('the Accounts page lists the panel users', async () => {
    const r = await (await as('admin')).get('settings/index.php');
    noPhpErrors(r.text);
    assert.match(r.text, />admin</);
    assert.match(r.text, />reader</);
  });
  test('create a user, who can then log in', async () => {
    const a = await as('admin');
    const r = await createUser(a, 'alice');
    assert.ok(alerts(r.text).includes('Successfully created user "alice"'));
    assert.equal((await new Client().login('alice', 'Acc0unt!pass')).status, 302);
  });
  test('a user name that exists already is refused', async () => {
    const r = await createUser(await as('admin'), 'alice');
    assert.match(alerts(r.text).join(), /Failed to create user/);
  });
  test('a user with "&" in the name can log in (names are stored as typed)', async () => {
    const a = await as('admin');
    await createUser(a, 'tom&jerry');
    assert.equal((await new Client().login('tom&jerry', 'Acc0unt!pass')).status, 302);
    assert.match((await a.get('settings/index.php')).text, />tom&amp;jerry</);
  });
  test('profile fields are saved and shown', async () => {
    const a = await as('admin');
    await createUser(a, 'bob', 'Read-Only', { add_first_name: 'Bobby', add_last_name: 'Tables', user_bio: 'Robert' });
    const r = await a.get('settings/index.php');
    assert.match(r.text, /Bobby/);
    assert.match(r.text, /Tables/);
    assert.match(r.text, /mailto:bob@example.test/);
  });
  test('edit a profile: first name, bio and session timeout', async () => {
    const a = await as('admin');
    const id = await userId(a, 'bob');
    const url = `settings/user-edit.php?id=${id}`;
    await a.submit(url, { update_user: '1', first_name: 'Rob', bio: 'new bio', session_timeout: '1800' });
    const p = await a.get(url);
    noPhpErrors(p.text);
    assert.match(p.text, /placeholder="Rob"/);
    assert.match(p.text, />new bio</);
    assert.match(p.text, /name="session_timeout"[^>]*value="1800"/);
  });
  test('change a password: the old one stops working, the new one works', async () => {
    const a = await as('admin');
    const url = `settings/user-edit.php?id=${await userId(a, 'bob')}`;
    await a.submit(url, { update_user: '1', password: 'N3w!password', passwordconfirm: 'N3w!password', session_timeout: '3600' });
    assert.notEqual((await new Client().login('bob', 'Acc0unt!pass')).status, 302);
    assert.equal((await new Client().login('bob', 'N3w!password')).status, 302);
  });
  test('passwords that do not match are refused', async () => {
    const a = await as('admin');
    const url = `settings/user-edit.php?id=${await userId(a, 'bob')}`;
    const r = await a.submit(url, { update_user: '1', password: 'One!pass1', passwordconfirm: 'Two!pass2', session_timeout: '3600' });
    assert.ok(alerts(r.text).includes('Could not update password: Passwords did not match'));
  });
  test('a user can edit their own profile but not their role', async () => {
    const c = new Client(); await c.login('alice', 'Acc0unt!pass');
    const p = await c.get('settings/user-edit.php');
    assert.match(p.text, /<select name="user_role" disabled/);
    await c.submit('settings/user-edit.php', { update_user: '1', first_name: 'Ally', user_role: 'Super-Admin', session_timeout: '3600' });
    const after = await c.get('settings/user-edit.php');
    assert.match(after.text, /placeholder="Ally"/);
    assert.match(after.text, /<option value="Read-Only" selected/);
  });
  test('a user cannot edit someone else\'s profile', async () => {
    const a = await as('admin');
    const bobId = await userId(a, 'bob');
    const c = new Client(); await c.login('alice', 'Acc0unt!pass');
    const p = await c.get(`settings/user-edit.php?id=${bobId}`);
    assert.match(p.text, /name="first_name" id="first_name" placeholder/);
    assert.match(p.text, /<input disabled type="text" class="form-control" name="first_name"/);
  });
  test('delete a user', async () => {
    const a = await as('admin');
    await createUser(a, 'carol');
    const id = await userId(a, 'carol');
    const r = await a.submit('settings/index.php', { delete_user: '1', 'userch[]': [id] });
    assert.match(alerts(r.text).join(), /Successfully deleted user "carol"/);
    assert.equal(await userId(a, 'carol'), null);
  });
  test('Role Editor: create a role', async () => {
    const r = await (await as('admin')).submit('settings/user-role-edit.php', { add_role_name: 'Moderator' });
    assert.ok(alerts(r.text).includes('Added user role "Moderator"'));
    assert.match(r.text, /Moderator/);
  });
  test('Role Editor: a role name that exists is refused', async () => {
    const r = await (await as('admin')).submit('settings/user-role-edit.php', { add_role_name: 'moderator' });
    assert.match(alerts(r.text).join(), /already exists/);
  });
  test('Role Editor: give a role permissions, and a user with it can use them', async () => {
    const a = await as('admin');
    const r = await a.submit('settings/user-role-edit.php', { update_role: 'Moderator', 'permissions[]': ['tkl_add', 'tkl_del'] });
    assert.ok(alerts(r.text).includes('Successfully updated role "Moderator"'));
    await createUser(a, 'mod1', 'Moderator');
    const m = new Client(); await m.login('mod1', 'Acc0unt!pass');
    const add = await m.submit('server-bans/index.php', { do_add_ban: '1', edit_existing: '', ban_host: '*@mod.example', ban_type: 'gline', ban_duration: '1h', ban_reason: 'mod test' }, 'server-bans/');
    assert.ok(alerts(add.text).includes('Ban added on *@mod.example'));
    const users = await m.get('settings/index.php');
    assert.doesNotMatch(users.text, /Add New User/, 'no user management for this role');
  });
  test('Role Editor: duplicate a role', async () => {
    const r = await (await as('admin')).submit('settings/user-role-edit.php', { add_role_name: 'Mod copy', use_dup_role: 'on', dup_role: 'Moderator' });
    assert.ok(alerts(r.text).includes('Added user role "Mod copy", a duplicate of "Moderator"'));
  });
  test('Role Editor: saving a role with nothing ticked works and pages still load', async () => {
    const a = await as('admin');
    const r = await a.submit('settings/user-role-edit.php', { update_role: 'Mod copy' });
    assert.ok(alerts(r.text).includes('Successfully updated role "Mod copy"'));
    assert.equal((await a.get('')).status, 200);
  });
  test('Role Editor: delete a role', async () => {
    const r = await (await as('admin')).submit('settings/user-role-edit.php', { del_role_name: 'Mod copy' });
    assert.ok(alerts(r.text).includes('Successfully deleted role "Mod copy"'));
  });
  test('Role Editor: the built-in roles cannot be changed or deleted', async () => {
    const a = await as('admin');
    for (const form of [{ del_role_name: 'Super-Admin' }, { update_role: 'Read-Only', 'permissions[]': ['manage_users'] }]) {
      const r = await a.submit('settings/user-role-edit.php', form);
      assert.match(alerts(r.text).join(), /Role does not exist/);
    }
  });
  test('Role Editor: unknown permissions are not stored', async () => {
    const a = await as('admin');
    await a.submit('settings/user-role-edit.php', { update_role: 'Moderator', 'permissions[]': ['tkl_add', 'root_shell'] });
    const p = await a.get('settings/user-role-edit.php');
    assert.doesNotMatch(p.text, /root_shell/);
  });
  test('a user cannot be given a role that does not exist', async () => {
    const a = await as('admin');
    const url = `settings/user-edit.php?id=${await userId(a, 'alice')}`;
    const r = await a.submit(url, { update_user: '1', user_role: 'No such role', session_timeout: '3600' });
    assert.match(alerts(r.text).join(), /unknown role/);
    assert.match((await a.get(url)).text, /<option value="Read-Only" selected/);
  });
  test('creating a user with an unknown role is refused', async () => {
    const r = await createUser(await as('admin'), 'dave', 'Root');
    assert.match(alerts(r.text).join(), /unknown role/);
  });
});
