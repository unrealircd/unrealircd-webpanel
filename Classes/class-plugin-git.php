<?php
	define('DEFAULT_PLUGINS_DIR', 'https://api.dalek.services/plugins.list');
	
	class PluginRepo
	{
	    public $plugins ;
	    public $data;
	    public $err;
	    function __construct($url = DEFAULT_PLUGINS_DIR)
	    {
            global $config;
            if (!isset($config['third-party-plugins']))
            {
                $config['third-party-plugins']['data'] = NULL;
                $config['third-party-plugins']['timestamp'] = 0;
            }
            if (time() - $config['third-party-plugins']['timestamp'] > 200) // Cache for 3.333 minutes lol
            {
                // come simba it is taem
                $curl = curl_init($url);
	
                // Set the options
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);  // Return the response instead of printing it
                curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);  // Set the content type to JSON
                curl_setopt($curl, CURLOPT_USERAGENT, "UnrealIRCd Admin Panel"); // This is Secret Agent UnrealIRCd Admin Panel reporting for doody
                // Execute the request
                $response = curl_exec($curl);
        
                // Check for errors
                if ($response === false)
                    $this->err = curl_error($curl);
                else
                {
                    $this->data = json_decode($response, false);
                    $config['third-party-plugins']['data'] = $this->data;
                    $config['third-party-plugins']['timestamp'] = time();
                    write_config('third-party-plugins');
                }
            }
            else
                $this->data = $config['third-party-plugins']['data'];
	        
	    }



        public function ifInstalledLabel($name, $installed = false)
        {
            if ($installed)
            {   ?>
                <div style="margin-left:40px;" class="badge rounded-pill badge-success">✔ Installed</div>
    <?php   }
            else if (Plugins::plugin_exists($name))
            {
                ?>
                    <div style="margin-left:40px;" class="badge rounded-pill badge-success">✔ Installed</div>
                <?php
            }
        }



    public function do_list()
    {
        global $config;
        if ($this->err)
            die("Could not fetch list.\n");
        global $config;
            ?>
               <div class="row">
            <?php     
        $counter = 0;


        foreach($this->data->list as $p)
        {
            $installed = in_array($p->name, $config['plugins']) ? true : false;

            if (is_string($p))
                continue;
            
            // use a default image if there was none
            $p->icon = $p->icon ?? get_config("base_url")."img/no-image-available.jpg";
                ?>
            <!-- Widget for plugins -->
            <div id="<?php echo $p->name ?>" class="<?php if ($installed) echo "installed" ?> plugin-card card text-dark bg-light ml-4 mb-3 w-25" style="min-width:300px">

                <!-- Card header -->
                <div class="card-header">
                    <div class="media">
                        <img class="align-self-start mr-3" src="<?php echo $p->icon ?>" height="50" width="55">
                        <div class="media-body">
                            <div style="position:relative;float:inline-end"><?php echo $this->ifInstalledLabel($p->name); ?></div>
                            <h4 class="mb-0 mt-0"><?php echo $p->title ?></h4>
                            <small>By <a href="<?php echo "mailto:$p->email" ?>" target="_blank"><?php echo $p->author ?></a></small>
                        </div>
                    </div>
                </div>

                <!-- Card body -->
                <div class="card-body">
                    <h6 class="card-title"><?php echo $p->title ?> <small><code>v<?php echo $p->version ?></code></small></h6>
                    <p class="card-text"><?php echo $p->description ?> </p>
                </div>

                <!-- Card footer -->
                <div class="card-footer d-flex justify-content-between">
                    <div id="justALonelyEmptyDivCryEmoji"></div>
                    <div>
                        <div id="<?php echo $p->name ?>" class="more-info btn btn-info">More Info</div>
                        <div id="<?php echo $p->name ?>install" class="btn-install-plugin btn btn-primary">Install</div>
                    </div>
                </div>
            </div>
            <?php
                /** only do three per row.
                 *  WARNING: untested due to not having more than 2 plugins atm...
                */
                if ($counter >= 3)
                {
                    ?><!-- New row please mr html -->
                        </div>
                        <div class="row"> 
                    <?php
                    $counter = 0;
                }
                $counter++;


            }
            ?>
        </div>
        <i>Want to see your plugin listed here? <a href="https://github.com/unrealircd/unrealircd-webpanel-plugins" target="__blank">Make a pull request to our GitHub Repository</a>!</i>
    <?php
    }
}

?>