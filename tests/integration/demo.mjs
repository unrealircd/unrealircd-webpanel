#!/usr/bin/env node
// A panel full of data to click around in: starts the test stack, installs a
// fresh panel, fills IRC and the panel with demo data, and stays running
// (the IRC users stay online) until you press Ctrl+C.
//
//   cd tests/integration && npm install && node demo.mjs
//   node demo.mjs --no-prepare     reuse the running stack and panel, only add the data
//
// Then open http://127.0.0.1:18080/unrealircd-webpanel/  (admin / Adm1n!pass#)
import path from 'node:path';
import { spawn } from 'node:child_process';
import { DIR, Client, Irc, sleep, HOST, BASE, ADMIN, READER } from './lib.mjs';

const args = process.argv.slice(2);
const log = (...a) => console.log('[demo]', ...a);
const b64 = (s) => Buffer.from(s).toString('base64');

if (!args.includes('--no-prepare')) {
  log('Preparing the test stack and a fresh panel (the first time this builds the Docker images)...');
  const code = await new Promise((resolve) => {
    const p = spawn(process.execPath, [path.join(DIR, 'run-tests.mjs'), '--prepare-only'], { cwd: DIR, stdio: 'inherit', env: process.env });
    p.on('close', resolve);
  });
  if (code !== 0) { log('Preparing failed, see above.'); process.exit(1); }
}

// ------------------------------------------------------------------ IRC side
const clients = [];
async function user(nick, real, ident = nick.toLowerCase().slice(0, 9)) {
  const c = new Irc(nick, { user: ident, real });
  await c.connect();
  clients.push(c);
  return c;
}
const say = (c, target, text) => c.raw(`PRIVMSG ${target} :${text}`);

const CHANNELS = {
  '#lobby': { topic: 'Welcome to TestNet! Be nice, read the rules: https://example.org/rules', modes: '+nt' },
  '#help': { topic: 'Need help? Ask your question and wait a bit.', modes: '+ntC' },
  '#dev': { topic: 'UnrealIRCd module development | builds green', modes: '+nt', key: 'devs-only' },
  '#gaming': { topic: 'LAN party Saturday 8pm | bring snacks', modes: '+nt' },
  '#music': { topic: 'Now playing: anything but polka', modes: '+nt' },
  '#ops': { topic: 'Staff only', modes: '+ntsi' },
  '#rock&roll': { topic: 'Channel with an & in its name: open it from the Channels list', modes: '+nt' },
  '#quiet': { topic: 'Moderated channel, voice to speak', modes: '+ntm' },
  '#limited': { topic: 'Max 5 people', modes: '+ntl', limit: '5' },
  '#trivia': { topic: 'Round 12: what does IRC stand for?', modes: '+nt' },
};
const PEOPLE = [
  ['Alice', 'Alice Example'], ['bob_', 'Bob from accounting'], ['carol', 'Carol Danvers'], ['dave', 'Dave'],
  ['eve', 'Eve (definitely not listening)'], ['frank', 'Frank Lee'], ['grace', 'Grace Hopper fan'], ['heidi', 'Heidi'],
  ['ivan', 'Ivan Petrov'], ['judy', 'Judy'], ['mallory', 'Mallory'], ['nina', 'Nina Simone'], ['oscar', 'Oscar the Grouch'],
  ['peggy', 'Peggy'], ['quentin', 'Quentin T.'], ['rupert', 'Rupert'], ['sybil', 'Sybil Fawlty'], ['trent', 'Trent Reznor'],
  ['ursula', 'Ursula'], ['victor', 'Victor Victoria'], ['walter', 'Walter White'], ['xena', 'Xena'], ['yolanda', 'Yolanda'], ['zoe', 'Zoe'],
];

log('Connecting IRC users...');
const founder = {};
const people = [];
for (const [nick, real] of PEOPLE) people.push(await user(nick, real));

// The first person in each channel founds it (and gets ops)
const chanNames = Object.keys(CHANNELS);
for (let i = 0; i < chanNames.length; i++) {
  const ch = chanNames[i], f = people[i % people.length];
  founder[ch] = f;
  await f.join(ch);
  const c = CHANNELS[ch];
  f.raw(`TOPIC ${ch} :${c.topic}`);
  let modes = c.modes, params = [];
  if (c.key) { modes += 'k'; params.push(c.key); }
  if (c.limit) params.push(c.limit);
  f.raw(`MODE ${ch} ${modes} ${params.join(' ')}`.trim());
}
// Everyone joins a few channels (not the keyed / invite-only / limited ones)
const open = ['#lobby', '#help', '#gaming', '#music', '#rock&roll', '#quiet', '#trivia'];
for (let i = 0; i < people.length; i++) {
  const p = people[i];
  for (const ch of open) if ((i + ch.length) % 3 !== 0 && founder[ch] !== p) p.raw('JOIN ' + ch);
  if (i % 4 === 0) p.raw('JOIN #dev devs-only');
  if (i < 3) p.raw('JOIN #limited');
  await sleep(150);
}
await sleep(1500);

