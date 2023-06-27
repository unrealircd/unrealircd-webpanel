<?php
require_once "inc/common.php";
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
	<div id="live_stats" data-toggle="tooltip" data-placement="top" title="The stats on this page are updated in real-time"
	     class="card text-center row font-weight-bold"
	     style="margin-left:5%;height:26px;width:60px;background-color:red;color:white;visibility:hidden">
	     <small style="margin-left:-40px;padding-top:3px;margin-right:-45px">⚪</small>LIVE
	</div>
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
	.card {
		min-height: 100%;
	}
	</style>
<div class="container card-container" style="margin-left:40px;margin-top:30px">

	<div class="row mt-3">
		<div class="col-sm mb-3">
			<div class="card text-center">
				<div class="card-header bg-success text-white">
					<div class="row">
						<div class="col">
							<i aria-hidden="true" class="fa fa-users fa-3x"></i><span class="position-absolute badge rounded-pill badge-warning">
							<?php echo "Record: "; ?>
						</span>
						</div>
						<div class="col">
							<h3 id="stats_user_total" class="display-4"></h3>
						</div>
					</div>
				</div>
				<div class="card-body">
					<div class="row">
						<div class="col">
							<h6>Users Online</h6>
						</div>
						<div class="col"> <a class="btn btn-primary" href="<?php echo get_config("base_url"); ?>users">View</a></div>
					</div>
				</div>
			</div>
			

		</div>
		<div class="col-sm mb-3">
			<div class="card text-center">
				<div class="card-header bg-primary text-white">
					<div class="row">
						<div class="col">
							<i aria-hidden="true" class="fa fa-hashtag fa-3x"></i>
						</div>
						<div class="col">
							<h3 id="stats_channel_total" class="display-4"></h3>
						</div>
					</div>
				</div>
				<div class="card-body">
					<div class="row">
						<div class="col">
							<h6>Channels</h6>
						</div>
						<div class="col"><a class="btn btn-primary" href="<?php echo get_config("base_url"); ?>channels">View</a></div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-sm mb-3">
			<div class="card text-center">
				<div class="card-header bg-warning">
					<div class="row">
						<div class="col">
							<i aria-hidden="true" class="fa fa-shield-halved fa-3x"></i>
						</div>
						<div class="col">
							<h3 id="stats_oper_total" class="display-4"></h3>
						</div>
					</div>
				</div>
				<div class="card-body">
					<div class="row">
						<div class="col">
							<h6>Opers</h6>
						</div>
						<!-- <div class="col"><a class="btn btn-primary" href="<?php echo get_config("base_url")."users/?operonly"; ?>">View</a></div> -->
					</div>
				</div>
			</div>
		</div>

		<div class="col-sm mb-3">
			<div class="card text-center">
				<div class="card-header bg-secondary text-white">
					<div class="row">
						<div class="col">
							<i aria-hidden="true" class="fa fa-network-wired fa-3x"></i>
						</div>
						<div class="col">
							<h3 id="stats_server_total" class="display-4"></h3>
						</div>
					</div>
				</div>
				<div class="card-body">
					<div class="row">
						<div class="col">
							<h6>Servers</h6>
						</div>
						<div class="col"> <a class="btn btn-primary" href="<?php echo get_config("base_url"); ?>servers">View</a></div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="container card-container" style="margin-left:40px;margin-top:30px">

	<div class="row">
		<div class="col-sm mb-3">
			<div class="card text-center">
				<div class="card-header bg-danger text-white">
					<div class="row">
						<div class="col">
							<i aria-hidden="true" class="fa fa-ban fa-3x"></i>
						</div>
						<div class="col">
							<h3 id="num_server_bans" class="display-4"></h3>
						</div>
					</div>
				</div>
				<div class="card-body">
					<div class="row">
						<div class="col">
							<h6>Server Bans</h6>
						</div>
						<div class="col"> <a class="btn btn-primary" href="<?php echo get_config("base_url"); ?>server-bans">View</a></div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-sm mb-3">
			<div class="card text-center">
				<div class="card-header bg-secondary text-white">
					<div class="row">
						<div class="col">
							<i aria-hidden="true" class="fa fa-filter fa-3x"></i>
						</div>
						<div class="col">
							<h3 id="num_spamfilter_entries" class="display-4"></h3>
						</div>
					</div>
				</div>
				<div class="card-body">
					<div class="row">
						<div class="col">
							<h6>Spamfilter</h6>
						</div>
						<div class="col"> <a class="btn btn-primary" href="<?php echo get_config("base_url"); ?>spamfilter.php">View</a></div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-sm mb-3">
			<div class="card text-center">
				<div class="card-header bg-primary text-white">
					<div class="row">
						<div class="col">
							<i aria-hidden="true" class="fa fa-door-open fa-3x"></i>
						</div>
						<div class="col">
							<h3 id="num_ban_exceptions" class="display-4"></h3>
						</div>
					</div>
				</div>

				<div class="card-body">
					<div class="row">
						<div class="col">
							<h6>Server Ban Exceptions</h6>
						</div>
						<div class="col"> <a class="btn btn-primary" href="<?php echo get_config("base_url"); ?>server-bans/ban-exceptions.php">View</a></div>
					</div>
				</div>
			</div>
		</div>
		<?php
		$bg = "bg-success"; // FIXME: this isn't dynamic
		?> 
		<div class="col-sm mb-3">
			<div class="card text-center">
				<div class="card-header <?php echo $bg; ?> text-white">
					<div class="row">
						<div class="col">
							<i aria-hidden="true" class="fa fa-database fa-3x"> </i>
						</div>
						<div class="col">
						<span data-toggle="tooltip" title="" style="border-bottom: 1px dotted #000000">
						<h3 id="stats_uline_total" class="display-4"></h3>
						</div>
					</div>
				</div>
				<div class="card-body">
					<div class="row">
						<div class="col">
							<h6>Services Online</h6>
						</div>
						<!-- <div class="col"> <a class="btn btn-primary" href="<?php echo get_config("base_url")."users/?servicesonly"; ?>">View</a></div> -->
					</div>
				</div>
				
			</div>
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
		document.getElementById("live_stats").style.visibility = '';
		document.getElementById("stats_user_total").innerHTML = data.user.total;
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

