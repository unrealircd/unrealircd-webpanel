import { describe, test } from 'node:test';
import assert from 'node:assert/strict';
import { as, enc, noPhpErrors, HOST, BASE } from '../lib.mjs';

/** Read Server-Sent Events from a panel stream for a while */
async function readStream(client, path, ms = 4000) {
  const ctl = new AbortController();
  const res = await fetch(HOST + BASE + path, { headers: { cookie: client.cookieHeader() }, signal: ctl.signal });
  const events = [];
  const reader = res.body.getReader();
  const dec = new TextDecoder();
  let buf = '';
  const stop = setTimeout(() => ctl.abort(), ms);
  try {
    for (;;) {
      const { value, done } = await reader.read();
      if (done) break;
      buf += dec.decode(value, { stream: true });
      let i;
      while ((i = buf.indexOf('\n\n')) >= 0) {
        const chunk = buf.slice(0, i); buf = buf.slice(i + 2);
        const line = chunk.split('\n').find(l => l.startsWith('data: '));
        if (line) try { events.push(JSON.parse(line.slice(6))); } catch { }
      }
    }
  } catch { /* aborted */ } finally { clearTimeout(stop); }
  return { status: res.status, type: res.headers.get('content-type'), events };
}

describe('Logs & search', () => {
  test('the Logs page loads', async () => {
    const r = await (await as('admin')).get('logs/');
    noPhpErrors(r.text);
    assert.match(r.text, /Log viewer/);
  });
  test('the log stream sends the stored log entries', async () => {
    const s = await readStream(await as('admin'), 'api/log.php');
    assert.equal(s.status, 200);
    assert.match(s.type, /text\/event-stream/);
    const entries = s.events.filter(e => e.msg);
    assert.ok(entries.length > 5, `${entries.length} entries`);
    assert.ok(entries.some(e => e.subsystem === 'connect'));
    assert.ok(s.events.some(e => e.sync_option === 'sync_now'));
  });
  test('the Overview stream sends live statistics', async () => {
    const s = await readStream(await as('admin'), 'api/overview.php', 4000);
    const st = s.events.find(e => e.user);
    assert.ok(st, 'a statistics event');
    assert.ok(st.user.total >= 3);
    assert.ok(st.channel.total >= 3);
    assert.equal(st.server.total, 1);
  });
  test('search finds users by nick', async () => {
    const j = await (await as('admin')).json('api/search.php?search=' + enc('chanop'));
    assert.ok(j.users.some(u => u.name === 'chanop' && u.label === 'nick'));
  });
  test('search finds users by realname, and returns it as plain data', async () => {
    const j = await (await as('admin')).json('api/search.php?search=' + enc('evil.example'));
    assert.ok(j.users.some(u => u.name === 'httpuser' && u.label === 'GECOS' && u.data === 'https://evil.example/phish'));
  });
  test('search finds channels by name and topic', async () => {
    const a = await as('admin');
    assert.ok((await a.json('api/search.php?search=' + enc('#keyzero'))).channels.some(c => c.name === '#keyzero'));
    assert.ok((await a.json('api/search.php?search=' + enc('REAL channel'))).channels.some(c => c.name === '#a&b' && c.label === 'channel topic'));
  });
  test('search finds channel bans', async () => {
    const j = await (await as('admin')).json('api/search.php?search=' + enc('~text'));
    assert.ok(j.channels.some(c => c.name === '#evil' && c.label === 'ban (+b)'));
  });
  test('search finds the server and log entries', async () => {
    const j = await (await as('admin')).json('api/search.php?search=' + enc('irc.test'));
    assert.ok(j.servers.some(s => s.name === 'irc.test'));
    assert.ok(j.logs.length > 0);
  });
  test('search with "*" as a wildcard', async () => {
    const j = await (await as('admin')).json('api/search.php?search=' + enc('chan*op'));
    assert.ok(j.users.some(u => u.name === 'chanop'));
  });
  test('search answers with valid JSON for any term', async () => {
    const a = await as('admin');
    for (const term of ['a', '(', '[', '\\', '"', '%', 'é']) {
      const r = await a.get('api/search.php?search=' + enc(term));
      assert.doesNotThrow(() => JSON.parse(r.text), `term ${term}`);
    }
  });
});
