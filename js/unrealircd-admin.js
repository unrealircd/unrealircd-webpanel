


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

function generate_bs_notif(id, title, body)
{
    document.write('<div class="position-fixed bottom-0 right-0 p-3" style="z-index: 5; right: 0; bottom: 50px;">');
    document.write('    <div id="' + id + '" class="toast hide" role="alert" aria-live="assertive" aria-atomic="true" data-delay="10000">');
    document.write('        <div class="toast-header">');
    document.write('            <strong class="mr-auto">' + title + '</strong>');
    document.write('            <small>11 mins ago</small>');
    document.write('            <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">');
    document.write('                <span aria-hidden="true">&times;</span>');
    document.write('            </button>');
    document.write('        </div>');
    document.write('        <div class="toast-body">');
    document.write(body);
    document.write('        </div>');
    document.write('        </div>');
    document.write('</div>');
}