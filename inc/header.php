<?php
$nav_shown = true;
$arr = []; Hook::run(HOOKTYPE_PRE_HEADER, $arr);

?>
<!DOCTYPE html>
<head>
<div class="media">
<div class="media-body">

	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="HandheldFriendly" content="true">

<link href="<?php echo h(get_config("base_url")); ?>css/unrealircd-admin.css" rel="stylesheet">
<link href="<?php echo h(get_config("base_url")); ?>css/right-click.css" rel="stylesheet">
<style>
.big-page-item:hover, .big-page-item:active, .nav-link {
	color: black;
}
</style>

<link rel="stylesheet" href="<?php echo h(get_config("base_url")); ?>css/datatables.min.css" />

<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

<!-- Font Awesome JS -->
<script defer src="https://use.fontawesome.com/releases/v6.2.1/js/solid.js" integrity="sha384-tzzSw1/Vo+0N5UhStP3bvwWPq+uvzCMfrN1fEFe+xBmv1C/AtVX5K0uZtmcHitFZ" crossorigin="anonymous"></script>
<script defer src="https://use.fontawesome.com/releases/v6.2.1/js/fontawesome.js" integrity="sha384-6OIrr52G08NpOFSZdxxz1xdNSndlD4vdcf/q2myIUVO0VsqaGHJsB0RaBE01VTOY" crossorigin="anonymous"></script>

<!-- Font Awesome icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css">
<title>UnrealIRCd Panel</title>
<link rel="icon" type="image/png" href="<?php echo h(get_config("base_url")); ?>img/unreal.png">
</head>
<body role="document">
<div aria-live="polite" aria-atomic="true">
  <div id="toaster" style="right: 0; bottom: 50px; z-index: 5;" class="position-fixed bottom-0 right-0 p-4">
	<!-- insert your javascript bread in here to make toast -->
  </div>
</div>
<script> const BASE_URL = <?php echo js_value(get_config("base_url")); ?>; </script>
<meta name="csrf-token" content="<?php echo h(panel_csrf_token()); ?>">
<script>
/* CSRF protection: add the session's token to every form that is posted,
 * and to every non-GET fetch()/XMLHttpRequest to the panel itself.
 */
(function () {
	const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;
	function add_token(form) {
		if ((form.method || '').toLowerCase() !== 'post' || form.querySelector('input[name="csrf_token"]'))
			return;
		var i = document.createElement('input');
		i.type = 'hidden';
		i.name = 'csrf_token';
		i.value = CSRF_TOKEN;
		form.appendChild(i);
	}
	function same_origin(url) {
		try { return new URL(url, window.location.href).origin === window.location.origin; }
		catch (e) { return false; }
	}
	/* forms: on load, and just before submit (for forms added later) */
	document.addEventListener('DOMContentLoaded', function () {
		document.querySelectorAll('form').forEach(add_token);
	});
	document.addEventListener('submit', function (e) { if (e.target && e.target.tagName === 'FORM') add_token(e.target); }, true);
	var orig_submit = HTMLFormElement.prototype.submit;
	HTMLFormElement.prototype.submit = function () { add_token(this); return orig_submit.apply(this, arguments); };
	/* fetch() */
	var orig_fetch = window.fetch;
	window.fetch = function (resource, init) {
		init = init || {};
		var method = (init.method || (resource && resource.method) || 'GET').toUpperCase();
		var url = (typeof resource === 'string' || resource instanceof URL) ? resource : resource.url;
		if (method !== 'GET' && method !== 'HEAD' && same_origin(url)) {
			var headers = new Headers(init.headers || (resource instanceof Request ? resource.headers : undefined));
			headers.set('X-CSRF-Token', CSRF_TOKEN);
			init.headers = headers;
		}
		return orig_fetch.call(this, resource, init);
	};
	/* XMLHttpRequest */
	var orig_open = XMLHttpRequest.prototype.open;
	var orig_send = XMLHttpRequest.prototype.send;
	XMLHttpRequest.prototype.open = function (method, url) {
		this._csrf_needed = (String(method).toUpperCase() !== 'GET' && String(method).toUpperCase() !== 'HEAD' && same_origin(url));
		return orig_open.apply(this, arguments);
	};
	XMLHttpRequest.prototype.send = function () {
		if (this._csrf_needed)
			this.setRequestHeader('X-CSRF-Token', CSRF_TOKEN);
		return orig_send.apply(this, arguments);
	};
})();
</script>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.min.js" integrity="sha384-ZvpUoO/+PpLXR1lu4jmpXWu80pZlYUAfxl5NsBMWOEPSjUn/6Z/hRTt8+pR6L4N2" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js" integrity="sha384-+sLIOodYLS7CIrQpBjl+C7nPvqq+FbNUBDunl/OZv93DB7Ln/533i8e/mZXLi/P+" crossorigin="anonymous"></script>
<script src="<?php echo h(get_config("base_url")); ?>js/unrealircd-admin.js"></script>
<!-- <script defer src="<?php echo h(get_config("base_url")); ?>js/right-click-menus.js"></script> -- We're not doing this yet XD -->
<script src="<?php echo h(get_config("base_url")); ?>js/bs-modal.js"></script>
<script src="<?php echo h(get_config("base_url")); ?>js/bs-toast.js"></script>
<script src="<?php echo h(get_config("base_url")); ?>js/datatables.min.js"></script>
<script src="<?php echo h(get_config("base_url")); ?>js/datatables-natural-sort.js"></script>
<script src="<?php echo h(get_config("base_url")); ?>js/datatables-ellipsis.js"></script>
<script src="<?php echo h(get_config("base_url")); ?>js/moment-with-locales.min.js"></script> 
<link rel="manifest" href="<?php echo h(get_config("base_url")); ?>manifest.json">	
<script>
		console.log("Attempting to add service worker...");
		if ("serviceWorker" in navigator) {
			navigator.serviceWorker.register(BASE_URL+"js/service-worker.js")
			.then((registration) => {
			console.log("Service Worker registered:", registration);
			})
		.catch((error) => {
			console.log("Service Worker registration failed:", error);
			});
		}

		function timeoutCheck() {
			var xhttp = new XMLHttpRequest();
			xhttp.onreadystatechange = function() {
				if (this.readyState == 4 && this.status == 200) {
					var data = JSON.parse(this.responseText);
					if (data.session == 'none')
						window.location = BASE_URL + 'login/?timeout=1&redirect=' + encodeURIComponent(window.location.pathname + window.location.search);
				}
			};
			xhttp.open("GET", BASE_URL + "api/timeout.php", true);
			xhttp.send();
		}

		timeoutCheck();
		StartStreamNotifs(BASE_URL + "api/notification.php");
		setInterval(timeoutCheck, 15000);

		function change_active_server(name)
		{
			fetch(BASE_URL + 'api/set_rpc_server.php', {
			      method:'POST',
			      headers: {'Content-Type':'application/x-www-form-urlencoded'},
			      body: 'server='+encodeURIComponent(name)
			      })
			.then(response => response.json())
			.then(data => {
				if (data.error)
					alert("Could not change the RPC server: " + data.error);
				location.reload();
			})
			.catch(error => {
				alert("Could not change the RPC server");
			});
		}