<div class="container card-container card-container" style="margin-left:40px;margin-top:10px">

			<div class="row">
			<div class="col-sm-3">
					<div class="card text-center">
						<div class="card-header bg-success text-white">
							<div class="row">
								<div class="col">
									<i aria-hidden="true" class="fa fa-lock-open fa-3x"></i>
								</div>
								<div class="col">
									<h3 class="display-4"><?php echo $num_of_panel_admins; ?></h3>
								</div>
							</div>
						</div>
						<div class="card-body">
							<div class="row">
								<div class="col">
									<h6>Panel Accounts</h6>
								</div>
								<div class="col"> <a class="btn btn-primary" href="<?php echo get_config("base_url"); ?>settings">View</a></div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-sm-3">
					<div class="card text-center">
						<div class="card-header bg-light">
							<div class="row">
								<div class="col">
									<i aria-hidden="true" class="fa fa-plug fa-3x"></i>
								</div>
								<div class="col">
									<h3 class="display-4"><?php echo count(Plugins::$list); ?></h3>
								</div>
							</div>
						</div>
						<div class="card-body">
							<div class="row">
								<div class="col">
									<h6>Plugins</h6>
								</div>
								<div class="col"> <a class="btn btn-primary" href="<?php echo get_config("base_url"); ?>settings/plugins.php">View</a></div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>	
<?php

Hook::run(HOOKTYPE_OVERVIEW_CARD, $stats);

require_once "inc/footer.php";
