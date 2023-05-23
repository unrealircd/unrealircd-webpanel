<?php
/**
 * Right-click menus
 */
if (!isset($config)) die;

$menu = [
	[
		"text" => "Copy",
		"onclick" => "copy_to_clipboard(window.getSelection().toString())",
		"icon" => "fa-clipboard"
	],
	[
		"text" => "Paste",
		"onclick" => "paste_from_clipboard()",
		"icon" => "fa-paint-brush",
	],
];

// register our menu
Hook::run(HOOKTYPE_RIGHTCLICK_MENU, $menu);

?>
<!-- Right-click menu -->

<div id='rclickmenu' class="nav-item list-group">
	<div id="rclick_opt1" class="item list-group-item-action" onclick="copy_to_clipboard(window.getSelection().toString())">Copy</div>
	<div id="rclick_opt2" class="item list-group-item-action" onclick="paste_from_clipboard()">Paste</div>
	<div id="rclick_opt3" class="item list-group-item-action">Search</div>

	<a class="item list-group-item-action" href="https://www.unrealircd.org/docs/UnrealIRCd_webpanel" target="_blank">Documentation Wiki</a>
</div>