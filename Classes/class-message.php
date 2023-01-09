<?php 

class Message
{
	static function Fail($message)
	{
		?>
		<div class="alert">
		<span class="closebtn" onclick="this.parentElement.style.display='none';">&times;</span>
		<?php echo $message; ?>
	  </div> <?php
	}
	static function Success($message)
	{
		?>
		<div class="success">
		<span class="closebtn" onclick="this.parentElement.style.display='none';">&times;</span>
		<?php echo $message; ?>
	  </div> <?php
	}
	static function Info($message)
	{
		?>
		<div class="information">
		<span class="closebtn" onclick="this.parentElement.style.display='none';">&times;</span>
		<?php echo $message; ?>
	  </div> <?php
	}
}