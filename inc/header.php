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

<link href="<?php echo get_config("base_url"); ?>css/unrealircd-admin.css" rel="stylesheet">
<link href="<?php echo get_config("base_url"); ?>css/right-click.css" rel="stylesheet">
<style>
.big-page-item:hover, .big-page-item:active, .nav-link {
	color: black;
}
</style>

<link rel="stylesheet" href="<?php echo get_config("base_url"); ?>css/datatables.min.css" />

 <!-- Latest compiled and minified CSS -->
 <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

<!-- Font Awesome JS -->
<script defer src="https://use.fontawesome.com/releases/v6.2.1/js/solid.js" integrity="sha384-tzzSw1/Vo+0N5UhStP3bvwWPq+uvzCMfrN1fEFe+xBmv1C/AtVX5K0uZtmcHitFZ" crossorigin="anonymous"></script>
<script defer src="https://use.fontawesome.com/releases/v6.2.1/js/fontawesome.js" integrity="sha384-6OIrr52G08NpOFSZdxxz1xdNSndlD4vdcf/q2myIUVO0VsqaGHJsB0RaBE01VTOY" crossorigin="anonymous"></script>

<!-- Font Awesome icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css">
<title>UnrealIRCd Panel</title>
<link rel="icon" type="image/x-icon" href="<?php echo get_config("base_url"); ?>img/favicon.ico">
</head>
<body role="document">
<div aria-live="polite" aria-atomic="true">
  <div id="toaster" style="right: 0; bottom: 50px; z-index: 5;" class="position-fixed bottom-0 right-0 p-4">
	<!-- insert your javascript bread in here to make toast -->
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.min.js" integrity="sha384-ZvpUoO/+PpLXR1lu4jmpXWu80pZlYUAfxl5NsBMWOEPSjUn/6Z/hRTt8+pR6L4N2" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js" integrity="sha384-+sLIOodYLS7CIrQpBjl+C7nPvqq+FbNUBDunl/OZv93DB7Ln/533i8e/mZXLi/P+" crossorigin="anonymous"></script>
<script src="<?php echo get_config("base_url"); ?>js/unrealircd-admin.js"></script>
<!-- <script defer src="<?php echo get_config("base_url"); ?>js/right-click-menus.js"></script> -- We're not doing this yet XD -->
<script src="<?php echo get_config("base_url"); ?>js/bs-modal.js"></script>
<script src="<?php echo get_config("base_url"); ?>js/bs-toast.js"></script>
<script src="<?php echo get_config("base_url"); ?>js/datatables.min.js"></script>
<script src="<?php echo get_config("base_url"); ?>js/datatables-natural-sort.js"></script>
<script src="<?php echo get_config("base_url"); ?>js/datatables-ellipsis.js"></script>
<script src="<?php echo get_config("base_url"); ?>js/moment-with-locales.min.js"></script>
<script>
		var BASE_URL = "<?php echo get_config("base_url"); ?>";
		function timeoutCheck() {
			var xhttp = new XMLHttpRequest();
			xhttp.onreadystatechange = function() {
				if (this.readyState == 4 && this.status == 200) {
					var data = JSON.parse(this.responseText);
					if (data.session == 'none')
						window.location = BASE_URL + 'login/?timeout=1&redirect=' + encodeURIComponent(window.location.pathname);
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
				location.reload();
			})
			.catch(error => {
				// handle error? nah.
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
		echo "<a href=\"".get_config("base_url").$url."\" style=\"text-decoration: none\">\n";
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
		echo "<a href=\"".get_config("base_url").$page."\" >\n";
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
?>

		<div class="dropdown navbar-expand-md navbar-nav">
			<a href="#" class="dropdown-toggle nav-link" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><?php echo $active_server ?></a>
			<div class="dropdown-menu">
<?php
			foreach($servers as $name=>$d)
			{
				$link = "";
				if ($name != $active_server)
					echo "<a class=\"dropdown-item\" href=\"javascript:change_active_server('".htmlspecialchars($name)."')\">".htmlspecialchars($name)."</a>\n";
				else
					echo "<div class=\"dropdown-item\">".htmlspecialchars($name)." <i>(current)</i></div>\n"; // current
			}
?>
			</div>
		</div>
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
			<a class="navbar-brand" href="<?php echo get_config("base_url"); ?>">
			<img src="<?php echo get_config("base_url"); ?>img/favicon.ico" height="25" width="25"> UnrealIRCd Admin Panel</a>
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
				searchResults.innerHTML = null;
				// Work with the JSON data
				console.log(data);

				// Update UI with search results

				//users
				var search_label =document.createElement('div');
				search_label.classList.add('card', 'bg-light', 'badge','p-2', 'm-1');
				search_label.innerText = "Users";
				searchResults.appendChild(search_label);
				for (let key in data.users)
				{
					console.log(key+": "+data.users[key].name);
					var user_result =document.createElement('div');
					user_result.classList.add('card','p-3', 'm-1');
					user_result.onclick = function(){
						window.location.href = BASE_URL+"users/details.php?nick="+encodeURIComponent(data.users[key].name);
					};
					user_result.innerHTML = "<span class='p-0'>"+data.users[key].name+"<div class='badge ml-2 badge-primary'>Matches "+data.users[key].label+"</div></span><i>"+data.users[key].data+"</i>";
					searchResults.appendChild(user_result);
				}

				//channels
				var search_label =document.createElement('div');
				search_label.classList.add('card', 'badge', 'bg-light','p-2', 'm-1');
				search_label.innerText = "Channels";
				searchResults.appendChild(search_label);
				for (let key in data.channels)
				{
					console.log(key+": "+data.channels[key].name);
					var channel_result =document.createElement('div');
					channel_result.classList.add('card','p-3', 'm-1');
					channel_result.onclick = function(){
						window.location.href = BASE_URL+"channels/details.php?chan="+encodeURIComponent(data.channels[key].name);
					};
					channel_result.innerHTML = "<span class='p-0'>"+data.channels[key].name+"<div class='badge ml-2 badge-primary'>Matches "+data.channels[key].label+"</div></span><i>"+(data.channels[key].topic?data.channels[key].topic:"")+"</i>";
					searchResults.appendChild(channel_result);
				}
				
				//Servers
				var search_label =document.createElement('div');
				search_label.classList.add('card', 'badge', 'bg-light','p-2', 'm-1');
				search_label.innerText = "Servers";
				searchResults.appendChild(search_label);
				for (let key in data.servers)
				{
					console.log(key+": "+data.servers[key].name);
					var serv_result =document.createElement('div');
					serv_result.classList.add('card','p-3', 'm-1');
					serv_result.onclick = function(){
						window.location.href = BASE_URL+"servers/details.php?server="+encodeURIComponent(data.servers[key].name);
					};
					serv_result.innerHTML = data.servers[key].name;
					searchResults.appendChild(serv_result);
				}

				//Server Bans
				var search_label =document.createElement('div');
				search_label.classList.add('card', 'badge', 'bg-light','p-2', 'm-1');
				search_label.innerText = "Server Bans";
				searchResults.appendChild(search_label);
				for (let key in data.server_bans)
				{
					console.log(key+": "+data.server_bans[key].name);
					var serv_result =document.createElement('div');
					serv_result.classList.add('card','p-3', 'm-1');
					serv_result.onclick = function(){
						window.location.href = BASE_URL+"server-bans";
					};
					serv_result.innerHTML = "<span class='p-0'>"+data.server_bans[key].name+"<div class='badge ml-2 badge-primary'>Matches "+data.server_bans[key].label+"</div></span><i>"+(data.server_bans[key].data?data.server_bans[key].data:"")+"</i>";
					searchResults.appendChild(serv_result);
				}

				//Server Excepts
				var search_label =document.createElement('div');
				search_label.classList.add('card', 'badge', 'bg-light','p-2', 'm-1');
				search_label.innerText = "Server Exceptions";
				searchResults.appendChild(search_label);
				for (let key in data.excepts)
				{
					console.log(key+": "+data.excepts[key].name);
					var serv_result =document.createElement('div');
					serv_result.classList.add('card','p-3', 'm-1');
					serv_result.onclick = function(){
						window.location.href = BASE_URL+"server-bans/ban-exceptions";
					};
					serv_result.innerHTML = "<span class='p-0'>"+data.excepts[key].name+"<div class='badge ml-2 badge-primary'>Matches "+data.excepts[key].label+"</div></span><i>"+(data.excepts[key].data?data.excepts[key].data:"")+"</i>";
					searchResults.appendChild(serv_result);
				}

				//Name Bans
				var search_label =document.createElement('div');
				search_label.classList.add('card', 'badge', 'bg-light','p-2', 'm-1');
				search_label.innerText = "Name Bans";
				searchResults.appendChild(search_label);
				for (let key in data.name_bans)
				{
					console.log(key+": "+data.name_bans[key].name);
					var serv_result =document.createElement('div');
					serv_result.classList.add('card','p-3', 'm-1');
					serv_result.onclick = function(){
						window.location.href = BASE_URL+"server-bans/name-bans";
					};
					serv_result.innerHTML = "<span class='p-0'>"+data.name_bans[key].name+"<div class='badge ml-2 badge-primary'>Matches "+data.name_bans[key].label+"</div></span><i>"+(data.name_bans[key].data?data.name_bans[key].data:"")+"</i>";
					searchResults.appendChild(serv_result);
				}
				//Spamfilter
				var search_label =document.createElement('div');
				search_label.classList.add('card', 'badge', 'bg-light','p-2', 'm-1');
				search_label.innerText = "Spamfilter";
				searchResults.appendChild(search_label);
				for (let key in data.spamfilter)
				{
					console.log(key+": "+data.spamfilter[key].name);
					var serv_result =document.createElement('div');
					serv_result.classList.add('card','p-3', 'm-1');
					serv_result.onclick = function(){
						window.location.href = BASE_URL+"server-bans/spamfilter.php";
					};
					serv_result.innerHTML = "<span class='p-0'>"+data.spamfilter[key].name+"<div class='badge ml-2 badge-primary'>Matches "+data.spamfilter[key].label+"</div></span><i>"+(data.spamfilter[key].data?data.spamfilter[key].data:"")+"</i>";
					searchResults.appendChild(serv_result);
				}

				//Logs
				var search_label =document.createElement('div');
				search_label.classList.add('card', 'badge', 'bg-light','p-2', 'm-1');
				search_label.innerText = "Logs";
				searchResults.appendChild(search_label);
				for (let key in data.logs)
				{
					console.log(key+": "+data.logs[key].msg);
					var log_result =document.createElement('div');
					log_result.classList.add('card','p-3', 'm-1');
					log_result.onclick = function(){
						window.location.href = BASE_URL+"logs";
					};
					log_result.innerHTML = data.logs[key].msg;
					searchResults.appendChild(log_result);
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
			searchResults.innerHTML = null;
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
