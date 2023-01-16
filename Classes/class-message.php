<?php 

class Message
{
	static function Fail(...$message)
	{
		?>
		<div class="alert alert-short alert-danger fade show" role="alert">
		<span class="closebtn text-right" onclick="this.parentElement.style.display='none';">&times;</span>
		<?php
			for ($i = 0; isset($message[$i]); $i++)
			{
				echo $message[$i];
				if (isset($message[$i + 1]))
					echo "<br>";
			}
		?>
	  </div> <?php
	}
	static function Success(...$message)
	{
		?>
		<div class="alert alert-short alert-success fade show" role="alert">
		<span class="closebtn text-right" onclick="this.parentElement.style.display='none';">&times;</span>
		<?php
			for ($i = 0; isset($message[$i]); $i++)
			{
				echo $message[$i];
				if (isset($message[$i + 1]))
					echo "<br>";
			}
		?>
	  </div> <?php
	}
	static function Info(...$message)
	{
		?>
		<div class="alert alert-short alert-info fade show" role="alert">
		<span class="closebtn text-right" onclick="this.parentElement.style.display='none';">&times;</span>
		<?php
			for ($i = 0; isset($message[$i]); $i++)
			{
				echo $message[$i];
				if (isset($message[$i + 1]))
					echo "<br>";
			}
		?>
	  </div> <?php
	}
}