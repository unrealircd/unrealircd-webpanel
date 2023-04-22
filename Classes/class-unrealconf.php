<?php 

require_once("../inc/common.php");

class Conf
{
	static $settings_short = [];
	static $settings_temp = [];
	static $settings = [];
	function __construct($filename, &$error)
	{
		if (!is_file($filename))
		{
			$error[] = "Not a valid configuration file: $filename";
		}
		else
		{
			$file = file_get_contents($filename);
			$file = preg_replace('/\/\*(.|\s)*?\*\//', '', $file);
			//$file = preg_replace('/\/\/(.|\s)*?\n/', '', $file);
			$file = str_replace("\t"," ",$file);
			$file = str_replace("\n\n","\n",$file);
			$file = str_replace("\n", " ",$file);
			for(; strstr($file,"  ");)
				$file = str_replace("  "," ",$file);
			$config = $this->parse_config($file, $error);
		}
	}

	private function parse_config($string, &$error)
	{
		$tok = split($string);
		$blockstring = "";
		$full = "";
		foreach($tok as $str)
		{
			$str = trim($str);
            if ($str[0] == '#' || substr($str,0,2) == "//")
            {
                var_dump($str);
                continue;
            }
			if (!strcmp($str,"{") && mb_substr($blockstring,-2,2) !== "::")
				strcat($blockstring,"::");
			
			elseif (!strcmp($str,"}"))
			{
				$split = split($blockstring,"::");
				if (BadPtr($split[sizeof($split) - 1]))
					unset($split[sizeof($split) - 1]);
				unset($split[sizeof($split) - 1]);
				$blockstring = glue($split,"::");
				if (!BadPtr($blockstring))
				{
					strcat($blockstring,"::");
				}
			}
			// if we found a value and it's time to go to the next one
			elseif (!BadPtr($str) && $str[strlen($str) - 1] == ";")
			{
				if (substr_count($str,"\"") != 1)
					strcat($blockstring, "::".rtrim($str,";")); // finish off our item
				else strcat($blockstring, " ".rtrim($str,";"));
				strcat($full,str_replace(["::::", "\""],["::", ""],$blockstring)."\n"); // add the full line to our $full variable
				
				/* rejig the blockstring */
				$split = split($blockstring,"::");
				if (BadPtr($split[sizeof($split) - 1]))
					unset($split[sizeof($split) - 1]);
				unset($split[sizeof($split) - 1]);
				unset($split[sizeof($split) - 1]);
				$blockstring = glue($split,"::");
				if (!BadPtr($blockstring))
				{
					rtrim($blockstring,":");
					strcat($blockstring,"::");
				}
			}

			else
			{	if (!BadPtr($blockstring) && mb_substr($blockstring,-2,2) !== "::")
					strcat($blockstring," ");
				strcat($blockstring,$str);
			}
		}
		
		$full = split($full,"\n");
        echo highlight_string(var_export($full, true));
		$long = [];

		foreach($full as $config_item)
		{
			$arr = &$long;
			self::$settings_short[] = $config_item;
			$tok = split($config_item,"::");
			for ($i = 0; $i <= count($tok); $i++)
			{
				if (isset($tok[$i + 2]))
					$arr = &$arr[$tok[$i]];					
				
				elseif (isset($tok[$i + 1]) && isset($tok[$i - 1]))
					$arr[$tok[$i]] = $tok[$i + 1];

				elseif (isset($tok[$i + 1]))
					$arr[$tok[$i]][] = $tok[$i + 1];
			}
		}
		self::$settings_temp = $long;
		$cf = &self::$settings_temp;
        
		if (!empty($error))
		{
			self::$settings_temp = [];
			return false;
		}
		$arr = ['cfg' => $cf, 'err' => &$error];
		
        
		if (!empty($error))
		{
			self::$settings_temp = [];
			return false;
		}
		self::$settings = self::$settings_temp;
		self::$settings_temp = [];

        echo highlight_string(var_export(self::$settings, true));
	}
    function parse2()
    {
        $configFile = 'unrealircd.conf';

        $config = array();

        if (file_exists($configFile)) {
            $lines = file($configFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line)
            {
                $config[] = trim($line);
            }
        }
        echo highlight_string(var_export($config, true));
        return $config;
    }
}

$errors = [];
new Conf("unrealircd.conf", $errors);
