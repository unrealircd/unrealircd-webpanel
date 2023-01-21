<?php
require_once "common.php";
require_once "header.php";

$stats = $rpc->query("stats.get", []);
?>

<h2>Network Overview</h2>

<?php
$array_of_stats = (array)$stats;

/* What if someone wants to add their own stats... */
Hook::run(HOOKTYPE_PRE_OVERVIEW_CARD, $array_of_stats);

/* This makes sure that a plugin which called the parameter
 * by reference can add/update the stats for display here.
*/
$stats = (object) $array_of_stats;


?>

<div class="container mt-5">

	<div class="row">
		<div class="col-sm">
			<div class="card text-center">
				<div class="card-header bg-success text-white">
					<div class="row">
						<div class="col">
							<i class="fa fa-users fa-3x"></i><span class="position-absolute badge rounded-pill badge-warning">
							<?php echo "Record: ".$stats->user->record; ?>
						</span>
						</div>
						<div class="col">
							<h3 class="display-4"><?php echo $stats->user->total; ?></h3>
						</div>
					</div>
				</div>
				<div class="card-body">
					<div class="row">
						<div class="col">
							<h6>Users Online</h6>
						</div>
						<div class="col"> <a class="btn btn-primary" href="<?php echo BASE_URL; ?>users">View</a></div>
					</div>
				</div>
			</div>
			

		</div>
		<div class="col-sm">
			<div class="card text-center">
				<div class="card-header bg-primary text-white">
					<div class="row">
						<div class="col">
							<i class="fa fa-hashtag fa-3x"></i>
						</div>
						<div class="col">
							<h3 class="display-4"><?php echo $stats->channel->total; ?></h3>
						</div>
					</div>
				</div>
				<div class="card-body">
					<div class="row">
						<div class="col">
							<h6>Channels</h6>
						</div>
						<div class="col"><a class="btn btn-primary" href="<?php echo BASE_URL; ?>channels">View</a></div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-sm">
			<div class="card text-center">
				<div class="card-header bg-warning">
					<div class="row">
						<div class="col">
							<i class="fa fa-shield-halved fa-3x"></i>
						</div>
						<div class="col">
							<h3 class="display-4"><?php echo $stats->user->oper; ?></h3>
						</div>
					</div>
				</div>
				<div class="card-body">
					<div class="row">
						<div class="col">
							<h6>Opers</h6>
						</div>
						<div class="col"><a class="btn btn-primary" href="<?php echo BASE_URL."users/?operonly"; ?>">View</a></div>
					</div>
				</div>
			</div>
		</div>

		<div class="col-sm">
			<div class="card text-center">
				<div class="card-header bg-secondary text-white">
					<div class="row">
						<div class="col">
							<i class="fa fa-network-wired fa-3x"></i>
						</div>
						<div class="col">
							<h3 class="display-4"><?php echo $stats->server->total; ?></h3>
						</div>
					</div>
				</div>
				<div class="card-body">
					<div class="row">
						<div class="col">
							<h6>Servers</h6>
						</div>
						<div class="col"> <a class="btn btn-primary" href="<?php echo BASE_URL; ?>servers">View</a></div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="container mt-3">

	<div class="row">
		<div class="col-sm">
			<div class="card text-center">
				<div class="card-header bg-danger text-white">
					<div class="row">
						<div class="col">
							<i class="fa fa-ban fa-3x"></i>
						</div>
						<div class="col">
							<h3 class="display-4"><?php echo $stats->server_ban->server_ban; ?></h3>
						</div>
					</div>
				</div>
				<div class="card-body">
					<div class="row">
						<div class="col">
							<h6>Server Bans</h6>
						</div>
						<div class="col"> <a class="btn btn-primary" href="<?php echo BASE_URL; ?>server_bans.php">View</a></div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-sm">
			<div class="card text-center">
				<div class="card-header bg-secondary text-white">
					<div class="row">
						<div class="col">
							<i class="fa fa-filter fa-3x"></i>
						</div>
						<div class="col">
							<h3 class="display-4"><?php echo $stats->server_ban->spamfilter; ?></h3>
						</div>
					</div>
				</div>
				<div class="card-body">
					<div class="row">
						<div class="col">
							<h6>Spamfilter</h6>
						</div>
						<div class="col"> <a class="btn btn-primary" href="<?php echo BASE_URL; ?>spamfilter.php">View</a></div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-sm">
			<div class="card text-center">
				<div class="card-header bg-primary text-white">
					<div class="row">
						<div class="col">
							<i class="fa fa-door-open fa-3x"></i>
						</div>
						<div class="col">
							<h3 class="display-4"><?php echo $stats->server_ban->server_ban_exception; ?></h3>
						</div>
					</div>
				</div>

				<div class="card-body">
					<div class="row">
						<div class="col">
							<h6>Server Ban Exceptions</h6>
						</div>
						<div class="col"> <a class="btn btn-secondary disabled" href="#">View</a></div>
					</div>
				</div>
			</div>
		</div>
		<?php
		if ($stats->server->ulined) {
			$bg = "bg-success";

			/* honestly can't think of a case where there would actually be only one uline... but... well here we are, worrying over the small stuff =] */
			$user_noun = ($stats->user->ulined == 1) ? "user" : "users"; // use "users" even if 0, sounds better.
			$is_are = ($stats->user->ulined == 1) ? "is" : "are";
			$server_noun = ($stats->server->ulined == 1) ? "server" : "servers";
			$tooltip = "There $is_are " . $stats->user->ulined . " U-Lined $user_noun over " . $stats->server->ulined . " U-Lined $server_noun";
		}
		else
			$bg = "bg-warning";
		?> 
		<div class="col-sm">
			<div class="card text-center">
				<div class="card-header <?php echo $bg; ?> text-white">
					<div class="row">
						<div class="col">
							<i class="fa fa-database fa-3x"> </i>
						</div>
						<div class="col">
						<span data-toggle="tooltip" title="<?php echo $tooltip; ?>" style="border-bottom: 1px dotted #000000"><h3 class="display-4"><?php echo $stats->user->ulined; ?>/<?php echo $stats->server->ulined; ?></h3>
						</div>
					</div>
				</div>
				<div class="card-body">
					<div class="row">
						<div class="col">
							<h6>Services Online</h6>
						</div>
						<div class="col"> <a class="btn btn-primary" href="<?php echo BASE_URL."users/?servicesonly"; ?>">View</a></div>
					</div>
				</div>
				
			</div>
		</div>
	</div>
</div>

<?php

Hook::run(HOOKTYPE_OVERVIEW_CARD, $stats);

require_once "footer.php";
