<?php


/**
 * Does a complete checkup of the network.
 */
class CheckUp
{
    public $num_of_problems = [
        "chanmodes" => 0,
        "usermodes" => 0,
        "modules" => 0,
        "other" => 0,
    ];
    public $problems = [
        "chanmodes" => [],
        "usermodes" => [],
        "modules" => [],
        "other" => [],
    ];

    public $serverlist = [];

    /**
     * Construct0r
     */
    function __construct()
    {
        global $rpc;

        $this->serverlist = $rpc->server()->getAll();
        $this->chanmode_check();
        $this->usermode_check();
        //$this->module_check();
        
    }

    /**
     * Checks channel modes of servers against other servers
     * @return void
     */
    function chanmode_check() : void
    {
        foreach($this->serverlist as $s) // cycle through each server
        {
            /* make a single string from the array of groups */
            $ourchmodes = "";
            foreach ($s->server->features->chanmodes as $set)
                for ($i=0; isset($set[$i]); $i++)
                    strcat($ourchmodes,$set[$i]);

            /* take a look at every other server... yep, we do this for every server */
            foreach ($this->serverlist as $serv)
            {
                /* except for ourselves lol */
                if ($serv->id == $s->id)
                    continue;
                
                /* hmm if it's not unreal, skip it too */
                if (!strstr($serv->server->features->software,"UnrealIRCd"))
                    continue;
                
                /* make a single string from the array of groups but for them this time */
                $theirchmodes = "";
                foreach ($serv->server->features->chanmodes as $set)
                    for ($i=0; isset($set[$i]); $i++)
                        strcat($theirchmodes,$set[$i]);

                /* check ours against theirs */
                for ($i=0; isset($ourchmodes[$i]) && $m = $ourchmodes[$i]; $i++)
                {
                    /* if we have a mode that they don't have */
                    if (!strstr($theirchmodes, $m))
                    {
                        ++$this->num_of_problems['chanmodes'];
                        $this->problems['chanmodes'][] = "Channel mode $m is present on $s->name but missing on $serv->name";
                    }
                }
            }
        }
    }

    /**
     * Checks user modes of servers against other servers
     * @return void
     */
    function usermode_check() : void
    {
         /* make a single string from the array of groups */
         $ourumodes = $s->server->features->usermodes;

        /* take a look at every other server... yep, we do this for every server */
        foreach ($this->serverlist as $serv)
        {
            /* except for ourselves lol */
            if ($serv->id == $s->id)
                continue;
            
            /* hmm if it's not unreal, skip it too */
            if (!strstr($serv->server->features->software,"UnrealIRCd"))
                continue;
            
            $theirumodes = $serv->server->features->usermodes;

            /* check ours against theirs */
            for ($i=0; isset($ourumodes[$i]) && $m = $ourumodes[$i]; $i++)
            {
                /* if we have a mode that they don't have */
                if (!strstr($theirumodes, $m))
                {
                    ++$this->num_of_problems['usermodes'];
                    $this->problems['usermodes'][] = "User mode $m is present on $s->name but missing on $serv->name";
                }
            }
        }
    }

    /**
     * Checks modules of servers against other servers
     * @return void
     */
    function module_check() : void
    {
        global $rpc;
        $modlist = [];
        $modarray = [];
        foreach ($this->serverlist as $serv)
            $modlist[$serv->name] = json_decode(json_encode($rpc->server()->module_list($serv->id)->list), true);

        echo highlight_string(var_export($modlist,true));
    }
}