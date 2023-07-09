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


function create_plugin_info_modal(modname)
{
    let found = false;
    fetch(BASE_URL + 'api/plugin.php')
    .then(response => response.json()) // Parse the response as JSON
    .then(data => {
        for (let i = 0; data.list[i]; i++)
        {
            if (data.list[i].name == modname)
            {
                found = true;
                const modal = bsModal(
                    "<i>Information about " + data.list[i].title + "</i>", // title
                    "<div class=\"" + data.list[i].name + "_screenshots\"><i class=\"fa fa-spinner\" aria-hidden=\"true\"></i></div><div class=\"" + data.list[i].name + "_description\"><i class=\"fa fa-spinner\" aria-hidden=\"true\"></i></div>",
                    "<div id=\""+modname+"closebtn\" class=\"btn btn-danger\">Close</div>", null, true, true, true
                );
                let modalclose = document.getElementById(modal);
                modalclose.addEventListener('click', (e) => {
                    $("#"+modal).modal('hide');
                });
                boobs = document.getElementById(modal + '-body');
                boobs.innerHTML = "";
                if (data.list[i].screenshot.length)
                {
                    boobs.innerHTML += ` <div style="padding-left: 0px;  padding-right: 0px;">
                                        <img src="` + (data.list[i].screenshot[0] ?? "") + `" class="screenshot img-fluid" alt="` + data.list[i].screenshot[1] + ` style="max-width: 100%; height:auto">
                                    </div>`;
                }
                boobs.innerHTML += "<p class=\"alert alert-primary mt-2\"><i><b>Description:</i></b><br>" + atob(data.list[i].readme.replace(["\n",""],["<br>","<br>"])) + "</p>";
                boobs.innerHTML +=  `<div class="alert alert-dark">
                                    <table class="table">
                                        <tr>
                                                <th scope="row">Title</th>
                                                <td>`+data.list[i].title+`</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Description</th>
                                                <td>`+data.list[i].description+`</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Version</th>
                                                <td>`+data.list[i].version+`</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Author</th>
                                                <td>`+data.list[i].author+`</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Min Version Required</th>
                                                <td>`+data.list[i].minver+`</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Max Version</th>
                                                <td>`+data.list[i].maxver+`</td>
                                            </tr>
                                            
                                        </table></small>
                                    </div>`;
            }
        }
        if (!found)
        {
            bsModal("Hmmm. Something went wrong.", "It seems we can't find any information about that plugin! Maybe it's built-in? If not, please report this via <a href='https://github.com/unrealircd/unrealircd-webpanel'>GitHub</a> or <a href='mailto:v.a.pond@outlook.com'>email</a>. ", "", null, null, true, true);
        }
    })
    .catch(error => {
        // Handle any errors that occur during the request
        bsModal("Hmmm. Something went wrong.", "It seems we can't query our own API! Please report this via <a href='https://github.com/unrealircd/unrealircd-webpanel'>GitHub</a> or <a href='mailto:v.a.pond@outlook.com'>email</a>. ", "", null, null, true, true);
    });
}


function requestInstall(name, uninstall = false)
{
    let inst = (uninstall) ? "uninstall" : "install";
    var xhr = new XMLHttpRequest();

    xhr.onload = function() {
        if (xhr.status === 200) {
            var response = JSON.parse(xhr.responseText);
            console.log(response.success);
            let install_button = document.getElementById(name+'install');
            if (response.success !== undefined)
            {
                if (install_button)
                {
                    install_button.innerHTML = (inst == "uninstall") ? "Install" : "Uninstall";
                    install_button.classList.replace('btn-secondary', (inst == "uninstall") ? 'btn-primary' : 'btn-outline-danger');
                    setTimeout(() => { location.reload() }, 500);
                }
            }
            else
            {
                if (install_button)
                {
                    install_button.innerHTML = (inst == "uninstall") ? "Uninstall" : "Install";
                    install_button.classList.replace('btn-secondary', (inst == "uninstall") ? 'btn-outline-danger' : 'btn-primary');
                    setTimeout(() => { location.reload() }, 2000);
                }
            }
        }
    };

    xhr.open('GET', BASE_URL + 'api/plugin.php?'+inst+'=' + name, true);
    xhr.send();
    return true;
}


function create_plugin_info_modal(modname)
{
    let found = false;
    fetch(BASE_URL + 'api/plugin.php')
    .then(response => response.json()) // Parse the response as JSON
    .then(data => {
        for (let i = 0; data.list[i]; i++)
        {
            if (data.list[i].name == modname)
            {
                found = true;
                const modal = bsModal(
                    "<i>Information about " + data.list[i].title + "</i>", // title
                    "<div class=\"" + data.list[i].name + "_screenshots\"><i class=\"fa fa-spinner\" aria-hidden=\"true\"></i></div><div class=\"" + data.list[i].name + "_description\"><i class=\"fa fa-spinner\" aria-hidden=\"true\"></i></div>",
                    "<div id=\""+modname+"closebtn\" class=\"btn btn-danger\">Close</div>", null, true, true, true
                );
                let modalclose = document.getElementById(modal);
                modalclose.addEventListener('click', (e) => {
                    $("#"+modal).modal('hide');
                });
                boobs = document.getElementById(modal + '-body');
                boobs.innerHTML = "";
                if (data.list[i].screenshot.length)
                {
                    boobs.innerHTML += ` <div style="padding-left: 0px;  padding-right: 0px;">
                                        <img src="` + (data.list[i].screenshot[0] ?? "") + `" class="screenshot img-fluid" alt="` + data.list[i].screenshot[1] + ` style="max-width: 100%; height:auto">
                                    </div>`;
                }
                boobs.innerHTML += "<p class=\"alert alert-primary mt-2\"><i><b>Description:</i></b><br>" + atob(data.list[i].readme.replace(["\n",""],["<br>","<br>"])) + "</p>";
                boobs.innerHTML +=  `<div class="alert alert-dark">
                                    <table class="table">
                                        <tr>
                                                <th scope="row">Title</th>
                                                <td>`+data.list[i].title+`</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Description</th>
                                                <td>`+data.list[i].description+`</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Version</th>
                                                <td>`+data.list[i].version+`</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Author</th>
                                                <td>`+data.list[i].author+`</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Min Version Required</th>
                                                <td>`+data.list[i].minver+`</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Max Version</th>
                                                <td>`+data.list[i].maxver+`</td>
                                            </tr>
                                            
                                        </table></small>
                                    </div>`;
            }
        }
        if (!found)
        {
            bsModal("Hmmm. Something went wrong.", "It seems we can't find any information about that plugin! Maybe it's built-in? If not, please report this via <a href='https://github.com/unrealircd/unrealircd-webpanel'>GitHub</a> or <a href='mailto:v.a.pond@outlook.com'>email</a>. ", "", null, null, true, true);
        }
    })
    .catch(error => {
        // Handle any errors that occur during the request
        bsModal("Hmmm. Something went wrong.", "It seems we can't query our own API! Please report this via <a href='https://github.com/unrealircd/unrealircd-webpanel'>GitHub</a> or <a href='mailto:v.a.pond@outlook.com'>email</a>. ", "", null, null, true, true);
    });
}
