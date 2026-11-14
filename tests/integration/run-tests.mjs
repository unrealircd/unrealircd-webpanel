#!/usr/bin/env node
// Runs all panel tests: the PHPUnit unit tests and the node:test integration
// and browser tests, against a copy of this checkout and a real UnrealIRCd.
//
//   cd tests/integration && npm install && npm test
//
// Needs: Docker (running), node 20+. The first run builds the test images
// (UnrealIRCd is compiled from source, a few minutes).
// Options:  --skip-prepare   reuse the running stack and the installed panel
//           --only <regex>   only run tests whose name matches
// Env:      TEST_WORK_DIR    scratch folder (default: <tmp>/unrealircd-panel-tests)
//           PANEL_PORT, IRC_PORT   ports on 127.0.0.1 (default 18080, 16667)
// Output:   <work>/results/node.xml and phpunit.xml (JUnit), and lines starting
//           with "##" for tools that follow the progress (eg the Panel tests gadget).
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import net from 'node:net';
import { spawn } from 'node:child_process';
import { DIR, Client, ADMIN, READER, sleep, IRC_PORT, PANEL_PORT } from './lib.mjs';

const REPO = path.resolve(DIR, '..', '..');
const WORK = process.env.TEST_WORK_DIR || path.join(os.tmpdir(), 'unrealircd-panel-tests');
const RESULTS = path.join(WORK, 'results');
const args = process.argv.slice(2);
const skipPrepare = args.includes('--skip-prepare');
const prepareOnly = args.includes('--prepare-only'); /* set up the stack and the panel, run no tests (used by demo.mjs) */
const only = args.includes('--only') ? args[args.indexOf('--only') + 1] : null;

process.env.PANEL_DIR = path.join(WORK, 'panel');
process.env.PANEL_PORT = String(PANEL_PORT);
process.env.IRC_PORT = String(IRC_PORT);

let planter = null;
const STEPS = ['Check Docker', 'Copy the panel', 'Start the test servers', 'Reset', 'Install the panel', 'Connect test IRC users', 'Unit tests (PHPUnit)', 'Integration and browser tests (node:test)'];
function step(name) { console.log(`##step ${STEPS.indexOf(name) + 1}/${STEPS.length} ${name}`); }
function fail(msg) { console.log(`##error ${msg}`); if (planter) planter.kill(); process.exit(2); }
const compose = (...a) => ['compose', '-f', path.join(DIR, 'docker-compose.yml'), ...a];

function run(cmd, argv, opts = {}) {
  return new Promise((resolve) => {
    const p = spawn(cmd, argv, { cwd: opts.cwd || DIR, env: process.env, shell: false });
    let out = '';
    const on = (d) => { const s = d.toString(); out += s; if (opts.echo !== false) process.stdout.write(s); };
    p.stdout.on('data', on); p.stderr.on('data', on);
    const timer = opts.timeoutMs ? setTimeout(() => { p.kill(); resolve({ code: -2, out: out + `\n(no answer after ${opts.timeoutMs / 1000} s)` }); }, opts.timeoutMs) : null;
    p.on('close', (code) => { if (timer) clearTimeout(timer); resolve({ code, out }); });
    p.on('error', (e) => { if (timer) clearTimeout(timer); resolve({ code: -1, out: String(e) }); });
  });
}
async function must(label, cmd, argv, opts) {
  const r = await run(cmd, argv, opts);
  if (r.code !== 0) fail(`${label} failed (exit ${r.code}): ${r.out.trim().split('\n').slice(-3).join(' | ')}`);
  return r.out;
}
function waitPort(port, ms) {
  const end = Date.now() + ms;
  return new Promise((resolve, reject) => {
    const tryOnce = () => {
      const s = net.connect(port, '127.0.0.1');
      s.once('connect', () => { s.destroy(); resolve(); });
      s.once('error', () => { s.destroy(); if (Date.now() > end) reject(new Error(`port ${port} not reachable`)); else setTimeout(tryOnce, 1000); });
    };
    tryOnce();
  });
}
async function waitIrcReady(ms) {
  const end = Date.now() + ms;
  while (Date.now() < end) {
    try {
      await waitPort(IRC_PORT, 2000);
      const ok = await new Promise((res) => {
        const s = net.connect(IRC_PORT, '127.0.0.1');
        let buf = '';
        s.on('data', d => {
          buf += d;
          /* UnrealIRCd sends a PING cookie and waits for the PONG before it registers the client */
          const ping = buf.match(/^PING :?(\S+)/m);
          if (ping && !s.ponged) { s.ponged = true; s.write(`PONG :${ping[1]}\r\n`); }
          if (/ 001 /.test(buf)) { s.destroy(); res(true); }
        });
        s.on('error', () => res(false));
        s.write('NICK probe' + Math.floor(Math.random() * 1e5) + '\r\nUSER p 0 * :p\r\n');
        setTimeout(() => { s.destroy(); res(false); }, 30000);
      });
      if (ok) return;
    } catch { /* retry */ }
    await sleep(1500);
  }
  fail('The IRC server did not come up');
}

function copyPanel() {
  /* A copy of this checkout, without its own config, accounts and tools */
  const skip = new Set(['.git', 'node_modules', '.prism', 'manifest.json']);
  const skipRel = new Set(['config/config.php', 'data/database.php']);
  fs.rmSync(process.env.PANEL_DIR, { recursive: true, force: true });
  fs.cpSync(REPO, process.env.PANEL_DIR, {
    recursive: true,
    filter: (src) => {
      const rel = path.relative(REPO, src).split(path.sep).join('/');
      if (rel === '') return true;
      if (rel.split('/').some(t => skip.has(t))) return false;
      if (skipRel.has(rel) || /^config\/config\.tmp\./.test(rel)) return false;
      return true;
    },
  });
  if (!fs.existsSync(path.join(process.env.PANEL_DIR, 'vendor', 'autoload.php')))
    fail('vendor/ is missing: run "composer install" in the panel directory first');
}

