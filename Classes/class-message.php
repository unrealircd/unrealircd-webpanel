<?php 

class Message
{
	static function Fail(...$message)
	{
		// cater for arrays
		if (is_array($message[0]) && !isset($message[1]))
			$message = $message[0];
		?>
		<div class="alert alert-danger fade show" role="alert">
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
		// cater for arrays
		if (is_array($message[0]) && !isset($message[1]))
			$message = $message[0];
		?>
		<div class="alert alert-success fade show" role="alert">
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
		// cater for arrays
		if (is_array($message[0]) && !isset($message[1]))
			$message = $message[0];
		?>
		<div class="alert alert-info fade show" role="alert">
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
	static function Warning(...$message)
	{
		?>
		<div class="alert alert-short alert-warning fade show" role="alert">
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