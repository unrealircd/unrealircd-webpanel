// Connects ordinary (non-oper) IRC users that put attacker-controlled data
// into the IRC server, and stays connected (channels vanish when empty).
// Prints READY when done. Started and stopped by run-tests.mjs.
// The "payloads" only set a marker variable (top.X<letter>=1) that the
// browser tests look for; they do nothing else.
import { Irc, sleep } from './lib.mjs';

const P = (id) => `<img/src/onerror=top.X${id}=1>`;
const keep = [];
async function user(nick, real) { const c = new Irc(nick, { real }); await c.connect(); keep.push(c); return c; }

// Log entries: realname, failed OPER, QUIT reason (F01, F08, F23)
const q = new Irc('quitter', { real: '<img src=x onerror=top.XR=1>' });
await q.connect();
q.raw(`OPER ${P('O')} wrongpass`);
await q.wait(/ 491 /, 10000).catch(() => {});
await sleep(1000);
await q.quit('<img src=x onerror=top.XQ=1>');

// Users that stay online
await user('httpuser', 'https://evil.example/phish');           // F24
await user('gecosuser', '<img src=x onerror=top.XU=1>');         // F08 (search on realname)
await user('banme', 'banme');                                    // F25

// Channel data (F02): ~text ban, +e, +k, +L, topic, channel name with markup
const op = await user('chanop', 'chanop');
op.raw('JOIN #evil');
await op.wait(/ 366 .*#evil/);
op.raw(`MODE #evil +b ~text:block:'>${P('B')}`);
op.raw(`MODE #evil +e ${P('E')}!*@*`);
op.raw('MODE #evil +k <svg/onload=top.XK=1>');
op.raw('MODE #evil +l 10');
op.raw('MODE #evil +L #<svg/onload=top.XL=1>');
op.raw('TOPIC #evil :<img src=x onerror=top.XT=1> topic');
op.raw('JOIN #<svg/onload=top.XC=1>');
await sleep(1000);

// Decoy channel (F10/F16): the real "#a&b" and its escaped twin "#a&amp;b"
op.raw('JOIN #a&b');
const decoy = await user('decoyop', 'decoy');
decoy.raw('JOIN #a&amp;b');
await decoy.wait(/ 366 .*#a&amp;b/);
decoy.raw('TOPIC #a&amp;b :I am the DECOY');
await op.wait(/ 366 .*#a&b/);
op.raw('TOPIC #a&b :I am the REAL channel');

// Invalid UTF-8 (F19, F20) - needs set::allowed-channelchars any
op.rawBytes(Buffer.concat([Buffer.from('JOIN #bad'), Buffer.from([0xff])]));
decoy.rawBytes(Buffer.concat([Buffer.from('JOIN #bad'), Buffer.from([0xEF, 0xBF, 0xBD])]));
op.raw('JOIN #topic');
await op.wait(/ 366 .*#topic/);
op.rawBytes(Buffer.concat([Buffer.from('TOPIC #topic :start'), Buffer.from([0xff]), Buffer.from(' x END')]));

// +k 0 (F21)
op.raw('JOIN #keyzero');
await op.wait(/ 366 .*#keyzero/);
op.raw('MODE #keyzero +kl 0 5');

// Lookalike names (F26): Cyrillic 'а', a zero-width space, and the real one
op.raw('JOIN #pаypal');
op.raw('JOIN #pay​pal');
op.raw('JOIN #paypal');
await sleep(2000);

console.log('READY');
setInterval(() => {}, 1 << 30);
