<?php 

class Message
{
	static function Fail($message)
	{
		?>
		<div class="alert alert-danger" role="alert">
		<span class="closebtn" onclick="this.parentElement.style.display='none';">&times;</span>
		<?php echo $message; ?>
	  </div> <?php
	}
	static function Success($message)
	{
		?>
		<div class="alert alert-success" role="alert">
		<span class="closebtn" onclick="this.parentElement.style.display='none';">&times;</span>
		<?php echo $message; ?>
	  </div> <?php
	}
	static function Info($message)
	{
		?>
		<div class="alert alert-info" role="alert">
		<span class="closebtn" onclick="this.parentElement.style.display='none';">&times;</span>
		<?php echo $message; ?>
	  </div> <?php
	}
}