</script>
<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
<style>
	#optionsopen {
		transition: left 0.3s;
	}
	#optionsclose {
		transition: left 0.3s;
	}
	.w3-sidebar {
		top: 52px;
		color: white;
		transition: left 0.3s;
		width: 160px;
	}
	.container-fluid {
		transition: padding-left 0.3s;
	}
	.list-group-item-action {
		color: #e0e0e0;
	}
	.list-group-item-action:visited{
		color: black;
	}
</style>
<?php $a = []; Hook::run(HOOKTYPE_HEADER, $a); ?>
<nav id="sidebarlol" style="left:0;overflow:auto" class="w3-sidebar navbar-expand-md bg-dark padding-top me-5 ma-5">
<div class="list-group">
	<div class="badge badge-secondary rounded-pill">Main Menu</div>
	<?php 

function show_page_item($name, $page, $nestlevel, $small = false)
{
	$active_page = NULL;
	$icon = $style = "";
	$class = "nav-link nav-item";
	if ($small)
		$class .= " list-group-item-action";
	//if (is_string($active_page) && $page == $active_page)
	//	$class .= " active";

	$is_link = isset($page["script"]) ? true : false;

	if ($nestlevel > 0)
	{
		echo "<small>";
		$name = "&nbsp; ".$name;
		$style = "padding-bottom: 1px; padding-top: 1px";
	} else {
		echo "<b>";
	}
	if (!$is_link)
	{
		$style = "padding-bottom: 0px;";
	} else {
		$url = $page["script"];
		if (str_ends_with($url, "/index.php"))
			$url = str_replace('/index.php', '', $url);
                if (!str_ends_with($url, ".php") && !empty($url))
                        $url = $url.'/';
		echo "<a href=\"".h(get_config("base_url").$url)."\" style=\"text-decoration: none\">\n";
	}
	echo "<div class=\"big-page-item d-flex justify-content-between align-items-center $class\" style=\"$style\">$name
		<div class=\"text-right padding-top\">
			<i class=\"fa fa-$icon\"></i>
		</div></div>\n";
	if ($is_link)
		echo "</a>";
	if ($nestlevel > 0)
		echo "</small>";
	else
		echo "</b>";
	if (!$is_link)
	{
		foreach ($page as $subname=>$subpage)
			show_page_item($subname, $subpage, 1, $small);
	}
}

