<body>
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
					<img src="<?php echo get_config('base_url')?>img/patreon.png" style="margin-right: 4px;" width="16" height="16"></a>

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
	</footer>
</body>
</html>
