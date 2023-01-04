<?php

/**
 * RPC Functionality for UnrealIRCd Admin Webpanel
 * License: GPLv3 or later
 * Author: Valware
 * 2023
 */

if (!defined('UPATH'))
	die("Access denied");

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
		do_log("Set method:", $method);
		$this->body['method'] = $method;
	}

	function set_params(array $params) : void
	{
		do_log("Set params:", $params);
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
		do_log("RPC::fetch_assoc()", $this->result);
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


/** RPC TKL Add */
function rpc_tkl_add($name, $type, $expiry, $reason) : bool
{
	$rpc = new RPC();
	$rpc->set_method("server_ban.add");
	$rpc->set_params(["name" => $name, "type" => $type, "reason" => $reason, "duration_string" => $expiry]);
	$rpc->execute();
	$result = $rpc->fetch_assoc();
	if (isset($result['error']))
	{
		$msg = "The $type could not be added: $name - ".$result['error']['message'] . " (" . $result['error']['code'] . ")";
		Message::Fail($msg);
		return false;
	}
	return true;
}


/** RPC TKL Delete */
function rpc_tkl_del($name, $type) : bool
{
	$rpc = new RPC();
	$rpc->set_method("server_ban.del");
	$rpc->set_params(["name" => $name, "type" => $type]);
	$rpc->execute();
	$result = $rpc->fetch_assoc();
	if (isset($result['error']))
	{
		$msg = "The $type could not be deleted: $name - ".$result['error']['message'] . " (" . $result['error']['code'] . ")";
		Message::Fail($msg);
		return false;
	}
	return true;
}

/** RPC Spamfilter Delete
 * 
 */
function rpc_sf_del($name, $mtype, $targets, $action) : bool
{
	$rpc = new RPC();
	$rpc->set_method("spamfilter.del");
	$rpc->set_params(["name" => $name, "match_type" => $mtype, "spamfilter_targets" => $targets, "ban_action" => $action, "set_by" => "YoMama"]);
	$rpc->execute();
	$result = $rpc->fetch_assoc();
	if (isset($result['error']))
	{
		$msg = "The spamfilter entry could not be deleted: $name - ".$result['error']['message'] . " (" . $result['error']['code'] . ")";
		Message::Fail($msg);
		return false;
	}
	else
	{
		$r = $result['result']['tkl']; 
		Message::Success($r['name']." [type: ".$r['match_type']."] [targets: ".$r['spamfilter_targets']. "] [action: ".$r['ban_action']."] [reason: ".$r['reason']."] [set by: ".$r['set_by']."]");
	}
	return true;
}