function show_page_item_mobile($name, $page, $nestlevel)
{
	$active_page = NULL;
	$icon = $style = "";
	$class = "nav-link nav-item";
	if (is_string($active_page) && $page == $active_page)
		$class .= " active";

	if ($nestlevel > 0)
	{
		echo "<small>";
		$name = "&nbsp; ".$name;
		$style = "padding-bottom: 1px; padding-top: 1px";
	} else {
		echo "<b>";
	}
	if (is_array($page))
	{
		$style = "padding-bottom: 0px;";
	} else {
		echo "<a href=\"".h(get_config("base_url").$page)."\" >\n";
	}
	echo "<div class=\"bg-dark lil-page-item d-flex justify-content-between align-items-center $class\" style=\"$style\">$name
		<div class=\"text-right padding-top\">
			<i class=\"fa fa-$icon\"></i>
		</div></div>\n";
	if (!is_array($page))
		echo "</a>";
	if ($nestlevel > 0)
		echo "</small>";
	else
		echo "</b>";
	if (is_array($page))
	{
		foreach ($page as $subname=>$subpage)
			show_page_item($subname, $subpage, 1);
	}
}

function rpc_server_nav()
{
	$active_server = get_active_rpc_server();
	if (!$active_server)
		return; // eg empty servers
	$servers = get_config("unrealircd");
	$cnt = count($servers);
	/* Changing the default server changes it for everyone,
	 * so only users who can manage the panel may do it.
	 */
	$can_switch = current_user_can(PERMISSION_MANAGE_USERS);
?>

		<div class="dropdown navbar-expand-md navbar-nav">
			<a href="#" class="dropdown-toggle nav-link" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><?php echo h($active_server); ?></a>
			<div class="dropdown-menu">
<?php
			foreach($servers as $name=>$d)
			{
				if ($name == $active_server)
					echo "<div class=\"dropdown-item\">".h($name)." <i>(current)</i></div>\n"; // current
				else if ($can_switch)
					echo "<a class=\"dropdown-item rpc-server-switch\" href=\"#\" data-server=\"".h($name)."\">".h($name)."</a>\n";
				else
					echo "<div class=\"dropdown-item text-muted\">".h($name)."</div>\n";
			}
?>
			</div>
		</div>
		<script>
			document.querySelectorAll('.rpc-server-switch').forEach(function (el) {
				el.addEventListener('click', function (e) {
					e.preventDefault();
					change_active_server(el.dataset.server);
				});
			});
		</script>
<?php
}


foreach($pages as $name=>$page)
	show_page_item($name, $page, 0, true);
?>
</div>
</nav>

<div class="container-fluid">
	
	<!-- Fixed navbar -->
	<nav class="topbar navbar navbar-expand-md navbar-dark bg-dark fixed-top z-index padding-top">
		<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#collapsibleNavbar" aria-controls="collapsibleNavbar" aria-expanded="false" aria-label="Toggle navigation">
			<span class="navbar-toggler-icon"></span>
		</button>
		<div>
			<a class="navbar-brand" href="<?php echo h(get_config("base_url")); ?>">
			<img src="<?php echo h(get_config("base_url")); ?>img/unreal.png" height="25" width="25"> UnrealIRCd Admin Panel</a>
		</div>
		<?php rpc_server_nav(); ?>
		<div class="collapse navbar-collapse" id="collapsibleNavbar">
			<ul id="big-nav-items" class="navbar-nav mr-auto">
				
<?php

foreach ($pages as $name => $page)
	show_page_item($name, $page, 0);


?>
	
		</ul>
		<style>
			.search-container {
				position: fixed;
				right: 20px;
			}
			.search-container input,button {
				border-radius: 7px;
			}
			#search-results {
				max-height: 70vh;
				overflow-y: auto;
			}
			#search-results .card.p-3:hover {
				cursor: pointer;
			}
			
		</style>
		<div class="search-container">
			<input type="text" placeholder="Search.." id="search_box" name="search">
		</div>
	</nav><br>
</div>
	<div style="font-size: 13px; position: absolute; top:40px; right: 20px; width: 520px; z-index:1040; height: auto;">
		<ol><div id="search-results" class="card" hidden>
			
		</div></ol>
	</div>
<div id="main_contain" class="container-fluid" style="padding-left: 180px" role="main">

