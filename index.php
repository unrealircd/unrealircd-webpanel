<?php
require_once "common.php";
require_once "header.php";

rpc_pop_lists();
?>

<h2>Network Overview</h2>

<div class="container mt-5">

	<div class="row">
		<div class="col-sm">
			<div class="card text-center">
				<div class="card-header bg-success text-white">
					<div class="row">
						<div class="col">
							<i class="fa fa-users fa-3x"></i>
						</div>
						<div class="col">
							<h3 class="display-4"><?php echo count(RPC_List::$user); ?></h3>
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
							<h3 class="display-4"><?php echo count(RPC_List::$channel); ?></h3>
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
							<h3 class="display-4"><?php echo RPC_List::$opercount; ?></h3>
						</div>
					</div>
				</div>
				<div class="card-body">
					<div class="row">
						<div class="col">
							<h6>Opers</h6>
						</div>
						<!-- TODO: Filter opers in user list and make this do that -->
						<div class="col"><a class="btn btn-secondary disabled" href="#">View</a></div>
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
							<h3 class="display-4"><?php echo count(RPC_List::$server); ?></h3>
						</div>
					</div>
				</div>
				<div class="card-body">
					<div class="row">
						<div class="col">
							<h6>Servers</h6>
						</div>
						<div class="col"> <a class="btn btn-primary" href="<?php echo BASE_URL; ?>network">View</a></div>
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
							<h3 class="display-4"><?php echo count(RPC_List::$tkl); ?></h3>
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
							<h3 class="display-4"><?php echo count(RPC_List::$spamfilter); ?></h3>
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
							<h3 class="display-4"><?php echo count(RPC_List::$exception); ?></h3>
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
		if (RPC_List::$services_count) {
			$bg = "bg-success";
		} ?> 
		<div class="col-sm">
			<div class="card text-center">
				<div class="card-header <?php echo $bg; ?> text-white">
					<div class="row">
						<div class="col">
							<i class="fa fa-database fa-3x"> </i>
						</div>
						<div class="col">
							<h3 class="display-4"><?php echo RPC_List::$services_count; ?></h3>
						</div>
					</div>
				</div>
				<div class="card-body">
					<div class="row">
						<div class="col">
							<h6>Services Online</h6>
						</div>
						<div class="col"> <a class="btn btn-secondary disabled" href="#">View</a></div>
					</div>
				</div>
				
			</div>
		</div>
	</div>
</div>

<?php

require_once "footer.php";
