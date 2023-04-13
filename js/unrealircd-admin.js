


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
    
    const notiftime = document.createElement('small');
    notiftime.textContent = "Just now"; // always just now I think right :D

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

$("#myModal").on('shown.bs.modal', function(){
    $("#CloseButton").focus();
});
function StreamNotifs(e)
{
    var data;
    try {
        data = JSON.parse(e.data);
    } catch(e) {
        return;
    }
    title = data.subsystem + '.' + data.event_id;
    msg = data.msg;
    generate_notif(title, msg);
}
function StartStreamNotifs(url)
{
    if (!!window.EventSource) {
        var source = new EventSource(url);
        source.addEventListener('message', StreamNotifs, false);
    }
}