<script>

	const searchBox = document.getElementById('search_box');
	const searchResults =document.getElementById('search-results');
	// Add event listener for keyup event
	searchBox.addEventListener('input', function(event) {
	// Get the value from the input box
		const query = event.target.value.trim();

		// Make sure query is not empty
		if (query !== '') {
			searchResults.removeAttribute('hidden');
			// Make a request to the JSON endpoint with the query
			fetch(BASE_URL+`api/search.php?search=`+encodeURIComponent(searchBox.value))
			.then(response => {
				// Check if the response is successful
				if (!response.ok) {
					throw new Error('Network response was not ok');
				}
				// Parse the JSON data
				return response.json();
			})
			.then(data => {
				searchResults.textContent = '';

				/* All values from the API are raw IRC data:
				 * they are only ever inserted as text (textContent).
				 */
				function add_section(title)
				{
					var search_label = document.createElement('div');
					search_label.classList.add('card', 'bg-light', 'badge', 'p-2', 'm-1');
					search_label.textContent = title;
					searchResults.appendChild(search_label);
				}
				function add_result(name, label, extra, url, name_as_code)
				{
					var result = document.createElement('div');
					result.classList.add('card', 'p-3', 'm-1');
					result.onclick = function() { window.location.href = url; };
					var span = document.createElement('span');
					span.classList.add('p-0');
					var n = document.createElement(name_as_code ? 'code' : 'span');
					n.textContent = (name === undefined || name === null) ? '' : name;
					span.appendChild(n);
					if (label !== undefined && label !== null)
					{
						var badge = document.createElement('div');
						badge.classList.add('badge', 'ml-2', 'badge-primary');
						badge.textContent = 'Matches ' + label;
						span.appendChild(badge);
					}
					result.appendChild(span);
					if (extra)
					{
						var i = document.createElement('i');
						i.textContent = extra;
						result.appendChild(i);
					}
					searchResults.appendChild(result);
				}

				add_section("Users");
				for (let key in data.users)
				{
					let r = data.users[key];
					add_result(r.name, r.label, r.data, BASE_URL+"users/details.php?nick="+encodeURIComponent(r.name), false);
				}

				add_section("Channels");
				for (let key in data.channels)
				{
					let r = data.channels[key];
					add_result(r.name, r.label, r.topic ? r.topic : "", BASE_URL+"channels/details.php?chan="+encodeURIComponent(r.name), false);
				}

				add_section("Servers");
				for (let key in data.servers)
				{
					let r = data.servers[key];
					add_result(r.name, null, "", BASE_URL+"servers/details.php?server="+encodeURIComponent(r.name), false);
				}

				add_section("Server Bans");
				for (let key in data.server_bans)
				{
					let r = data.server_bans[key];
					add_result(r.name, r.label, r.data, BASE_URL+"server-bans/", true);
				}

				add_section("Server Exceptions");
				for (let key in data.excepts)
				{
					let r = data.excepts[key];
					add_result(r.name, r.label, r.data, BASE_URL+"server-bans/ban-exceptions.php", true);
				}

				add_section("Name Bans");
				for (let key in data.name_bans)
				{
					let r = data.name_bans[key];
					add_result(r.name, r.label, r.data, BASE_URL+"server-bans/name-bans.php", true);
				}

				add_section("Spamfilter");
				for (let key in data.spamfilter)
				{
					let r = data.spamfilter[key];
					add_result(r.name, r.label, r.data, BASE_URL+"server-bans/spamfilter.php", true);
				}

				add_section("Logs");
				for (let key in data.logs)
				{
					let r = data.logs[key];
					add_result(r.msg, null, "", BASE_URL+"logs/", false);
				}
			})
			.catch(error => {
				// Handle any errors
				console.error('There was a problem with the fetch operation:', error);
				// Display error message to the user
			});
		}
		else
		{
			searchResults.hidden = 'true';
			searchResults.textContent = '';
		} 
	});
	function nav_resize_check()
	{
		var width = window.innerWidth;
		var sidebar = document.getElementById('sidebarlol');
		var top = document.getElementById('big-nav-items');
		var maincontainer = document.getElementById('main_contain');
		
		if (width < 768)
		{
			sidebar.style.display = 'none';
			top.style.display = '';
			maincontainer.style.paddingLeft = "10px";
		}
		else
		{
			sidebar.style.display = '';
			top.style.display = 'none';
			maincontainer.style.paddingLeft = "180px";
		}
	}
	nav_resize_check();
	window.addEventListener('resize', function() {
		nav_resize_check();
	});
	
</script>

<?php
	if ($current_page)
	{
	    if (!(isset($current_page["no_irc_server_required"]) &&
	         ($current_page["no_irc_server_required"] == true)) &&
	        !get_active_rpc_server())
		{
			Message::Fail("No RPC server configured. Go to Settings - RPC Servers.");
			require_once('footer.php');
			die;
		}
		$current_page_title = "UnrealIRCd Panel";
		if (!empty($current_page_name))
			$current_page_title = "$current_page_name - $current_page_title";
		echo "<script>document.title='".htmlspecialchars($current_page_title)."';</script>\n";
	}