async function install() {
  const c = new Client();
  const g = await c.get('settings/install.php');
  const r = await c.post('settings/install.php', { auth_method: 'file_db', account_user: ADMIN.user, account_password: ADMIN.pass,
    account_password_conf: ADMIN.pass, account_email: 'admin@example.test', account_fname: 'Ad', account_lname: 'Min', account_bio: '',
    csrf_token: Client.csrf(g.text) || '' });
  if (!/configuration file has been written/i.test(r.text)) fail('installing the panel failed');
  const l = await c.login();
  if (l.status !== 302) fail(`admin login failed (status ${l.status})`);
  /* No password breach lookups (api.pwnedpasswords.com) during the tests */
  await c.submit('settings/general.php', { submit: '1' });
  const add = await c.submit('settings/rpc-servers.php', { do_add_server: '1', edit_existing: '', rpc_displayname: 'Test server', rpc_default: 'on',
    rpc_host: 'ircd', rpc_port: '8600', rpc_user: 'adminpanel', rpc_password: 'test-rpc-password' });
  if (!/RPC Server successfully added/.test(add.text)) fail('adding the RPC server failed');
  const reader = await c.submit('settings/index.php', { do_add_user: '1', user_add: READER.user, password: READER.pass, user_role: 'Read-Only',
    user_email: 'r@example.test', add_first_name: 'Re', add_last_name: 'Ader', user_bio: '' });
  if (!/Successfully created user/.test(reader.text)) fail('creating the Read-Only user failed');
}

// ------------------------------------------------------------------ main
fs.mkdirSync(RESULTS, { recursive: true });
for (const f of ['node.xml', 'phpunit.xml']) fs.rmSync(path.join(RESULTS, f), { force: true });
console.log(`##work ${WORK}`);

try {
  if (!skipPrepare) {
    step('Check Docker');
    const d = await run('docker', ['info', '--format', '{{.ServerVersion}}'], { echo: false, timeoutMs: 30000 });
    if (d.code === -2) fail('Docker does not answer (it may be hanging). Restart Docker Desktop (or the docker service) and try again.');
    if (d.code !== 0) fail('Docker is not running. Start Docker Desktop (or the docker service) and try again.');

    step('Copy the panel');
    copyPanel();

    step('Start the test servers');
    await must('docker compose up', 'docker', compose('up', '-d', '--build', '--remove-orphans'));
    await waitPort(PANEL_PORT, 120000).catch(() => fail('The panel web server did not come up'));

    step('Reset');
    /* A new container, not a restart: UnrealIRCd keeps server bans and
     * spamfilters in data/tkl.db, which would survive a restart */
    await must('recreate ircd', 'docker', compose('up', '-d', '--force-recreate', 'ircd'));
    await must('clean panel container', 'docker', compose('exec', '-T', 'panel', 'sh', '-c',
      'rm -rf /var/www/html/legacy /var/www/html/adminlogin /tmp/sess_* && chown -R www-data /var/www/html/unrealircd-webpanel'));
    await waitIrcReady(240000);

    step('Install the panel');
    await install();
  }
  if (prepareOnly) {
    console.log('##prepared');
    process.exit(0);
  }

  step('Connect test IRC users');
  planter = spawn(process.execPath, [path.join(DIR, 'plant.mjs')], { cwd: DIR, env: process.env });
  await new Promise((resolve, reject) => {
    let out = '';
    const t = setTimeout(() => reject(new Error('timed out: ' + out.slice(-300))), 180000);
    planter.stdout.on('data', d => { out += d; if (/READY/.test(out)) { clearTimeout(t); resolve(); } });
    planter.stderr.on('data', d => { out += d; });
    planter.on('exit', (c) => { clearTimeout(t); reject(new Error(`exited (${c}): ${out.slice(-300)}`)); });
  }).catch(e => fail('Connecting the test IRC users failed: ' + e.message));

  step('Unit tests (PHPUnit)');
  const unitArgs = ['php', 'vendor/bin/phpunit', '--log-junit', '/tmp/phpunit.xml'];
  if (only) unitArgs.push('--filter', only);
  await run('docker', compose('exec', '-T', '-w', '/var/www/html/unrealircd-webpanel', 'panel', ...unitArgs));
  const xml = await run('docker', compose('exec', '-T', 'panel', 'cat', '/tmp/phpunit.xml'), { echo: false });
  if (xml.code === 0) fs.writeFileSync(path.join(RESULTS, 'phpunit.xml'), xml.out);

  step('Integration and browser tests (node:test)');
  const suites = fs.readdirSync(path.join(DIR, 'suites')).filter(f => f.endsWith('.test.mjs')).sort().map(f => path.join('suites', f));
  const nodeArgs = ['--test', '--test-concurrency=1', '--test-timeout=600000',
    '--test-reporter=junit', `--test-reporter-destination=${path.join(RESULTS, 'node.xml')}`,
    '--test-reporter=spec', '--test-reporter-destination=stdout'];
  if (only) nodeArgs.push(`--test-name-pattern=${only}`);
  await run(process.execPath, [...nodeArgs, ...suites]);
} finally {
  if (planter) planter.kill();
}
console.log(`##done ${RESULTS}`);
