<?php
require_once "inc/common.php";
require_once "Classes/class-checkup.php";
if (!isset($config['unrealircd']))
{
	$redirect = get_config("base_url")."settings/rpc-servers.php";
	header('Location: ' . $redirect);
	die;
}

require_once "inc/header.php";

?>
<div class="row ml-0">
	<h2>Network Overview</h2>
	<span class="badge bg-danger text-light ml-4 pl-2 pr-2 rounded-pill" style="height:fit-content">LIVE</span>
		<?php checkup_widget(); ?>
	</div>
<?php
$array_of_stats = [];
/* What if someone wants to add their own stats... */
Hook::run(HOOKTYPE_PRE_OVERVIEW_CARD, $array_of_stats);

/* This makes sure that a plugin which called the parameter
 * by reference can add/update the stats for display here.
*/
$stats = (object) $array_of_stats;
$userlist = [];
Hook::run(HOOKTYPE_GET_USER_LIST, $userlist);
$num_of_panel_admins = count($userlist);

?>
<style>
	#health_banner {
		margin-left:20px;
		width:fit-content;
	}
	.card {
		min-height: 80%;
		border-radius: 16px;
	}
	body {
		background-image: url('https://cdn.wallpapersafari.com/34/98/yznZmQ.jpg');
		background-size: cover;
	}
	.card-body i {
		position: fixed;
		top: 10px;
		right: 10px;
	}
	.card:hover {
		text-decoration: none;
	}

	@keyframes rotateEffect {
		0% { transform: rotateX(0deg); }
		50% { transform: rotateX(180deg); }
		100% { transform: rotateX(0deg); }
	}

	.numberDisplay {
		animation: rotateEffect 0.5s ease;
	}

	.frosted-glass-success {
		/* From https://css.glass */
		background: rgba(63, 162, 36, 0.73);
		box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
		backdrop-filter: blur(9.8px);
		-webkit-backdrop-filter: blur(9.8px);
	}
	.frosted-glass-info {
		/* From https://css.glass */
		background: rgba(57, 127, 207, 0.73);
		box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
		backdrop-filter: blur(9.8px);
		-webkit-backdrop-filter: blur(9.8px);
	}
	.frosted-glass-danger {
		/* From https://css.glass */
		background: rgba(207, 57, 57, 0.73);
		box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
		backdrop-filter: blur(9.8px);
		-webkit-backdrop-filter: blur(9.8px);
	}
	.frosted-glass-warning {
		/* From https://css.glass */
		background: rgba(207, 194, 57, 0.73);
		box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
		backdrop-filter: blur(9.8px);
		-webkit-backdrop-filter: blur(9.8px);
	}
	.frosted-glass-secondary {
		/* From https://css.glass */
		background: rgba(75, 75, 75, 0.73);
		box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
		backdrop-filter: blur(9.8px);
		-webkit-backdrop-filter: blur(9.8px);
	}

	</style>
<div class="container card-container ml-1">

