// Helpers for the integration tests: an HTTP client with a cookie jar,
// a raw IRC client (an ordinary IRC user), and docker helpers.
import net from 'node:net';
import path from 'node:path';
import assert from 'node:assert/strict';
import { execFileSync } from 'node:child_process';
import { fileURLToPath } from 'node:url';

export const DIR = path.dirname(fileURLToPath(import.meta.url));
export const PANEL_PORT = Number(process.env.PANEL_PORT || 18080);
export const IRC_PORT = Number(process.env.IRC_PORT || 16667);
export const HOST = `http://127.0.0.1:${PANEL_PORT}`;
export const BASE = '/unrealircd-webpanel/';
export const WEBROOT = '/var/www/html/unrealircd-webpanel'; // inside the panel container
export const ADMIN = { user: 'admin', pass: 'Adm1n!pass#' };
export const READER = { user: 'reader', pass: 'Read3r!pass' };
export const enc = encodeURIComponent;
export const sleep = ms => new Promise(r => setTimeout(r, ms));

/** Run a shell command in the panel container */
export function dexec(cmd) {
  return execFileSync('docker', ['compose', '-f', path.join(DIR, 'docker-compose.yml'), 'exec', '-T', 'panel', 'sh', '-c', cmd],
    { encoding: 'utf8', env: process.env });
}

export class Client {
  constructor(base = BASE) { this.base = base; this.jar = new Map(); }
  cookieHeader() { return [...this.jar].map(([k, v]) => `${k}=${v}`).join('; '); }
  store(res) {
    for (const c of res.headers.getSetCookie?.() || []) {
      const [kv, ...attrs] = c.split(';');
      const i = kv.indexOf('=');
      const k = kv.slice(0, i).trim(), v = kv.slice(i + 1).trim();
      this.lastSetCookie = c;
      if (v === '' || v === 'deleted' || /max-age=0|expires=thu, 01 jan 1970/i.test(attrs.join(';'))) this.jar.delete(k);
      else this.jar.set(k, v);
    }
  }
  /** 'users/' is relative to the panel; '/x/y' is an absolute path (eg a link taken from a page) */
  url(p) { return p.startsWith('http') ? p : p.startsWith('/') ? HOST + p : HOST + this.base + p; }
  async req(method, p, { form, headers = {}, body } = {}) {
    const h = { ...headers };
    if (this.jar.size) h.cookie = this.cookieHeader();
    if (form) {
      body = new URLSearchParams();
      for (const [k, v] of Object.entries(form)) {
        if (Array.isArray(v)) v.forEach(x => body.append(k, x)); else body.append(k, v);
      }
      body = body.toString();
      h['content-type'] = 'application/x-www-form-urlencoded';
    }
    let res;
    try {
      res = await fetch(this.url(p), { method, headers: h, body, redirect: 'manual' });
    } catch (e) {
      /* fetch reuses idle keep-alive connections; Apache closes them after a few
       * seconds, so after a slow step the first request can hit a closed socket.
       * Then the request was never handled: try once more on a new connection. */
      const code = e.cause?.code || '';
      if (!/ECONNRESET|UND_ERR_SOCKET|EPIPE|UND_ERR_CLOSED/.test(code)) throw e;
      res = await fetch(this.url(p), { method, headers: h, body, redirect: 'manual' });
    }
    this.store(res);
    const text = await res.text();
    return { status: res.status, headers: res.headers, text, location: res.headers.get('location') };
  }
  get(p, o) { return this.req('GET', p, o); }
  post(p, form, o = {}) { return this.req('POST', p, { ...o, form }); }
  json(p) { return this.get(p).then(r => JSON.parse(r.text)); }
  /** CSRF token of a page (meta tag on panel pages, hidden field on login/installer) */
  static csrf(text) {
    const m = text.match(/name="csrf-token" content="([^"]+)"/) || text.match(/name="csrf_token" value="([^"]+)"/);
    return m ? m[1] : null;
  }
  async login(user = ADMIN.user, pass = ADMIN.pass) {
    const page = await this.get('login/');
    return this.post('login/', { username: user, password: pass, csrf_token: Client.csrf(page.text) || '' });
  }
  /** POST a form the way the panel does: with the token from page `from` */
  async submit(p, form, from = p) {
    const tok = Client.csrf((await this.get(from)).text);
    return this.post(p, { ...form, csrf_token: tok || '' });
  }
  /** POST to an API the way the panel's JavaScript does (X-CSRF-Token header) */
  async api(p, form, from = '') {
    const tok = Client.csrf((await this.get(from)).text);
    return this.post(p, form, { headers: { 'x-csrf-token': tok || '' } });
  }
}

