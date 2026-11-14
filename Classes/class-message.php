<?php

/** Marks a string as trusted HTML for the Message class.
 * Only wrap text that is written by the panel itself, never anything
 * that contains data from IRC, the RPC server, the URL or a form.
 * Example: Message::Fail(new RawHTML("<b>Note:</b> ..."), $some_text);
 */
class RawHTML
{
	public $html;
	function __construct(string $html)
	{
		$this->html = $html;
	}
	function __toString() : string
	{
		return $this->html;
	}
}

/** Show a dismissable alert box.
 * All messages are HTML-escaped, unless they are wrapped in RawHTML.
 * Arguments can be strings, RawHTML objects or arrays of those,
 * each item is shown on its own line.
 */
class Message
{
	private static function flatten($message) : array
	{
		$out = [];
		foreach ($message as $m)
		{
			if (is_array($m))
			{
				foreach (Message::flatten($m) as $x)
					$out[] = $x;
			} else {
				$out[] = $m;
			}
		}
		return $out;
	}

	private static function render(string $class, array $message)
	{
		$lines = [];
		foreach (Message::flatten($message) as $m)
		{
			if ($m instanceof RawHTML)
				$lines[] = $m->html;
			else
				$lines[] = h($m);
		}
		?>
		<div class="alert <?php echo $class; ?> fade show" role="alert">
		<span class="closebtn text-right" onclick="this.parentElement.style.display='none';">&times;</span>
		<?php echo implode("<br>", $lines); ?>
	  </div> <?php
	}

	static function Fail(...$message)
	{
		Message::render("alert-danger", $message);
	}
	static function Success(...$message)
	{
		Message::render("alert-success", $message);
	}
	static function Info(...$message)
	{
		Message::render("alert-info", $message);
	}
	static function Warning(...$message)
	{
		Message::render("alert-short alert-warning", $message);
	}
}
