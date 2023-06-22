<?php
	define('DEFAULT_PLUGINS_DIR', 'https://api.dalek.services/plugins.list');
	
	class PluginRepo
	{
	    public $plugins;
	    public $data;
	    public $err;
	    function __construct($url = DEFAULT_PLUGINS_DIR)
	    {
	
	    
	        // Initialize curl
	        $curl = curl_init($url);
	
	        // Set the options
	        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);  // Return the response instead of printing it
	        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);  // Set the content type to JSON
	        curl_setopt($curl, CURLOPT_USERAGENT, "UnrealIRCd Admin Panel");
	        // Execute the request
	        $response = curl_exec($curl);
	
	        // Check for errors
	        if ($response === false)
	            $this->err = curl_error($curl);
	        else
	            $this->data = json_decode($response, false);
	    }
        public function ifInstalledLabel($name)
        {
            if (Plugins::plugin_exists($name))
            {
                ?>
                    <div class="badge rounded-pill badge-success">Installed ✅</div>
                <?php
            }
        }
	    public function do_list()
	    {
	        ?>
<div class="row">
	<?php     
		$counter = 0;
		foreach($this->data as $p)
		{
		    if (is_string($p))
		        continue;
            
		    ?>
	<!-- Widget for plugins -->
	<div class="card text-dark bg-light ml-4 mb-3 w-25">
		<div class="card-header">
			<div class="font-weight-bold">
				<div><img class="mr-3" src="<?php echo $p->icon?>" height="50" width="55">
					<?php echo $p->title; $this->ifInstalledLabel($p->name); ?></div>
			</div>
		</div>
		<div class="card-body">
			<h5 class="card-title"><?php echo $p->title ?></h5>
			<p class="card-text"><?php echo $p->description ?> </p>
		</div>
		<div class="card-footer">
			Author: <a href="<?php echo "mailto:".$p->contact ?? "#" ?>">
			<i><?php echo $p->author ?></i></a>
			<div class="text-right">
				<div class="btn btn-secondary">More Info</div>
				<div class="btn btn-primary">Install</div>
			</div>
		</div>
	</div>
	<?php
		$counter++;
		 if ($counter >= 3) // only do three per row. WARNING: untested due to not having more than 2 plugins atm...
		 {
		     ?>
</div>
<div class="row"><?php
	$counter = 0;
	}
	}
	?></div>
<?php
}
}
