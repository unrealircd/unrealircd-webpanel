<?php
	if (!isset($nav_shown))
		return;
		$arr = []; Hook::run(HOOKTYPE_PRE_FOOTER, $arr);
?>
<footer class="text-center bg-dark text-white fixed-bottom" style="background-color: #f1f1f1;">
	<!-- Grid container -->
	<div class="container">
		<!-- Section: Social media -->
		<?php
			echo "<small style=\"font-size: 70%\"><code>Admin Panel ".get_config('webpanel_version')."</code></small>";
		?>

		<section class="mt-1"><a href="https://patreon.com/UnrealIRCd/"
				role="button"
				data-mdb-ripple-color="dark"
				target="_blank">
				<img src="<?php echo get_config('base_url')?>/img/patreon.png" style="margin-right: 4px;" width="16" height="16"></a>

			<a href="https://www.youtube.com/@unrealircdtutorials/"
				role="button"
				data-mdb-ripple-color="dark"
				target="_blank">
				<img src="https://static-00.iconduck.com/assets.00/youtube-icon-2048x2048-wiwalbpx.png"width="16" height="16"></a>
				
			
			<!-- Twitter -->
			<a
				class="btn btn-link btn-floating btn-md text-white"
				href="https://twitter.com/Unreal_IRCd"
				role="button"
				data-mdb-ripple-color="dark"
				target="_blank"
				><i class="fab fa-twitter" style="margin-left: -7px; margin-right:-4px" ></i
			></a>
			<!-- Github -->
			<a
				class="btn btn-link btn-floating btn-md text-white"
				href="https://github.com/unrealircd/unrealircd-webpanel"
				role="button"
				data-mdb-ripple-color="dark"
				target="_blank"
				><i style="margin-left: -18px" class="fab fa-github"></i
			></a>
			<!-- UnrealIRCd -->
			<a
				href="https://unrealircd.org"
				role="button"
				data-mdb-ripple-color="dark"
				target="_blank">
				<img src="<?php echo get_config("base_url"); ?>img/unreal.jpg" width="16" height="16" style="margin-left: -7px;"></a>

		<?php

		$arr = []; Hook::run(HOOKTYPE_FOOTER, $arr);
		
		?>
		
		
</div>
</div><!-- Second div is because there is a container div in the header-->
</footer>
</body>
</html>
<!--
<script>
	// Function to check if the feedback prompt should be shown
	function showFeedbackPrompt()
	{
		var showPrompt = localStorage.getItem('showFeedbackPrompt');

		// Check if the prompt has never been shown or the user opted to see it again
		if (showPrompt === null || showPrompt === 'true' || showPrompt == 'false')
		{
			const id = bsModal(

				// Modal header (1st param)
				"Help Us Improve Your Experience!",

				// Modal content (2nd param)
				`We value your opinion and would greatly appreciate your feedback. Your input will help us enhance the features, usability,
				and overall performance of the panel, making it even better for managing your UnrealIRCd server/network.<br><br>
				Please take a moment to share your thoughts, suggestions, or any issues you've encountered.
				We welcome feedback on the user interface, functionality, ease of use, or any other aspect you deem important.
				Your feedback is valuable to us in shaping the future of the web panel.<br><br>
				Thank you for your time and support in making UnrealIRCd web panel the best it can be!`,
				
				// Modal footer (3rd param) buttons
				`<div class="btn btn-sm btn-danger" onclick="remindFeedback()">Remind me later!</div>
				<div class="btn btn-sm btn-primary" onclick="submitFeedback()">Feedback</div>`,

				// Optional bootstrap size param (e.g. sm, lg, xl, etc) or null
				null,

				// Optional "static" bool
				true,

				// Optional "show" option
				false,

				// Optional "closebutton" display
				false
			);
		}
		$('#' + id).modal('show');

	}
	
	// Function to handle user feedback submission and store preferences
	function submitFeedback()
	{
		$('#'+id).modal('hide');
		// Handle feedback submission
		// ...

		// Store user preference not to show the prompt again
		localStorage.setItem('showFeedbackPrompt', 'false');
	}
	
	// don't show it yet lol
	// showFeedbackPrompt();
</script> -->
<?php

// don't let people use this yet, still work in progress.
// require_once UPATH . "/misc/right-click.php";
