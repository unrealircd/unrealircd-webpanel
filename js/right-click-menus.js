/**
 * Right-click menus for use around the webpanel
 */
let selection = null;
let click_target = null;
function can_clipboard()
{
	if (typeof navigator.clipboard !== "undefined" && typeof navigator.clipboard.writeText === "function"
		&& typeof navigator.clipboard.readText === "function")
		return true;
	return false;
}

async function paste_from_clipboard()
{
	let text = await navigator.clipboard.readText();
	click_target.value = text;
}

async function copy_to_clipboard()
{
	navigator.clipboard.writeText(selection);
}

function build_rclick_menu()
{
	const m = document.createElement('div');
	m.classList.add('nav-item','list-group');
	m.id = 'rclickmenu';

	const m1 = document.createElement('div');
	m1.classList.add('item', 'list-group-item-action');
	m1.id = 'rclick_opt1';
}

var rclickmenu = document.getElementById('rclickmenu');

document.addEventListener("click", (e) =>
{
	rclickmenu.classList.remove("visible");
});


document.addEventListener("contextmenu", (event) =>
{
	event.preventDefault();
	click_target = event.target;

	rclickmenu.classList.remove("visible"); // hide it if it was already elsweyr
	var { clientX: mouseX, clientY: mouseY } = event;
	
	rclickmenu.style.top = `${mouseY}px`;
	rclickmenu.style.left = `${mouseX}px`;

	/* "Copy" option */
	selection = window.getSelection().toString();

	if (selection.length == 0 || !can_clipboard())
		document.getElementById('rclick_opt1').style.display = 'none';

	else if (can_clipboard())
		document.getElementById('rclick_opt1').style.display = '';

	/* Check if the browser supports pasting */
	if (!can_clipboard() || (!click_target || click_target.tagName.toLowerCase() !== "input"))
		document.getElementById('rclick_opt2').style.display = 'none';

	else if (click_target && click_target.tagName.toLowerCase() === "input")
		document.getElementById('rclick_opt2').style.display = '';

	setTimeout(() => { rclickmenu.classList.add("visible"); });
});
document.addEventListener('keydown', (event) => {
	if (event.key === 'Escape')
	{
		rclickmenu.classList.remove("visible");
	}
});