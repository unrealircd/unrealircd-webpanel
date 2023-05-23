/**
 * Generate a bootstrap modal
 * Mandatory:
 * @param {string} title - The modal title
 * @param {string} body - HTML for the body
 * @param {string} footer - HTML for the footer
 * 
 * Optional:
 * @param {string|null} size - the bootstrap size category for modals (sm, lg, xl). Default is null.
 * @param {boolean} static - whether or not to make the backdrop static, forcing the user to respond to the dialog. Default is false
 * @param {boolean} show - whether or not to automatically show the modal. Default is false.
 * @param {boolean} closebutton - display and allow the close button. Default is true.
 * @returns {string} returns the ID
 */

function bsModal(title, body, footer, size = null, static = false, show = false, closebutton = true)
{
    /* generate a random number between 1000 and 90000 to use as an id */
    const min = 1000;
    const max = 90000;
    id = Date.now().toString(36); // base36 unique id
    ourSize = "";
    if (size)
        ourSize += "modal-" + size;

    const m1 = document.createElement("div");
    const m2 = m1.cloneNode();
    const m3 = m1.cloneNode();
    const mHeader = m1.cloneNode();
    const mBody = m1.cloneNode();
    const mFooter = m1.cloneNode();

    m1.classList.add("modal", "fade");
    m1.id = id;
    m1.role = "dialog";
    m1.ariaHidden = "true";

    m2.classList.add("modal-dialog", "modal-dialog-centered");
    if (ourSize.length)
        m2.classList.add(ourSize);

    m2.role = "document";
    m2.id = id + "-2";

    m3.classList.add("modal-content");
    m3.id = id + "-3";

    mHeader.classList.add("modal-header");
    mHeader.id = id + "-header";
    mHeader.innerHTML =`<h5 class="modal-title" id="` + id + `"-title">` + title + `</h5>`;

    if (closebutton)
        mHeader.innerHTML +=   `<button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span></button>`;
    
    mBody.classList.add("modal-body");
    mBody.id = id + "-body";
    mBody.innerHTML = body;

    mFooter.classList.add("modal-footer");
    mFooter.id = id + "-footer";
    mFooter.innerHTML = footer;

    m1.appendChild(m2);
    m2.appendChild(m3);
    m3.appendChild(mHeader);
    m3.appendChild(mBody);
    m3.appendChild(mFooter);

    document.body.append(m1);
    
    if (static)
        $('#' + m1.id).modal({ backdrop: "static" });

    if (show)
        $('#' + m1.id).modal('show');

    return m1.id;
}
