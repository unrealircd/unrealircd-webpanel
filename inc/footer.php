<?php
	if (!isset($nav_shown))
		return;
?>
<footer class="text-center bg-dark text-white fixed-bottom" style="background-color: #f1f1f1;">
	<!-- Grid container -->
	<div class="container">
		<!-- Section: Social media -->

		<?php
			$arr = []; Hook::run(HOOKTYPE_PRE_FOOTER, $arr);
			echo "<small style=\"font-size: 70%\"><code>Admin Panel ".get_config('webpanel_version')."</code></small>";
		?>

		<section class="mt-1">
			<!-- Twitter -->
			<a
				class="btn btn-link btn-floating btn-md text-white"
				href="https://twitter.com/Unreal_IRCd"
				role="button"
				data-mdb-ripple-color="dark"
				target="_blank"
				><i class="fab fa-twitter"></i
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
				<img src="<?php echo get_config("base_url"); ?>img/unreal.jpg" width="16" height="16" style="margin-left: -7px; margin-right: 24px"></a>

			<i id="bugreport" style="margin-left: -18px; margin-right: 10px;" height="10px" class="fa fa-bug" data-toggle="tooltip" data-placement="top" title="Report a bug (Opens in new tab)"></i>
		
		<?php

		$arr = []; Hook::run(HOOKTYPE_FOOTER, $arr);
		
		?>
		
		
</div>
</div><!-- Second div is because there is a container div in the header-->

</footer>
</body>
</html>
<script>
	var bugreport = document.getElementById('bugreport');
	bugreport.addEventListener('click', (e) => {
		window.open("https://github.com/unrealircd/unrealircd-webpanel/issues/new?labels=bug&template=bug_report.md", '_blank');
	});
</script>