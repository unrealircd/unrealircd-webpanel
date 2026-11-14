
/* Escape a value for use in HTML (element content and quoted attributes).
 * Use this (or textContent / jQuery .text()) for anything that comes
 * from IRC, the RPC server or the URL.
 */
function escapeHTML(t)
{
    if (t === null || t === undefined)
        return '';
    return ('' + t)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

/* Characters that do not show (zero-width, bidi controls, other format
 * and control characters, and blank-looking fillers).
 */
const IRC_INVISIBLE_RE = /[\p{Cf}\p{Cc}ᅟᅠㅤﾠ⠀]/gu;

/* Escape an IRC name (channel, nick) for display, and make lookalike
 * names stand out: invisible characters are shown as their code point,
 * and names that mix Latin with Cyrillic or Greek, or that contain
 * invalid UTF-8 (shown by UnrealIRCd as U+FFFD), get a warning badge.
 */
function irc_name_html(name)
{
    if (name === null || name === undefined)
        return '';
    name = '' + name;
    var out = '';
    var last = 0;
    for (const m of name.matchAll(IRC_INVISIBLE_RE))
    {
        out += escapeHTML(name.substring(last, m.index));
        var cp = m[0].codePointAt(0).toString(16).toUpperCase().padStart(4, '0');
        out += '<span class="badge badge-warning" title="Invisible character">U+' + cp + '</span>';
        last = m.index + m[0].length;
    }
    out += escapeHTML(name.substring(last));
    if (name.includes('�'))
        out += ' <span class="badge badge-danger" title="This name contains bytes that are not valid UTF-8. The panel cannot address it reliably.">invalid UTF-8</span>';
    if (/\p{Script=Latin}/u.test(name) && /[\p{Script=Cyrillic}\p{Script=Greek}]/u.test(name))
        out += ' <span class="badge badge-warning" title="This name mixes Latin with Cyrillic or Greek letters, it may imitate another name">mixed scripts</span>';
    return out;
}

/* DataTables render function for IRC names.
 * Note: for the 'filter' type, DataTables puts the value into a (detached)
 * element with innerHTML whenever it contains a '&', so that must be
 * escaped as well. DataTable.render.text() does the same.
 */
function render_irc_name(data, type, row)
{
    if (type == 'display')
        return irc_name_html(data);
    if (type == 'filter')
        return escapeHTML(data);
    return data;
}

/* Wrap a DataTables render function that builds HTML for 'display',
 * so that all the other types get safe values.
 */
function dt_safe_render(display_fn)
{
    return function (data, type, row, meta) {
        if (type == 'display')
            return display_fn(data, type, row, meta);
        if (type == 'filter')
            return escapeHTML(data);
        return data;
    };
}

/* A shortened, escaped text column (like DataTable.render.ellipsis
 * with escaping on), that also gives safe 'filter' values.
 */
function dt_text_ellipsis(cutoff)
{
    var display = DataTable.render.ellipsis(cutoff, false, true);
    return function (data, type, row, meta) {
        if (type == 'display')
            return display(data, type, row, meta);
        if (type == 'filter')
            return escapeHTML(data);
        return data;
    };
}

/* TKL (un)select all checkbox */
function toggle_tkl(source) {
    checkboxes = document.getElementsByName("tklch[]");
    for (var i = 0, n = checkboxes.length; i < n; i++) {
        checkboxes[i].checked = source.checked;
    }
}


/* TKL (un)select all checkbox */
function toggle_user(source) {
    checkboxes = document.getElementsByName("userch[]");
    for (var i = 0, n = checkboxes.length; i < n; i++) {
        checkboxes[i].checked = source.checked;
    }
}


/* TKL (un)select all checkbox */
function toggle_server(source) {
    checkboxes = document.getElementsByName("serverch[]");
    for (var i = 0, n = checkboxes.length; i < n; i++) {
        checkboxes[i].checked = source.checked;
    }
}

/* TKL (un)select all checkbox */
function toggle_sf(source) {
    checkboxes = document.getElementsByName("sf[]");
    for (var i = 0, n = checkboxes.length; i < n; i++) {
        checkboxes[i].checked = source.checked;
    }
}

function toggle_chanbans(source) {
    checkboxes = document.getElementsByName("cb_checkboxes[]");
    for (var i = 0, n = checkboxes.length; i < n; i++) { 
        checkboxes[i].checked = source.checked;
    }
}

function toggle_chanexs(source) {
    checkboxes = document.getElementsByName("ce_checkboxes[]");
    for (var i = 0, n = checkboxes.length; i < n; i++) { 
        checkboxes[i].checked = source.checked;
    }
}

function toggle_chaninvs(source) {
    checkboxes = document.getElementsByName("ci_checkboxes[]");
    for (var i = 0, n = checkboxes.length; i < n; i++) { 
        checkboxes[i].checked = source.checked;
    }
}

function toggle_checkbox(source) {
    checkboxes = document.getElementsByName("checkboxes[]");
    for (var i = 0, n = checkboxes.length; i < n; i++) {
        checkboxes[i].checked = source.checked;
    }
}

/* Popup notifications */

function generate_notif(title, body)
{
    /* generate a random number between 1000 and 90000 to use as an id */
    const min = 1000;
    const max = 90000;
    const id = Math.floor(Math.random() * (max - min + 1)) + min;

    const toast = document.createElement('div');
    toast.classList.add('toast', 'hide');
    toast.id = 'toast' + id;
    toast.role = 'alert';
    toast.ariaLive = 'assertive';
    toast.ariaAtomic = 'true';
    toast.setAttribute('data-delay', '10000');

    const header = document.createElement('div');
    header.classList.add('toast-header');

    const theTitle = document.createElement('strong');
    theTitle.classList.add('mr-auto');
    theTitle.textContent = title;
    
    const notiftime = document.createElement('div');
    notiftime.classList.add('badge', 'rounded-pill', 'badge-primary', 'ml-1');
    notiftime.textContent = 'Just now'; // always just now I think right :D

    const closebutton = document.createElement('button');
    closebutton.type = 'button';
    closebutton.classList.add('ml-2', 'mb-1', 'close');
    closebutton.setAttribute('data-dismiss', 'toast');
    closebutton.ariaLabel = 'Close';

    const closebuttonspan = document.createElement('span');
    closebuttonspan.ariaHidden = 'true';
    closebuttonspan.innerHTML = "&times;";

    const toastbody = document.createElement('div');
    toastbody.classList.add('toast-body');
    toastbody.textContent = body;


    /* put it all together */
    closebutton.appendChild(closebuttonspan);
    header.appendChild(theTitle);
    header.appendChild(notiftime);
    header.appendChild(closebutton);
    toast.appendChild(header);
    toast.appendChild(toastbody);
    document.getElementById('toaster').append(toast);

    $('#' + toast.id).toast('show');
}

/* Popups for warnings/errors from the IRC server log.
 * Log messages can contain text chosen by any IRC user (for example the
 * name given in a failed OPER attempt), so:
 * - the popup is labeled as an IRC server log event,
 * - the text is shortened, the full entry is on the Logs page,
 * - the same event type pops up at most once per 30 seconds, and
 *   at most 3 popups are shown at the same time.
 */
const notif_last_shown = {};
function StreamNotifs(e)
{
    var data;
    try {
        data = JSON.parse(e.data);
    } catch(e) {
        return;
    }
    var key = data.subsystem + '.' + data.event_id;
    var now = Date.now();
    if (notif_last_shown[key] && (now - notif_last_shown[key] < 30000))
        return;
    if (document.querySelectorAll('#toaster .toast.show').length >= 3)
        return;
    notif_last_shown[key] = now;
    var title = 'IRC server log: ' + key;
    var msg = '' + (data.msg === undefined ? '' : data.msg);
    if (msg.length > 150)
        msg = msg.substring(0, 149) + '…';
    generate_notif(title, msg);
}

function StartStreamNotifs(url)
{
    if (!!window.EventSource) {
        var source = new EventSource(url);
        source.addEventListener('message', StreamNotifs, false);
    }
}

/* Log streamer */
function NewLogEntry(e)
{
    var data;
    try {
        data = JSON.parse(e.data);
    } catch(e) {
        return;
    }

    if (data.sync_option != "sync_now")
    {
        var sync = (data.sync_option == "no_sync") ? false : true;
        delete data.sync_option;

        data_list_table.row.add({
            'Time':data.timestamp,
            'Level':data.level,
            'Subsystem':data.subsystem,
            'Event':data.event_id,
            'Message':data.msg,
            'Raw':JSON.stringify(data)});

        if (!sync)
            return;
    }
    data_list_table.draw(true);
    data_list_table.rows().invalidate();
    data_list_table.searchPanes.rebuildPane();
}

function StartLogStream(url)
{
    var source = new EventSource(url);
    source.addEventListener('message', NewLogEntry, false);
}

  