// Channel status: ops, voices, bans, exceptions, invites
const f = founder;
f['#lobby'].raw('MODE #lobby +ov bob_ carol');
f['#lobby'].raw('MODE #lobby +vvv dave eve frank');
f['#lobby'].raw('MODE #lobby +bb *!*@spammer.example troll*!*@*');
f['#lobby'].raw('MODE #lobby +e *!*@trusted.example');
f['#help'].raw('MODE #help +h grace');
f['#gaming'].raw('MODE #gaming +b ~quiet:*!*@laggy.example');
f['#gaming'].raw('MODE #gaming +b ~time:1440:*!*@cheater.example');
f['#quiet'].raw('MODE #quiet +vv heidi ivan');
f['#ops'].raw('MODE #ops +I *!*@staff.example');
f['#music'].raw('MODE #music +b ~realname:*polka*');
f['#trivia'].raw('MODE #trivia +b ~text:block:*spoiler*');

// Some chatter (this ends up in the logs only for joins/parts/etc, not messages)
const lines = ['hi all', 'anyone around?', 'brb coffee', 'lol', 'nice one', 'who is up for a game?', 'has anyone seen the new release?', 'gg', 'thanks!', 'good morning'];
for (let i = 0; i < 30; i++) {
  const p = people[i % people.length];
  say(p, open[i % open.length], lines[i % lines.length]);
  await sleep(100);
}
// Away messages, a bot, an oper
people[4].raw('AWAY :Out for lunch');
people[9].raw('AWAY :Sleeping, ping me tomorrow');
const bot = await user('InfoBot', 'I answer questions in #help', 'bot');
bot.raw('MODE InfoBot +B');
bot.raw('JOIN #help');
const oper = await user('NetAdmin', 'Network Administrator', 'admin');
oper.raw('OPER bobsmith operpass');
await oper.wait(/ 381 /, 10000).catch(() => log('(oper login did not answer)'));
oper.raw('JOIN #ops');

// Log entries: a failed OPER attempt, some users leaving
mallory_failed: {
  const m = people.find(p => p.nick === 'mallory');
  m.raw('OPER root hunter2');
}
const leaver1 = await user('leaver', 'Just passing by');
leaver1.raw('JOIN #lobby');
await sleep(500);
await leaver1.quit('Gone fishing');
clients.splice(clients.indexOf(leaver1), 1);

// Tricky data, to check that the panel shows it as text and handles it right
log('Adding the tricky data...');
const tricky = await user('tricky', '<b>not bold</b> <img src=x onerror=alert("realname")>', 'tricky');
await tricky.join('#tricky');
tricky.raw('TOPIC #tricky :<script>alert("topic")</script> should show as plain text');
tricky.raw(`MODE #tricky +b ~text:block:'><img/src/onerror=alert(1)>`);
tricky.raw('MODE #tricky +b a&b!*@*');
tricky.raw('JOIN #pаypal'); /* a Cyrillic 'а': marked "mixed scripts" */
tricky.raw('JOIN #pay​pal'); /* a zero-width space: shown as U+200B */
tricky.raw('JOIN #paypal');       /* the real one */
const twin = await user('twinop', 'Owner of a look-alike channel', 'twin');
twin.raw('JOIN #rock&amp;roll');   /* the escaped-name twin of #rock&roll */
twin.raw('TOPIC #rock&amp;roll :I am the look-alike, you should not land here from the list');
tricky.raw('OPER <b>fake-oper</b> nope');
await sleep(1500);

// ------------------------------------------------------------------ panel side
log('Adding panel data (accounts, roles, server bans, spamfilters)...');
const a = new Client();
const lr = await a.login();
if (lr.status !== 302) { log('Could not log in to the panel as admin'); process.exit(1); }

