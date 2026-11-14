import { describe, test } from 'node:test';
import assert from 'node:assert/strict';
import { Client, as, dexec, alerts, noPhpErrors, ADMIN, BASE, WEBROOT } from '../lib.mjs';

describe('Login & sessions', () => {
  test('the login page loads, with a CSRF token in the form', async () => {
    const r = await new Client().get('login/');
    assert.equal(r.status, 200);
    noPhpErrors(r.text);
    assert.ok(Client.csrf(r.text));
  });
  test('pages need a login: they redirect to the login page, keeping where you were going', async () => {
    const r = await new Client().get('users/?x=1');
    assert.equal(r.status, 302);
    assert.equal(r.location, BASE + 'login/?redirect=' + encodeURIComponent(BASE + 'users/?x=1'));
  });
  test('API calls without a login get 403 JSON, not a redirect', async () => {
    const r = await new Client().get('api/users.php');
    assert.equal(r.status, 403);
    assert.equal(JSON.parse(r.text).error, 'Access denied');
  });
  test('a correct login goes to the Overview', async () => {
    const c = new Client();
    const r = await c.login();
    assert.equal(r.status, 302);
    assert.equal(r.location, BASE);
    const ov = await c.get('');
    assert.match(ov.text, /Network Overview/);
    noPhpErrors(ov.text);
  });
  test('a wrong password is refused', async () => {
    const c = new Client();
    const r = await c.login(ADMIN.user, 'wrong');
    assert.equal(r.status, 200);
    assert.ok(alerts(r.text).includes('Incorrect login'));
  });
  test('an unknown username is refused the same way', async () => {
    const r = await new Client().login('nobody', 'wrong');
    assert.ok(alerts(r.text).includes('Incorrect login'));
  });
  test('missing credentials are refused', async () => {
    const c = new Client();
    const p = await c.get('login/');
    const r = await c.post('login/', { username: '', password: '', csrf_token: Client.csrf(p.text) });
    assert.match(alerts(r.text).join(), /Missing credentials/);
  });
  test('login keeps a redirect inside the panel', async () => {
    const c = new Client();
    const target = BASE + 'channels/';
    const p = await c.get('login/?redirect=' + encodeURIComponent(target));
    const r = await c.post('login/?redirect=' + encodeURIComponent(target), { ...ADMIN, username: ADMIN.user, password: ADMIN.pass, csrf_token: Client.csrf(p.text) });
    assert.equal(r.location, target);
  });
  for (const target of ['https://evil.example/', '//evil.example/', '/\t/evil.example/', '/\\evil.example']) {
    test(`login never redirects off the panel (${JSON.stringify(target)})`, async () => {
      const c = new Client();
      const p = await c.get('login/?redirect=' + encodeURIComponent(target));
      const r = await c.post('login/?redirect=' + encodeURIComponent(target), { username: ADMIN.user, password: ADMIN.pass, csrf_token: Client.csrf(p.text) });
      assert.equal(r.location, BASE);
    });
  }
  test('logout with the menu link, then log in again', async () => {
    const c = new Client();
    await c.login();
    const link = (await c.get('')).text.match(/href="([^"]*login\/\?logout=[^"]+)"/);
    assert.ok(link, 'Logout link in the menu');
    const out = await c.get(link[1].replace(/&amp;/g, '&'));
    assert.ok(alerts(out.text).includes('You have been logged out'));
    assert.equal((await c.get('settings/')).status, 302);
    assert.equal((await c.login()).status, 302);
  });
  test('a logout link without the right token does not log out', async () => {
    const c = new Client();
    await c.login();
    await c.get('login/?logout=true');
    assert.equal((await c.get('settings/')).status, 200);
  });
  /** Make the server see this client's session as idle for a long time */
  const ageSession = (c) => dexec(`sed -i 's/last-activity|i:[0-9]*;/last-activity|i:1;/' /tmp/sess_${[...c.jar.values()][0]}`);

  test('a link with ?timeout=1 does not log out someone who is logged in', async () => {
    const c = new Client();
    await c.login();
    const r = await c.get('login/?timeout=1&redirect=' + encodeURIComponent(BASE + 'users/'));
    assert.equal(r.status, 302);
    assert.equal(r.location, BASE + 'users/', 'sent on to the page');
    assert.equal((await c.get('settings/')).status, 200, 'still logged in');
  });
  test('after a real timeout, the login page says so and login works', async () => {
    const c = new Client();
    await c.login();
    ageSession(c);
    assert.equal((await c.get('settings/')).status, 302, 'the server logged the session out');
    const t = await c.get('login/?timeout=1');
    assert.ok(alerts(t.text).includes('Your session has timed out. Please login again to continue'));
    const r = await c.post('login/', { username: ADMIN.user, password: ADMIN.pass, csrf_token: Client.csrf(t.text) });
    assert.equal(r.status, 302);
  });
  test('with several tabs open, the first login after a timeout works', async () => {
    const c = new Client();
    await c.login();
    ageSession(c);
    /* tab A shows the login page; then the other tabs notice the timeout and load login/?timeout=1 too */
    const tabA = await c.get('login/?timeout=1');
    for (let i = 0; i < 3; i++) await c.get('login/?timeout=1');
    const r = await c.post('login/', { username: ADMIN.user, password: ADMIN.pass, csrf_token: Client.csrf(tabA.text) });
    assert.equal(r.status, 302, 'the form in tab A still has a valid token');
  });
  test('the timeout message is shown once, not on the next visit to the login page', async () => {
    const c = new Client();
    await c.login();
    ageSession(c);
    await c.get('settings/');
    assert.ok(alerts((await c.get('login/')).text).some(a => /timed out/.test(a)));
    assert.ok(!alerts((await c.get('login/')).text).some(a => /timed out/.test(a)));
  });
  test("a login page left open survives another tab's session poll", async () => {
    const c = new Client();
    const p = await c.get('login/');
    for (let i = 0; i < 3; i++) await c.get('api/timeout.php');
    const r = await c.post('login/', { username: ADMIN.user, password: ADMIN.pass, csrf_token: Client.csrf(p.text) });
    assert.equal(r.status, 302);
  });
  test('an expired login form shows a message instead of an error page', async () => {
    const c = new Client();
    await c.get('login/');
    const r = await c.post('login/', { username: ADMIN.user, password: ADMIN.pass, csrf_token: 'stale' });
    assert.equal(r.status, 200);
    assert.match(alerts(r.text).join(), /login form had expired/);
  });
  test('api/timeout.php reports the session', async () => {
    const c = new Client();
    assert.equal(JSON.parse((await c.get('api/timeout.php')).text).session, 'none');
    await c.login();
    assert.equal(JSON.parse((await c.get('api/timeout.php')).text).session, 'active');
  });
  test('a new session ID is given at login', async () => {
    const c = new Client();
    await c.get('login/');
    const before = [...c.jar.values()].join();
    await c.login();
    assert.notEqual([...c.jar.values()].join(), before);
  });
  test('the session cookie is HttpOnly and SameSite=Lax', async () => {
    const c = new Client();
    await c.login();
    assert.match(c.lastSetCookie, /HttpOnly/i);
    assert.match(c.lastSetCookie, /SameSite=Lax/i);
  });
  test('over HTTPS (also behind a proxy) the session cookie is Secure', async () => {
    const c = new Client();
    await c.get('login/', { headers: { 'x-forwarded-proto': 'https' } });
    assert.match(c.lastSetCookie, /;\s*secure/i);
    const plain = new Client();
    await plain.get('login/');
    assert.doesNotMatch(plain.lastSetCookie, /;\s*secure/i, 'not on plain http, or the browser would drop it');
  });
  test('an idle session is logged out by the server', async () => {
    const c = new Client();
    await c.login();
    const id = [...c.jar.values()][0];
    dexec(`sed -i 's/last-activity|i:[0-9]*;/last-activity|i:1;/' /tmp/sess_${id}`);
    assert.equal((await c.get('settings/')).status, 302);
  });
  test("a session from another PHP app on the server (with an 'id' in it) is not a login", async () => {
    const id = dexec(`php -r 'include "${WEBROOT}/data/database.php"; echo $db["users"]["admin"]["id"];'`).trim();
    const sid = 'otherapp' + Date.now();
    dexec(`printf 'id|i:${id};' > /tmp/sess_${sid} && chown www-data /tmp/sess_${sid}`);
    const probe = new Client(); await probe.get('login/');
    const c = new Client(); c.jar.set([...probe.jar.keys()][0], sid);
    assert.equal((await c.get('settings/')).status, 302);
  });
  test('the old default account default/testing cannot log in', async () => {
    const a = await as('admin');
    await a.submit('settings/index.php', { do_add_user: '1', user_add: 'default', password: 'testing', user_role: 'Read-Only',
      user_email: 'd@example.test', add_first_name: '', add_last_name: '', user_bio: '' });
    assert.notEqual((await new Client().login('default', 'testing')).status, 302);
  });
  test('login still works when manifest.json is missing (it is created again)', async () => {
    dexec(`rm -f ${WEBROOT}/manifest.json`);
    assert.equal((await new Client().login()).status, 302);
    assert.match(dexec(`cat ${WEBROOT}/manifest.json`), /"start_url": "\/unrealircd-webpanel\/"/);
  });
  test('unknown usernames take about as long as wrong passwords', async () => {
    const time = async (user) => {
      let best = Infinity;
      for (let i = 0; i < 3; i++) { const t = performance.now(); await new Client().login(user, 'wrong-Passw0rd!'); best = Math.min(best, performance.now() - t); }
      return best;
    };
    const known = await time(ADMIN.user), unknown = await time('nobody-here');
    assert.ok(known - unknown < 15, `known ${known.toFixed(0)} ms, unknown ${unknown.toFixed(0)} ms`);
  });
});