<div class="row mt-3">
	<div class="col-sm mb-3">
			<a class="card frosted-glass-success text-center" href="<?php echo get_config("base_url"); ?>users/">
				<div class="card-body text-white">
					<div class="row text-center">
						<span id="userRecord" class="position-absolute badge rounded-pill badge-warning" hidden>
							<?php echo "Record: "; ?>
						</span>
						<div class="col">
							<div class="col">
								<i aria-hidden="true" class="fa fa-users fa-2x"></i>
							</div>
							<div class="col">
								<h5 id="stats_user_total" class="display-4 numberDisplay"></h5>
								<h5 class="display-5">Users Online</h5>
							</div>
						</div>
					</div>
				</div>
			</a>
		</div>
		<div class="col-sm mb-3">
			<a class="card frosted-glass-info text-center" href="<?php echo get_config("base_url"); ?>channels/">
				<div class="card-body text-white">
					<div class="row text-center">
						<div class="col">
							<div class="col">
								<i aria-hidden="true" class="fa fa-hashtag fa-2x"></i>
							</div>
							<div class="col">
								<h5 id="stats_channel_total" class="display-4 numberDisplay"></h5>
								<h5 class="display-5">Channels</h5>
							</div>
						</div>
					</div>
				</div>
			</a>
		</div>
		<div class="col-sm mb-3">
			<a class="card frosted-glass-warning text-center" href="<?php echo get_config("base_url"); ?>users/">
				<div class="card-body text-dark">
					<div class="row text-center">
						<div class="col">
							<div class="col">
								<i aria-hidden="true" class="fa fa-shield-halved fa-2x"></i>
							</div>
							<div class="col">
								<h5 id="stats_oper_total" class="display-4 numberDisplay"></h5>
								<h5 class="display-5" style="margin-top: -3px">Operators</h5>
								<h5 style="font-size: 10px; margin-top:-12px">View in Users ></h5>
							</div>
						</div>
					</div>
				</div>
			</a>
		</div>
		<div class="col-sm mb-3">
			<a class="card frosted-glass-secondary text-center" href="<?php echo get_config("base_url"); ?>servers/">
				<div class="card-body text-white">
					<div class="row text-center">
						<div class="col">
							<div class="col">
								<i aria-hidden="true" class="fa fa-network-wired fa-2x"></i>
							</div>
							<div class="col">
								<h5 id="stats_server_total" class="display-4 numberDisplay"></h5>
								<h5 class="display-5">Servers</h5>
							</div>
						</div>
					</div>
				</div>
			</a>
		</div>
	</div>
</div>
<div class="container card-container ml-1">

	<div class="row">
		<div class="col-sm mb-3">
			<a class="card frosted-glass-danger text-center" href="<?php echo get_config("base_url"); ?>server-bans/">
				<div class="card-body text-white">
					<div class="row text-center">
						<div class="col">
							<div class="col">
								<i aria-hidden="true" class="fa fa-ban fa-2x"></i>
							</div>
							<div class="col">
								<h5 id="num_server_bans" class="display-4 numberDisplay"></h5>
								<h5 class="display-5">Server Bans</h5>
							</div>
						</div>
					</div>
				</div>
			</a>
		</div>
		<div class="col-sm mb-3">
			<a class="card frosted-glass-secondary text-center" href="<?php echo get_config("base_url"); ?>spamfilter.php/">
				<div class="card-body text-white">
					<div class="row text-center">
						<div class="col">
							<div class="col">
								<i aria-hidden="true" class="fa fa-filter fa-2x"></i>
							</div>
							<div class="col">
								<h5 id="num_spamfilter_entries" class="display-4 numberDisplay"></h5>
								<h5 class="display-5">Spamfilter</h5>
							</div>
						</div>
					</div>
				</div>
			</a>
		</div>
		<div class="col-sm mb-3">
			<a class="card frosted-glass-info text-center" href="<?php echo get_config("base_url"); ?>server-bans/ban-exceptions.php">
				<div class="card-body text-white">
					<div class="row text-center">
						<div class="col">
							<div class="col">
								<i aria-hidden="true" class="fa fa-door-open fa-2x"></i>
							</div>
							<div class="col">
								<h5 id="num_ban_exceptions" class="display-4 numberDisplay"></h5>
								<h5 class="display-5">Server Ban Exceptions</h5>
							</div>
						</div>
					</div>
				</div>
			</a>
		</div>
		<div class="col-sm mb-3">
			<a class="card frosted-glass-success text-center" href="<?php echo get_config("base_url"); ?>servers">
				<div class="card-body text-white">
					<div class="row text-center">
						<div class="col">
							<div class="col">
								<i aria-hidden="true" class="fa fa-database fa-2x"></i>
							</div>
							<div class="col">
								<h5 id="stats_uline_total" class="display-4 numberDisplay"></h5>
								<h5 class="display-5" style="margin-top: -3px">Services Online</h5>
								<h5 style="font-size: 10px; margin-top:-12px">View in Servers ></h5>
							</div>
						</div>
					</div>
				</div>
			</a>
		</div>
	</div>
