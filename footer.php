<footer class="text-center bg-dark text-white fixed-bottom"  style="background-color: #f1f1f1;">
  <!-- Grid container -->
  <div class="container">
    <!-- Section: Social media -->
    
    <?php $arr = []; Hook::run(HOOKTYPE_PRE_FOOTER, $arr); ?>

    <section class="mt-1">
    © 1999-<?php echo date('Y'); ?> UnrealIRCd

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
        ><i class="fab fa-github"></i
      ></a>
      <!-- UnrealIRCd -->
      <a
        href="https://unrealircd.org"
        role="button"
        data-mdb-ripple-color="dark">
        <img  class="btn btn-link btn-floating btn-xs text-white" src="<?php echo BASE_URL; ?>img/favicon.ico" width="25" height="25"></a>
    
    <?php $arr = []; Hook::run(HOOKTYPE_FOOTER, $arr); ?>
    </section>
    

</div>
</div>

</footer>
</body>
</html>