await a.submit('settings/user-role-edit.php', { add_role_name: 'Moderator' });
await a.submit('settings/user-role-edit.php', { update_role: 'Moderator', 'permissions[]': ['ban_users', 'edit_channel', 'edit_channel_user', 'tkl_add', 'tkl_del', 'nb_add', 'nb_del'] });
await a.submit('settings/user-role-edit.php', { add_role_name: 'Helper' });
await a.submit('settings/user-role-edit.php', { update_role: 'Helper', 'permissions[]': ['edit_channel'] });
const ACCOUNTS = [
  ['moderator', 'Moderator', 'Mia', 'Moderator', 'Handles the day shift'],
  ['helper', 'Helper', 'Hank', 'Helper', 'Answers questions in #help'],
  ['viewer', 'Read-Only', 'Vera', 'Viewer', 'Can look but not touch'],
];
for (const [u, role, fn, ln, bio] of ACCOUNTS)
  await a.submit('settings/index.php', { do_add_user: '1', user_add: u, password: 'Demo!pass1', user_role: role,
    user_email: `${u}@example.test`, add_first_name: fn, add_last_name: ln, user_bio: bio });

const BANS = [
  ['*@spam-farm.example', 'gline', '7d', 'Spam bots'],
  ['*@open-proxy.example', 'gline', '30d', 'Open proxy'],
  ['baduser@*.example.net', 'kline', '1d', 'Repeated flooding'],
  ['*@198.51.100.*', 'gzline', '', 'Abusive network (permanent)'],
  ['*@203.0.113.7', 'zline', '12h', 'Connection flood'],
];
for (const [host, type, dur, reason] of BANS)
  await a.submit('server-bans/index.php', { do_add_ban: '1', edit_existing: '', ban_host: host, ban_type: type, ban_duration: dur, ban_reason: reason }, 'server-bans/');
await a.submit('server-bans/index.php', { do_add_ban: '1', edit_existing: '', ban_host: '*@soft.example', ban_type: 'gline', ban_soft: 'on', ban_duration: '2d', ban_reason: 'Soft ban: logged in users may connect' }, 'server-bans/');

for (const [mask, reason] of [['*Serv', 'Reserved for services'], ['#warez*', 'Not on this network'], ['Admin*', 'Reserved for staff']])
  await a.submit('server-bans/name-bans.php', { tkl_add: mask, banlen_w: '0w', banlen_d: '0d', banlen_h: '0d', ban_reason: reason });

await a.submit('server-bans/ban-exceptions.php', { tkl_add: '*@trusted.example', 'bantype[]': ['G', 'k', 'Z'], banlen_w: '0w', banlen_d: '0d', banlen_h: '0d', ban_reason: 'Our office' });
await a.submit('server-bans/ban-exceptions.php', { tkl_add: '*@monitoring.example', 'bantype[]': ['c', 'm'], banlen_w: '0w', banlen_d: '30d', banlen_h: '0d', ban_reason: 'Uptime checker' });

const SPAM = [
  ['*buy cheap followers*', 'simple', ['target_usermsg', 'target_chanmsg'], 'block', 'Advertising'],
  ['*free bitcoin*', 'simple', ['target_usermsg', 'target_chanmsg', 'target_quit'], 'gline', 'Scam'],
  ['^!trigger(spam|flood)', 'regex', ['target_chanmsg'], 'warn', 'Bot trigger'],
];
for (const [mask, type, targets, action, reason] of SPAM) {
  const form = { sf_add: mask, matchtype: type, sf_bantype: action, banlen_w: '0w', banlen_d: '1d', banlen_h: '0d', ban_reason: reason };
  for (const t of targets) form[t] = 'on';
  await a.submit('server-bans/spamfilter.php', form);
}

log('');
log('READY');
log(`Panel:     ${HOST}${BASE}`);
log(`Logins:    ${ADMIN.user} / ${ADMIN.pass}   (Super-Admin)`);
log(`           moderator / Demo!pass1   (Moderator role)`);
log(`           helper / Demo!pass1      (Helper role)`);
log(`           viewer / Demo!pass1, ${READER.user} / ${READER.pass}   (Read-Only)`);
log(`IRC:       127.0.0.1:${process.env.IRC_PORT || 16667} (plain), to join yourself`);
log('Tricky data: #tricky, #rock&roll (and its twin #rock&amp;roll), #pаypal / #pay​pal / #paypal, user "tricky"');
log('Keep this running: the IRC users stay online until you press Ctrl+C.');

// keep the IRC users online
setInterval(() => {}, 1 << 30);
process.on('SIGINT', () => { for (const c of clients) try { c.close(); } catch { } process.exit(0); });
