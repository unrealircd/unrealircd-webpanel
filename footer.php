<footer class="text-center bg-dark text-white fixed-bottom d-none d-md-block"	style="background-color: #f1f1f1;">
	<!-- Grid container -->
	<div class="container">
		<!-- Section: Social media -->
		
		<?php $arr = []; Hook::run(HOOKTYPE_PRE_FOOTER, $arr); ?>

		<section class="mt-1">
		<a href="https://unrealircd.org/" class="btn btn-default" style="color:white">© 1999-<?php echo date('Y'); ?> UnrealIRCd</a>

			<!-- Twitter -->
			<a
				class="btn btn-link btn-floating btn-lg text-white"
				href="https://twitter.com/Unreal_IRCd"
				role="button"
				data-mdb-ripple-color="dark"
				><i class="fab fa-twitter"></i
			></a>
			<!-- Github -->
			<a
				class="btn btn-link btn-floating btn-lg text-white"
				href="https://github.com/unrealircd/unrealircd-webpanel"
				role="button"
				data-mdb-ripple-color="dark"
				><i style="margin-left: -18px" class="fab fa-github"></i
			></a>
			<!-- UnrealIRCd -->
			<a
				href="https://unrealircd.org"
				role="button"
				data-mdb-ripple-color="dark">
				<img src="<?php echo get_config("base_url"); ?>img/unreal.jpg" width="23" height="23" style="margin-right: 25px"></a>

			<i id="bugreport" style="margin-left: -18px; margin-right: 10px;" height="1000px" class="fa fa-bug" data-toggle="tooltip" data-placement="top" title="Report a bug (Opens in new tab)"></i>
		
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