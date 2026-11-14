// Browser tests (Playwright, headless Chromium): the pages where JavaScript puts
// IRC data into the page. The test IRC users' "payloads" only set a marker
// (window.X<letter> = 1); no marker may ever get set.
import { describe, test, before, after } from 'node:test';
import assert from 'node:assert/strict';
import { HOST, BASE, ADMIN, Irc, sleep, enc } from '../lib.mjs';

let chromium = null;
try { ({ chromium } = await import('playwright')); } catch { /* reported as skipped below */ }
const SKIP = chromium ? false : 'playwright is not installed: run "npm install" and "npx playwright install chromium" in tests/integration';
let browser, page;

const MARKERS = ['R', 'O', 'Q', 'U', 'B', 'E', 'K', 'L', 'T', 'C', 'G', 'H', 'P', 'N'];
async function noMarkers(where) {
  const hit = await page.evaluate((n) => n.filter(x => window['X' + x] === 1), MARKERS);
  assert.deepEqual(hit, [], `script from IRC data ran on ${where}`);
}
async function go(p) { await page.goto(HOST + BASE + p); }

async function setup() {
  browser = await chromium.launch();
  page = await (await browser.newContext()).newPage();
  await go('login/');
  await page.fill('#userinp', ADMIN.user);
  await page.fill('#passinp', ADMIN.pass);
  await Promise.all([page.waitForNavigation(), page.click('button[type=submit]')]);
}

describe('Logs & search', { skip: SKIP }, () => {
  before(setup);
  after(async () => { if (browser) await browser.close(); });

  test('the Logs page shows log entries as text', async () => {
    await go('logs/');
    await page.waitForSelector('#data_list tbody tr td.virtuallink', { timeout: 20000 });
    await sleep(3000);
    const rows = await page.$$eval('#data_list tbody tr', r => r.length);
    assert.ok(rows > 5, `${rows} rows`);
    await noMarkers('the Logs page');
  });
  test('the Logs page search filter finds an entry', async () => {
    await page.fill('input[type=search]', 'quitter');
    await sleep(800);
    const text = await page.$eval('#data_list tbody', el => el.innerText);
    assert.match(text, /quitter/);
    await noMarkers('the Logs page (filtered)');
  });
  test('a log entry opens with its message and JSON, as text', async () => {
    await page.click('#data_list tbody tr td');
    await page.waitForSelector('#view_log_entry.show');
    const msg = await page.$eval('#view_log_entry_message', el => el.textContent);
    assert.match(msg, /quitter/);
    await noMarkers('the log entry window');
    await page.keyboard.press('Escape');
  });
  test('text starting with "http" in a log entry is not a link', async () => {
    await page.fill('input[type=search]', 'httpuser');
    await sleep(800);
    await page.click('#data_list tbody tr td');
    await page.waitForSelector('#view_log_entry.show');
    /* (the expand/collapse toggles are <a> elements too, but without href) */
    const links = await page.$$eval('#view_log_entry_json a[href], #view_log_entry_json .json-formatter-url', as => as.map(a => a.getAttribute('href') || a.textContent));
    assert.deepEqual(links, []);
    const text = await page.$eval('#view_log_entry_json', el => el.textContent);
    assert.match(text, /https:\/\/evil\.example\/phish/, 'the realname is still shown, as text');
    await page.keyboard.press('Escape');
  });
  test('the navbar search shows matches as text', async () => {
    await go('users/');
    await page.fill('#search_box', 'onerror');
    await page.waitForFunction(() => document.querySelectorAll('#search-results .card.p-3').length > 0, null, { timeout: 15000 });
    const text = await page.$eval('#search-results', el => el.innerText);
    assert.match(text, /gecosuser/);
    assert.match(text, /<img src=x onerror=top\.XU=1>/, 'the realname is shown as text');
    await noMarkers('the search results');
  });
  test('a search result opens the user', async () => {
    await page.fill('#search_box', 'chanop');
    await page.waitForFunction(() => [...document.querySelectorAll('#search-results .card.p-3')].some(e => /chanop/.test(e.textContent)));
    await Promise.all([page.waitForNavigation(), page.click('#search-results .card.p-3 >> text=chanop')]);
    assert.match(page.url(), /users\/details\.php\?nick=chanop/);
  });
});

describe('Channels', { skip: SKIP }, () => {
  before(setup);
  after(async () => { if (browser) await browser.close(); });

  test('the Channels list shows the channels', async () => {
    await go('channels/');
    await page.waitForSelector('#data_list tbody tr td.virtuallink');
    const text = await page.$eval('#data_list tbody', el => el.innerText);
    assert.match(text, /#evil/);
    await noMarkers('the Channels list');
  });
  test('clicking a channel with "&" in its name opens that channel, not a look-alike', async () => {
    await page.fill('input[type=search]', 'REAL');
    await sleep(800);
    await Promise.all([page.waitForNavigation(), page.click('#data_list tbody tr td')]);
    assert.match(await page.content(), /I am the REAL channel/);
  });
  test('look-alike channel names are marked in the list', async () => {
    await go('channels/');
    await page.waitForSelector('#data_list tbody tr td.virtuallink');
    await page.fill('input[type=search]', 'pal');
    await sleep(800);
    const text = await page.$eval('#data_list tbody', el => el.innerText);
    assert.match(text, /U\+200B/);
    assert.match(text, /mixed scripts/);
  });
  test('the channel page with hostile bans, key and topic runs nothing', async () => {
    await go('channels/details.php?chan=' + enc('#evil'));
    await page.click('a[href="#chanbans"]');
    await sleep(1000);
    await noMarkers('the channel page');
  });
});

describe('Login & sessions', { skip: SKIP }, () => {
  before(setup);
  after(async () => { if (browser) await browser.close(); });

  test('failed OPER attempts make at most one popup per 30 seconds, labeled as IRC log', async () => {
    await go('servers/');
    await sleep(3000);
    const c = new Irc('toaster'); await c.connect();
    for (let i = 0; i < 4; i++) { c.raw(`OPER CALL-THIS-NUMBER-${i} wrong`); await sleep(1500); }
    await sleep(3000);
    c.close();
    const toasts = await page.$$eval('#toaster .toast', els => els.map(e => e.innerText));
    const ours = toasts.filter(t => /CALL-THIS-NUMBER/.test(t));
    assert.equal(ours.length, 1, `${ours.length} popups`);
    assert.match(ours[0], /IRC server log/);
  });
  test("the panel's own forms and API calls carry the CSRF token", async () => {
    await go('servers/');
    const ok = await page.$$eval('form', fs => fs.filter(f => f.method.toLowerCase() === 'post').every(f => !!f.querySelector('input[name=csrf_token]')));
    assert.ok(ok, 'every POST form has the token');
    const r = await page.evaluate(async (b) => (await fetch(b + 'api/set_rpc_server.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'server=Test%20server' })).text(), BASE);
    assert.equal(r.trim(), 'true');
  });
});
