// Security: install state and what the web server hands out.
import { describe, test } from 'node:test';
import assert from 'node:assert/strict';
import { as, dexec, Client, WEBROOT } from '../lib.mjs';

/** A copy of the panel without config, optionally with an old (pre-2023) root config.php */
function freshCopy(dir, oldRootConfig) {
  dexec(`rm -rf /var/www/html/${dir} && mkdir /var/www/html/${dir} && cd ${WEBROOT} && ` +
    `tar --exclude=./config/config.php --exclude=./data/database.php --exclude=./manifest.json -cf - . | tar -xf - -C /var/www/html/${dir} && ` +
    (oldRootConfig ? `printf '<?php\\ndefine("UNREALIRCD_RPC_USER","adminpanel");\\ndefine("UNREALIRCD_RPC_PASSWORD","test-rpc-password");\\ndefine("UNREALIRCD_HOST","ircd");\\ndefine("UNREALIRCD_PORT","8600");\\ndefine("PLUGINS",["file_auth"]);\\n' > /var/www/html/${dir}/config.php && ` : '') +
    `chown -R www-data /var/www/html/${dir}`);
  return new Client(`/${dir}/`);
}
async function runInstaller(c, extra = {}) {
  const g = await c.get('settings/install.php');
  return c.post('settings/install.php', { auth_method: 'file_db', account_user: 'someone', account_password: 'S0meone!pass', account_password_conf: 'S0meone!pass',
    account_email: 's@example.test', account_fname: '', account_lname: '', account_bio: '', csrf_token: Client.csrf(g.text) || '', ...extra });
}

describe('Security: install & web server', () => {
  test('the installer refuses to run on a configured panel', async () => {
    const r = await runInstaller(new Client());
    assert.match(r.text, /You&#039;re already configured!/);
  });
  test('the installer refuses to run when only an old root config.php exists', async () => {
    const r = await runInstaller(freshCopy('legacy', true));
    assert.match(r.text, /You&#039;re already configured!/);
    assert.doesNotMatch(r.text, /configuration file has been written/);
  });
  test('the installer only accepts the two database backends', async () => {
    const r = await runInstaller(freshCopy('legacy', false), { auth_method: '../plugins/file_db' });
    assert.match(r.text, /Invalid parameters/);
  });
  test('an install directory ending in "login" still needs a login', async () => {
    dexec(`rm -rf /var/www/html/adminlogin && cp -a ${WEBROOT} /var/www/html/adminlogin && chown -R www-data /var/www/html/adminlogin`);
    const r = await new Client('/adminlogin/').get('');
    assert.equal(r.status, 302);
  });
  for (const f of ['config/config.php', 'config/compat.php', 'data/database.php', 'config/.htaccess', 'composer.json', 'composer.lock', 'phpunit.xml', 'Dockerfile', 'tests/bootstrap.php', 'tests/integration/lib.mjs', 'vendor/autoload.php']) {
    test(`${f} is not handed out`, async () => {
      const r = await new Client().get(f);
      assert.ok(r.status === 403 || r.status === 404 || (r.status === 200 && r.text.trim() === ''), `status ${r.status}: ${r.text.slice(0, 60)}`);
    });
  }
  test('a backup copy config.php~ is not handed out', async () => {
    dexec(`cp ${WEBROOT}/config/config.php ${WEBROOT}/config.php~ && chown www-data ${WEBROOT}/config.php~`);
    try {
      const r = await new Client().get('config.php~');
      assert.notEqual(r.status, 200);
    } finally { dexec(`rm -f ${WEBROOT}/config.php~`); }
  });
  test('the Overview warns admins about backup copies in config/', async () => {
    dexec(`cp ${WEBROOT}/config/config.php ${WEBROOT}/config/config.php.bak`);
    try {
      const ov = await (await as('admin')).get('');
      assert.match(ov.text, /may be readable by anyone over the web/);
      assert.ok(ov.text.includes('config/config.php.bak'));
    } finally { dexec(`rm -f ${WEBROOT}/config/config.php.bak`); }
  });
  test('.prism/ (editor files) is not handed out', async () => {
    dexec(`mkdir -p ${WEBROOT}/.prism/gadgets/x && echo '{"secret":1}' > ${WEBROOT}/.prism/gadgets/x/gadget.json && echo '{}' > ${WEBROOT}/.prism/decisions.jsonl`);
    try {
      for (const f of ['.prism/gadgets/x/gadget.json', '.prism/decisions.jsonl', '.prism/'])
        assert.notEqual((await new Client().get(f)).status, 200, f);
    } finally { dexec(`rm -rf ${WEBROOT}/.prism`); }
  });
  test('.git/ is not handed out', async () => {
    dexec(`mkdir -p ${WEBROOT}/.git && echo 'ref: refs/heads/main' > ${WEBROOT}/.git/HEAD`);
    try {
      assert.notEqual((await new Client().get('.git/HEAD')).status, 200);
    } finally { dexec(`rm -rf ${WEBROOT}/.git`); }
  });
});
