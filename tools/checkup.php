<?php
require_once "../inc/common.php";
require_once "../inc/connection.php";
require_once "../inc/header.php";
require_once "../Classes/class-checkup.php";

$checkup = new CheckUp();
?>

<h4>Network Health Checkup</h4>
<style>
	.card {
		min-height: 80%;
		border-radius: 16px;
	}
	#accordion > .card {
		margin-bottom: 5px;
		width:100%;
		border-radius: 16x;
	}

</style>
<?php echo $checkup ?>
<div id="accordion" class="container-xxl">
	<div class="card">
		<div class="card-header" id="headingOne" aria-describedby="test1">
			<h5 class="mb-0">
				<button class="btn" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
					User Mode Conflicts <span class="badge badge-danger"><?php echo $checkup->num_of_problems['usermodes'] ?></span>
				</button>
			</h5>
		</div>
		<div id="collapseOne" class="collapse" aria-labelledby="headingOne" data-parent="#accordion">
			<div class="card-body">
				<?php $checkup->toTable($checkup->problems['usermodes']); ?>
			</div>
		</div>
	</div>
	<div class="card">
		<div class="card-header" id="headingTwo">
			<h5 class="mb-0">
				<button class="btn collapsed" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
					Channel Mode Conflicts <span class="badge badge-danger"><?php echo $checkup->num_of_problems['chanmodes'] ?></span>
				</button>
			</h5>
		</div>
		<div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordion">
			<div class="card-body">
				<?php $checkup->toTable($checkup->problems['chanmodes']); ?>
			</div>
		</div>
	</div>
	<div class="card">
		<div class="card-header" id="headingThree">
			<h5 class="mb-0">
				<button class="btn collapsed" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
					Module Conflicts <span class="badge badge-danger"><?php echo $checkup->num_of_problems['modules'] ?></span>
				</button>
			</h5>
		</div>
		<div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#accordion">
			<div class="card-body">
				<?php $checkup->toTable($checkup->problems['modules']); ?>
			</div>
		</div>
	</div>
	<div class="card">
		<div class="card-header" id="headingFour">
			<h5 class="mb-0">
				<button class="btn collapsed" data-toggle="collapse" data-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
					Server Conflicts <span class="badge badge-danger"><?php echo $checkup->num_of_problems['servers'] ?></span>
				</button>
			</h5>
		</div>
		<div id="collapseFour" class="collapse" aria-labelledby="headingFour" data-parent="#accordion">
			<div class="card-body">
				<?php $checkup->toTable($checkup->problems['servers']); ?>
			</div>
		</div>
	</div>
</div>
<?php

require_once "../inc/footer.php";