</div>


<script>
	/* Last time stats were updated */
	let stats_tick = 0;

	function updateStats(e)
	{
		var data;
		try {
			data = JSON.parse(e.data);
		} catch(e) {
			return;
		}
		stats_tick = Date.now()
		console.log(data);
		document.getElementById("userRecord").innerHTML = "Record: "+data.user.record;
		document.getElementById("stats_user_total").innerHTML = data.user.total;
		document.getElementById("stats_user_total").classList.remove('numberDisplay');
		document.getElementById("stats_user_total").classList.add('numberDisplay');
		document.getElementById("stats_channel_total").innerHTML = data.channel.total;
		document.getElementById("stats_oper_total").innerHTML = data.user.oper;
		document.getElementById("stats_server_total").innerHTML = data.server.total;
		document.getElementById("num_server_bans").innerHTML = data.server_ban.server_ban;
		document.getElementById("num_spamfilter_entries").innerHTML = data.server_ban.spamfilter;
		document.getElementById("num_ban_exceptions").innerHTML = data.server_ban.server_ban_exception;
		document.getElementById("stats_uline_total").innerHTML = data.user.ulined + "/" + data.server.ulined;
	}
	function checkStatsOutdated()
	{
		setTimeout(checkStatsOutdated, 2000);
		if (Date.now() - stats_tick > 10000)
			document.getElementById("live_stats").style.visibility = 'hidden';
	}
	setTimeout(checkStatsOutdated, 2000);

	function initStats()
	{
		if (!!window.EventSource) {
			var source = new EventSource('api/overview.php');
			source.addEventListener('message', updateStats, false);
		}
	}
	initStats();
	//setInterval(updateStats, 1000); // Update stats every second
	// ^ commented out but may want to restart initStats() when connection is lost.

	
	window.addEventListener('resize', function() {
		var containers = document.querySelectorAll('.card-container');
		var width = window.innerWidth;
		if (width < 768)
		{
			containers.forEach((container) => {
				container.removeAttribute('style');

			});
		} else 
		{
			containers.forEach((container) => {
				container.style.marginLeft = "40px";
				container.style.marginTop = "30px";

			});
		}
	});
</script>

<div class="container card-container card-container ml-1">

			<div class="row">
				<div class="col-sm mb-3">
					<a class="card frosted-glass-success text-center" href="<?php echo get_config("base_url"); ?>settings">
						<div class="card-body text-white">
							<div class="row text-center">
								<div class="col">
									<div class="col">
										<i aria-hidden="true" class="fa fa-lock-open fa-2x"></i>
									</div>
									<div class="col">
										<h5 class="display-4 numberDisplay"><?php echo $num_of_panel_admins; ?></h5>
										<h5 class="display-5">Panel Accounts</h5>
									</div>
								</div>
							</div>
						</div>
					</a>
				</div>
				<div class="col-sm mb-3">
					<a class="card frosted-glass-info text-center" href="<?php echo get_config("base_url"); ?>settings/plugins.php">
						<div class="card-body text-light">
							<div class="row text-center">
								<div class="col">
									<div class="col">
										<i aria-hidden="true" class="fa fa-plug fa-2x"></i>
									</div>
									<div class="col">
										<h5 class="display-4 numberDisplay"><?php echo count(Plugins::$list); ?></h5>
										<h5 class="display-5">Plugins</h5>
									</div>
								</div>
							</div>
						</div>
					</a>
				</div>
			</div>
		</div>	
<?php

Hook::run(HOOKTYPE_OVERVIEW_CARD, $stats);

require_once "inc/footer.php";