const clients = {};
/** A logged in client, cached per test file: as('admin') or as('reader') */
export async function as(role = 'admin') {
  if (!clients[role]) {
    const c = new Client();
    const who = role === 'admin' ? ADMIN : READER;
    const r = await c.login(who.user, who.pass);
    assert.equal(r.status, 302, `${role} login failed (status ${r.status})`);
    clients[role] = c;
  }
  return clients[role];
}

/** Turn HTML text back into plain text (as the browser shows it) */
export function htmlText(s) {
  return s.replace(/<[^>]+>/g, '').replace(/&quot;/g, '"').replace(/&#0?39;|&apos;/g, "'").replace(/&lt;/g, '<').replace(/&gt;/g, '>')
    .replace(/&times;/g, '×').replace(/&#(\d+);/g, (_, n) => String.fromCodePoint(+n)).replace(/&amp;/g, '&');
}

/** Text of the alert boxes on a page (success / fail messages), as the user reads them */
export function alerts(html) {
  return [...html.matchAll(/<div class="alert[^"]*"[^>]*>([\s\S]*?)<\/div>/g)]
    .map(m => htmlText(m[1].replace(/<span class="closebtn[\s\S]*?<\/span>/, '').replace(/<br>/g, ' | ')).replace(/\s+/g, ' ').trim());
}

const PHP_ERROR = /(<b>)?(Fatal error|Parse error|Warning|Deprecated|Notice|Uncaught)(<\/b>)?:/;
/** Assert that a page has no PHP errors or warnings in it */
export function noPhpErrors(html, where = '') {
  const m = html.match(new RegExp(PHP_ERROR.source + '.{0,160}'));
  assert.ok(!m, `PHP error ${where}: ${m ? m[0].replace(/<[^>]+>/g, '') : ''}`);
}

export class Irc {
  constructor(nick, { user = 'u', real = 'r', port = IRC_PORT } = {}) {
    this.nick = nick; this.user = user; this.real = real; this.port = port;
    this.buf = ''; this.lines = []; this.waiters = [];
  }
  connect() {
    return new Promise((resolve, reject) => {
      this.sock = net.connect(this.port, '127.0.0.1');
      this.sock.setEncoding('latin1');
      this.sock.on('error', reject);
      this.sock.on('data', d => {
        this.buf += d;
        let i;
        while ((i = this.buf.indexOf('\r\n')) >= 0) {
          const line = this.buf.slice(0, i); this.buf = this.buf.slice(i + 2);
          this.lines.push(line);
          if (line.startsWith('PING')) this.raw('PONG' + line.slice(4));
          for (const w of [...this.waiters]) if (w.re.test(line)) { this.waiters.splice(this.waiters.indexOf(w), 1); w.res(line); }
        }
      });
      this.raw(`NICK ${this.nick}`);
      this.raw(`USER ${this.user} 0 * :${this.real}`);
      this.wait(/ 001 /, 30000).then(resolve, reject);
    });
  }
  raw(s) { this.sock.write(Buffer.from(s + '\r\n', 'utf8')); }
  rawBytes(buf) { this.sock.write(Buffer.concat([buf, Buffer.from('\r\n')])); }
  /** Wait for a line matching re (seen=true: also look at lines that already came) */
  wait(re, ms = 30000, seen = true) {
    const hit = seen && this.lines.find(l => re.test(l));
    if (hit) return Promise.resolve(hit);
    return new Promise((res, rej) => {
      const w = { re, res };
      this.waiters.push(w);
      setTimeout(() => { const i = this.waiters.indexOf(w); if (i >= 0) { this.waiters.splice(i, 1); rej(new Error(`timeout waiting for ${re} (${this.nick})`)); } }, ms);
    });
  }
  async join(chan) { this.raw('JOIN ' + chan); await this.wait(new RegExp(' 366 \\S+ ' + escRe(chan) + ' '), 30000, false); }
  /** List a channel's +b / +e / +I entries (as a member, so the list is always shown) */
  async list(chan, mode = 'b') {
    const end = { b: '368', e: '349', I: '347' }[mode];
    const item = { b: ' 367 ', e: ' 348 ', I: ' 346 ' }[mode];
    const from = this.lines.length;
    this.raw(`MODE ${chan} ${mode}`);
    await this.wait(new RegExp(` ${end} \\S+ ${escRe(chan)} `), 30000, false);
    return this.lines.slice(from).filter(l => l.includes(item)).map(l => l.split(' ')[4]);
  }
  async quit(msg) { this.raw(`QUIT :${msg}`); await new Promise(r => this.sock.once('close', r)); }
  close() { this.sock.destroy(); }
}
export function escRe(s) { return s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'); }
