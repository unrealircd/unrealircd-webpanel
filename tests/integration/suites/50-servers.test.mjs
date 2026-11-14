import { describe, test } from 'node:test';
import assert from 'node:assert/strict';
import { as, alerts, noPhpErrors } from '../lib.mjs';

describe('Servers', () => {
  test('the Servers page lists the IRC server', async () => {
    const r = await (await as('admin')).get('servers/');
    noPhpErrors(r.text);
    assert.match(r.text, /details\.php\?server=001">irc\.test</);
    assert.match(r.text, /UnrealIRCd-6/);
  });
  test('filter the server list by name', async () => {
    const a = await as('admin');
    const hit = await a.submit('servers/index.php', { sf_name: 'irc' }, 'servers/');
    assert.match(hit.text, />irc\.test</);
    const miss = await a.submit('servers/index.php', { sf_name: 'nomatch' }, 'servers/');
    assert.doesNotMatch(miss.text, />irc\.test</);
  });
  test('server details show info, modes and modules', async () => {
    const r = await (await as('admin')).get('servers/details.php?server=irc.test');
    noPhpErrors(r.text);
    assert.match(r.text, /Server Lookup for &quot;irc\.test&quot;/);
    assert.match(r.text, /Panel test server/);
    assert.match(r.text, /<code>001<\/code>/);
    assert.match(r.text, /<code>chanmodes<\/code>|Channel Modes/);
    assert.match(r.text, /Official/);
  });
  test('an unknown server says it was not found', async () => {
    const r = await (await as('admin')).get('servers/details.php?server=no.such.server');
    assert.ok(alerts(r.text).includes('Could not find server: "no.such.server"'));
  });
  test('rehash the server', async () => {
    const r = await (await as('admin')).submit('servers/details.php?server=irc.test', { rehash: 'irc.test' });
    assert.match(alerts(r.text).join(), /successfully rehashed.*irc\.test/);
  });
  test('the Network Health check page loads', async () => {
    const r = await (await as('admin')).get('tools/checkup.php');
    assert.equal(r.status, 200);
    noPhpErrors(r.text);
  });
  test('a Read-Only user cannot rehash', async () => {
    const r = await (await as('reader')).submit('servers/details.php?server=irc.test', { rehash: 'irc.test' });
    assert.doesNotMatch(alerts(r.text).join(), /successfully rehashed/);
  });
});
