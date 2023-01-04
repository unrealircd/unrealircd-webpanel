/* Overview menu selector */
const tabs = document.querySelectorAll('[data-tab-target]');
const tabContents = document.querySelectorAll('[data-tab-content]')
tabs.forEach(tab => {
    tab.addEventListener('click', () => {
        const target = document.querySelector(tab.dataset.tabTarget)
        tabContents.forEach(tabContent => {
            tabContent.classList.remove('active')
        })
        tabs.forEach(tab => {
            tab.classList.remove('active')
        })
        tab.classList.add('active');
        target.classList.add('active');
    })
})


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
function toggle_sf(source) {
    checkboxes = document.getElementsByName("sf[]");
    for (var i = 0, n = checkboxes.length; i < n; i++) {
        checkboxes[i].checked = source.checked;
    }
}