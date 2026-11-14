// The panel's own Docker image (Dockerfile in the panel root).
import { describe, test, before, after } from 'node:test';
import assert from 'node:assert/strict';
import { execFileSync } from 'node:child_process';
import { sleep } from '../lib.mjs';

const CTX = process.env.PANEL_DIR;
const PORT = 18092;
const IMG = 'unrealircd-panel-tests-image';
const docker = (...a) => execFileSync('docker', a, { encoding: 'utf8', stdio: ['ignore', 'pipe', 'pipe'], maxBuffer: 1 << 26 });
let built = null;

describe('Security: install & web server', { skip: CTX ? false : 'run through run-tests.mjs (needs PANEL_DIR)' }, () => {
  before(async () => {
    try {
      docker('build', '-q', '-t', IMG, CTX);
      try { docker('rm', '-f', IMG); } catch { }
      docker('run', '-d', '--name', IMG, '-p', `127.0.0.1:${PORT}:8080`, IMG);
      for (let i = 0; i < 30; i++) { try { await fetch(`http://127.0.0.1:${PORT}/`); break; } catch { await sleep(1000); } }
      built = true;
    } catch (e) { built = String(e.stderr || e).slice(-400); }
  }, { timeout: 1800000 });
  after(() => { try { docker('rm', '-f', IMG); } catch { } });

  test('the Docker image builds and serves the panel', async () => {
    assert.equal(built, true, `docker build/run failed: ${built}`);
    const r = await fetch(`http://127.0.0.1:${PORT}/`, { redirect: 'manual' });
    assert.ok([200, 302].includes(r.status), `status ${r.status}`);
  });
  test('the Docker image keeps config/ and data/ on volumes', () => {
    const vols = JSON.parse(docker('image', 'inspect', '--format', '{{json .Config.Volumes}}', IMG)) || {};
    assert.ok('/var/www/html/config' in vols && '/var/www/html/data' in vols, JSON.stringify(Object.keys(vols)));
  });
  test('nginx in the Docker image does not hand out internal files', async () => {
    for (const f of ['composer.json', 'config/compat.php', 'config/.gitignore', 'data/index.php']) {
      const r = await fetch(`http://127.0.0.1:${PORT}/${f}`);
      assert.notEqual(r.status, 200, f);
    }
  });
  test('the Docker image does not contain the test tools', () => {
    let has = true;
    try { docker('exec', IMG, 'test', '-e', '/var/www/html/vendor/phpunit'); } catch { has = false; }
    assert.equal(has, false, 'vendor/phpunit is in the image');
    let tests = true;
    try { docker('exec', IMG, 'test', '-e', '/var/www/html/tests'); } catch { tests = false; }
    assert.equal(tests, false, 'tests/ is in the image');
  });
});
