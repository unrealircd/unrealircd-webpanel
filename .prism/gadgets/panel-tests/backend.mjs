// Panel tests: runs tests/integration/run-tests.mjs (which runs PHPUnit and
// node:test), reads the two JUnit reports, and groups the tests by category.
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';

/* Scratch folder outside the panel's web root (it holds a full copy of the panel) */
const WORK = path.join(os.tmpdir(), 'unrealircd-panel-tests');
const RESULTS = path.join(WORK, 'results');

let run = null;   // the run going on now
let last = null;  // the last finished run
let emitTimer = null;
let API = null;

function categories(api) {
  try { return JSON.parse(fs.readFileSync(path.join(api.root, 'tests', 'categories.json'), 'utf8')); }
  catch { return { order: [], phpunit: {} }; }
}

// ------------------------------------------------------------------ JUnit
const unesc = (s) => s.replace(/&quot;/g, '"').replace(/&apos;/g, "'").replace(/&lt;/g, '<').replace(/&gt;/g, '>')
  .replace(/&#(\d+);/g, (_, n) => String.fromCodePoint(+n)).replace(/&#x([0-9a-f]+);/gi, (_, n) => String.fromCodePoint(parseInt(n, 16))).replace(/&amp;/g, '&');
const attr = (a, n) => { const m = a.match(new RegExp('\\b' + n + '="([^"]*)"')); return m ? unesc(m[1]) : ''; };

/** Every <testcase>, with the names of the <testsuite>s around it */
function parseJunit(xml) {
  const out = [];
  const stack = [];
  const re = /<testsuite\b([^>]*?)(\/?)>|<\/testsuite>|<testcase\b([^>]*?)(?:\/>|>([\s\S]*?)<\/testcase>)/g;
  let m;
  while ((m = re.exec(xml))) {
    if (m[0].startsWith('</testsuite')) { stack.pop(); continue; }
    if (m[0].startsWith('<testsuite')) { if (!m[2]) stack.push(attr(m[1], 'name')); continue; }
    const a = m[3], inner = m[4] || '';
    let status = 'pass', message = '';
    const f = inner.match(/<(failure|error)\b([^>]*)>([\s\S]*?)<\/\1>|<(failure|error)\b([^>]*)\/>/);
    if (f) { status = 'fail'; message = attr(f[2] || f[5] || '', 'message') || unesc((f[3] || '').trim()); }
    else if (/<skipped\b/.test(inner)) { status = 'skip'; const s = inner.match(/<skipped\b([^>]*)/); message = s ? attr(s[1], 'message') : ''; }
    out.push({ name: attr(a, 'name'), cls: attr(a, 'class') || attr(a, 'classname'), time: parseFloat(attr(a, 'time')) || 0,
      suites: [...stack], status, message: message.replace(/\s+/g, ' ').trim().slice(0, 800) });
  }
  return out;
}

function buildResults(api, error) {
  const cats = categories(api);
  const tests = [];
  const pu = path.join(RESULTS, 'phpunit.xml');
  if (fs.existsSync(pu)) for (const t of parseJunit(fs.readFileSync(pu, 'utf8'))) {
    const cls = t.cls.split('\\').pop();
    let name = t.name.replace(/^test_/, '').replace(/ with data set (.*)$/, ' ($1)').replace(/_/g, ' ');
    tests.push({ category: cats.phpunit[cls] || cls || 'Unit tests', name, source: 'unit', file: cls, status: t.status, message: t.message, time: t.time,
      filter: `${cls}::${t.name.replace(/ with data set .*$/, '')}` });
  }
  const nx = path.join(RESULTS, 'node.xml');
  if (fs.existsSync(nx)) for (const t of parseJunit(fs.readFileSync(nx, 'utf8'))) {
    const category = t.suites.filter(Boolean).pop() || 'Other';
    const browser = /browser/i.test(t.cls) || false;
    tests.push({ category, name: t.name, source: 'integration', status: t.status, message: t.message, time: t.time, filter: t.name });
  }
  const order = [...cats.order, ...new Set(tests.map(t => t.category).filter(c => !cats.order.includes(c)))];
  const groups = order.map(c => {
    const list = tests.filter(t => t.category === c);
    return { name: c, tests: list, passed: list.filter(t => t.status === 'pass').length, failed: list.filter(t => t.status === 'fail').length, skipped: list.filter(t => t.status === 'skip').length };
  }).filter(g => g.tests.length);
  const totals = { tests: tests.length, passed: tests.filter(t => t.status === 'pass').length, failed: tests.filter(t => t.status === 'fail').length, skipped: tests.filter(t => t.status === 'skip').length };
  return { finishedAt: Date.now(), error: error || null, groups, totals };
}

// ------------------------------------------------------------------ the UI's view
function snapshot() {
  return {
    running: !!run,
    run: run ? { startedAt: run.startedAt, step: run.step, steps: run.steps, passed: run.passed, failed: run.failed, only: run.label, log: run.log.slice(-400) } : null,
    last,
  };
}
function emitSoon() {
  if (emitTimer) return;
  emitTimer = setTimeout(() => { emitTimer = null; API && API.emit('state', snapshot()); }, 400);
}
function pushLog(line) {
  if (!run) return;
  const s = line.replace(/\x1b\[[0-9;]*m/g, '');
  run.log.push(s);
  if (run.log.length > 3000) run.log.splice(0, run.log.length - 3000);
  const st = s.match(/^##step (\d+)\/(\d+) (.*)$/);
  if (st) { run.step = st[3]; run.steps = `${st[1]}/${st[2]}`; }
  const er = s.match(/^##error (.*)$/);
  if (er) run.error = er[1];
  if (/^\s*✔ /.test(s)) run.passed++;
  if (/^\s*✖ /.test(s)) run.failed++;
}

async function ensureTools(api) {
  const dir = path.join(api.root, 'tests', 'integration');
  if (!fs.existsSync(path.join(dir, 'node_modules', 'playwright'))) {
    pushLog('##step 0/8 Installing the test tools (npm install)'); emitSoon();
    const r = await api.shell('npm install --no-audit --no-fund', { cwd: dir, timeoutMs: 600000 });
    pushLog(r.stdout + r.stderr);
  }
  pushLog('##step 0/8 Checking the test browser'); emitSoon();
  const b = await api.shell('npx playwright install chromium', { cwd: dir, timeoutMs: 900000 });
  if (b.code !== 0) pushLog('The test browser could not be installed, the browser tests will be skipped: ' + (b.stderr || b.stdout).slice(-300));
}

function escRe(s) { return s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'); }

async function startRun(api, { only, category, label } = {}) {
  if (run) return { started: false, reason: 'Tests are already running' };
  if (!api.root) return { started: false, reason: 'No workspace open' };
  let pattern = only || null;
  if (category) {
    /* the describe() name for node:test, the class names for PHPUnit */
    const cats = categories(api);
    const classes = Object.entries(cats.phpunit).filter(([, c]) => c === category).map(([k]) => k);
    pattern = [`^${escRe(category)}$`, ...classes].join('|');
  }
  run = { startedAt: Date.now(), step: 'Starting', steps: '', log: [], passed: 0, failed: 0, label: label || category || only || null, error: null };
  emitSoon();
  const done = new Promise(async (resolve) => {
    await ensureTools(api);
    const args = [path.join(api.root, 'tests', 'integration', 'run-tests.mjs')];
    if (pattern) args.push('--only', pattern);
    let buf = '';
    run.proc = api.spawn('node', args, {
      cwd: path.join(api.root, 'tests', 'integration'),
      env: { ...process.env, TEST_WORK_DIR: WORK, FORCE_COLOR: '0', NO_COLOR: '1' },
      onData(chunk) {
        buf += chunk.toString();
        let i;
        while ((i = buf.indexOf('\n')) >= 0) { pushLog(buf.slice(0, i).replace(/\r$/, '')); buf = buf.slice(i + 1); }
        emitSoon();
      },
      onExit(code) {
        if (buf) pushLog(buf);
        const err = run.error || (run.stopped ? 'Stopped' : (code !== 0 && code !== 1 ? `The test runner stopped with exit code ${code}` : null));
        try { last = buildResults(api, err); }
        catch (e) { last = { finishedAt: Date.now(), error: 'Could not read the results: ' + e.message, groups: [], totals: null }; }
        last.durationMs = Date.now() - run.startedAt;
        last.only = run.label;
        last.logTail = run.log.slice(-400);
        api.state.set('last', last);
        run = null;
        emitSoon();
        const t = last.totals;
        api.ui.notify('Panel tests finished', last.error && !t?.tests ? last.error : `${t?.passed || 0} passed, ${t?.failed || 0} failed, ${t?.skipped || 0} skipped`);
        resolve(last);
      },
    });
  });
  return { started: true, done };
}

function summaryText(r, { category, failedOnly = true } = {}) {
  if (!r) return 'No results yet: run the tests first.';
  const lines = [];
  if (r.error) lines.push('Problem: ' + r.error);
  if (r.totals) lines.push(`${r.totals.tests} tests: ${r.totals.passed} passed, ${r.totals.failed} failed, ${r.totals.skipped} skipped` + (r.only ? ` (only: ${r.only})` : ''));
  for (const g of r.groups || []) {
    if (category && g.name.toLowerCase() !== category.toLowerCase()) continue;
    lines.push(`\n${g.name}: ${g.passed} passed, ${g.failed} failed, ${g.skipped} skipped`);
    for (const t of g.tests) {
      if (failedOnly && t.status === 'pass') continue;
      lines.push(`  [${t.status}] (${t.source}) ${t.name}${t.message ? '\n      ' + t.message : ''}`);
    }
  }
  return lines.join('\n');
}

export default {
  async start(api) { API = api; last = api.state.get('last', null); },
  async stop() { if (run?.proc) run.proc.kill(); },
  handlers: {
    async state() { return snapshot(); },
    async run(args = {}, api) { const r = await startRun(api, args); return { started: r.started, reason: r.reason }; },
    async stop() { if (run?.proc) { run.stopped = true; run.proc.kill(); } return true; },
  },
  tools: [
    {
      name: 'tests_run',
      description: 'Run the panel test suite (PHPUnit unit tests + node:test integration and browser tests against a real UnrealIRCd in Docker), wait until it is done, and return the results with every failing test. Optional: "category" (eg "Channels", "Security: forged requests") or "only" (a regex on test names). A full run takes several minutes, longer the first time (it builds the Docker images).',
      parameters: { type: 'object', properties: { category: { type: 'string' }, only: { type: 'string' } } },
      async execute(args, api) {
        const r = await startRun(api, args);
        if (!r.started) return { content: r.reason, isError: true };
        return summaryText(await r.done);
      },
    },
    {
      name: 'tests_results',
      description: 'The results of the last test run, grouped by category. By default only failing and skipped tests are listed; set all=true for every test, or category for one category. Also tells if a run is going on now.',
      parameters: { type: 'object', properties: { category: { type: 'string' }, all: { type: 'boolean' } } },
      async execute(args) {
        const now = run ? `A run is going on: step ${run.steps} ${run.step}, ${run.passed} passed / ${run.failed} failed so far.\n\n` : '';
        return now + summaryText(last, { category: args.category, failedOnly: !args.all });
      },
    },
    {
      name: 'tests_log',
      description: 'The last lines of the test output (of the run going on, or of the last run).',
      parameters: { type: 'object', properties: { lines: { type: 'number' } } },
      async execute(args) {
        const log = run ? run.log : (last?.logTail || []);
        return log.slice(-(args.lines || 80)).join('\n') || '(no output)';
      },
    },
  ],
};
