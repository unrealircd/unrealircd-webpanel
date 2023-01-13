<?php 

class Message
{
	static function Fail($message)
	{
		?>
		<div class="alert alert-short alert-danger fade show" role="alert">
		<?php echo $message; ?>
		<span class="closebtn text-right" onclick="this.parentElement.style.display='none';">&times;</span>
	  </div> <?php
	}
	static function Success($message)
	{
		?>
		<div class="alert alert-success fade show" role="alert">
		<?php echo $message; ?>
		<span class="closebtn text-right" onclick="this.parentElement.style.display='none';">&times;</span>
	  </div> <?php
	}
	static function Info($message)
	{
		?>
		<div class="alert alert-info fade show" role="alert">
		<?php echo $message; ?>
		<span class="closebtn text-right" onclick="this.parentElement.style.display='none';">&times;</span>
	  </div> <?php
	}
}