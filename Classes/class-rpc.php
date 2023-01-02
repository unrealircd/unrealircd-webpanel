<?php

if (!defined('UPATH'))
	die("Access denied");


/** The RPC User name as defined in your unrealircd.conf */
define( 'UNREALIRCD_RPC_USER', 'apiuser' );

/** The RPC User password as defined in your unrealircd.conf */
define( 'UNREALIRCD_RPC_PASSWORD', 'securepassword' );

/** The host of the RPC server */
define( 'UNREALIRCD_HOST', '127.0.0.1' );

/** The port of your RPC server as defined in your unrealircd.conf */
define( 'UNREALIRCD_PORT', '8000' );

/* You should only really uncomment this if you are running on 
 * localhost and for some reason don't have a fully valid cert
*/
define( 'UNREALIRCD_SSL_VERIFY', false );

/* You should only really need this if you're developing something. */
define( 'UNREALIRCD_DEBUG', true );

class RPC
{
	public $errs = [];
	public $errcount = 0; // more of a bool check
	public $content = [];
	public $body = [];
	public $result = NULL;
	function __construct()
	{
		if (!defined('UNREALIRCD_RPC_USER') ||
			!defined('UNREALIRCD_RPC_PASSWORD') ||
			!defined('UNREALIRCD_HOST') ||
			!defined('UNREALIRCD_PORT')
		) die("Unable to find RPC credentials in your wp-config");

		$sslverify = (defined('UNREALIRCD_SSL_VERIFY')) ? UNREALIRCD_SSL_VERIFY : true;

		$this->content['sslverify'] = $sslverify;
		$this->body['id'] = $this->generate_id();
		$this->body['jsonrpc'] = "2.0";
		$this->body['method'] = NULL; // MUST be set later
		$this->body['params'] = []; // CAN be set later
	}
	function add_body(array $b) : void
	{
		array_merge($this->body, $b);
	}

	private function generate_id()
	{
		$time = microtime(true);
		$str = (string)$time;
		$last = $str[strlen($str) - 1];
		$last = (int)$last;
		$id = $time * $time * $last;
		$id = md5(base64_encode($id));
		return $id;
	}

	/**
	 * This function sets the method of the RPC call you're making.
	 * For a list of available methods, see:
	 * https://www.unrealircd.org/docs/JSON-RPC#JSON-RPC_Methods
	 */
	function set_method(String $method) : void
	{
		$this->body['method'] = $method;
	}

	function set_params(array $params) : void
	{
		$this->body['params'] = $params;
	}

	function execute()
	{
		$this->content['body'] = json_encode($this->body);
		if (!$this->content['body'])
			return;
		$url = "https://".UNREALIRCD_HOST.":".UNREALIRCD_PORT."/api";
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

		$headers = array(
			"Accept: application/json",
			"Content-Type: application/json",
			"Authorization: Basic ". base64_encode(UNREALIRCD_RPC_USER.":".UNREALIRCD_RPC_PASSWORD),
		);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

		curl_setopt($curl, CURLOPT_POSTFIELDS, $this->content['body']);

		//for debug only!
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

		$apiResponse = curl_exec($curl);
		curl_close($curl);
		
		$this->result = $apiResponse;
	}

	function fetch_assoc()
	{
		return json_decode($this->result, true);
	}

	static function die(array $err)
	{
		die("There was a problem processing the request: ".$err['message']." (".$err['code'].")<br>Please contact the plugin author.<br>".
					"If you are a developer, see: <a href=\"https://www.unrealircd.org/docs/JSON-RPC#Error\">https://www.unrealircd.org/docs/JSON-RPC#Error</a>");
	}
}

class RPC_List
{
	static $user = [];
	static $channel = [];
	static $tkl = [];
	static $spamfilter = [];

	static $opercount = 0;
	static $services_count = 0;
	static $most_populated_channel = NULL;
	static $channel_pop_count = 0;
}

function rpc_pop_lists()
{
	$rpc = new RPC();

	/* Get the user list */
	$rpc->set_method("user.list");
	$rpc->execute();

	foreach($rpc->fetch_assoc() as $key => $value)
	{
		if ($key == "error")
		{
			RPC::die($value);
			return;
		}
		if ($key == "result")
			foreach($value['list'] as $r)
			{
				RPC_List::$user[] = $r;
				if (strpos($r['user']['modes'],"o") !== false && strpos($r['user']['modes'],"S") == false)
					RPC_List::$opercount++;
				elseif (strpos($r['user']['modes'],"S") !== false)
					RPC_List::$services_count++;
			}
	}

	/* Get the channels list */
	$rpc->set_method("channel.list");
	$rpc->execute();

	foreach($rpc->fetch_assoc() as $key => $value)
	{
		if ($key == "error")
		{
			RPC::die($value);
			return;
		}
		if ($key == "result")
			foreach($value['list'] as $r)
			{
				RPC_List::$channel[] = $r;
				if ($r['num_users'] > RPC_List::$channel_pop_count)
				{
					RPC_List::$channel_pop_count = $r['num_users'];
					RPC_List::$most_populated_channel = $r['name'];
				}
			}
	}
	
	/* Get the tkl list */
	$rpc->set_method("server_ban.list");
	$rpc->execute();

	foreach($rpc->fetch_assoc() as $key => $value)
	{
		if ($key == "error")
		{
			RPC::die($value);
			return;
		}
		if ($key == "result")
			foreach($value['list'] as $r)
				RPC_List::$tkl[] = $r;
	}

	
	/* Get the tkl list */
	$rpc->set_method("spamfilter.list");
	$rpc->execute();

	foreach($rpc->fetch_assoc() as $key => $value)
	{
		if ($key == "error")
		{
			RPC::die($value);
			return;
		}
		if ($key == "result")
			foreach($value['list'] as $r)
				RPC_List::$spamfilter[] = $r;
	}

}


/** RPC TKL Delete */
function rpc_tkl_del($name, $type) : bool
{
	$rpc = new RPC();
	$rpc->set_method("server_ban.del");
	$rpc->set_params(["name" => $name, "type" => $type]);
	$rpc->execute();
	foreach($rpc->fetch_assoc() as $r)
	{
		highlight_string(var_export($r, true));
	}
	return true;
}