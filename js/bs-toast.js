
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
