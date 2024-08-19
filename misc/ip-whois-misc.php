<?php

function get_ip_whois($ip) 
{
	$w = get_ip_whois_from_server('whois.iana.org' , $ip);

	preg_match("#whois:\s*([\w.]*)#si" , $w , $data);

	$whois_server = $data[1];
	$whois_data = get_ip_whois_from_server($whois_server , $ip);

	return $whois_data;
}

function get_ip_whois_from_server($server , $ip) 
{
	$data = '';
	$server = trim($server);

	if(!strlen($server))
	{
        return Message::Fail("Lookup failed: Could not find appropriate WHOIS server. Maybe you typed an incorrect IP?");
	}
	
	$f = fsockopen($server, 43, $errno, $errstr, 3);	//Open a new connection
	
	if (!$f)
	{
        Message::Fail("Lookup failed: Could not open socket");
	}
		
	// Set the timeout limit for read
	if (!stream_set_timeout($f , 10))
	{
        return Message::Fail("Lookup failed: Connection timed out");
	}
	
	// Send the IP to the whois server	
	if ($f)
	{
		$message = $ip . "\r\n";
		fputs($f, $message);
	}
	
    
	if( !stream_set_timeout($f , 3))
	{
		return Message::Fail("Lookup failed: Unable to stream_set_timeout");
	}
	
	// Set socket in non-blocking mode
	stream_set_blocking ($f, 0 );
	
	// If connection still valid
	if($f) 
	{
		while (!feof($f)) 
		{
			$data .= fread($f , 128);
		}
	}
	
	// Now return the data
	return $data;
}

function generate_ip_whois_table($data)
{
    ?>
		<div class="container-xl">
			<table class="table table-responsive-xl caption-top table-hover">
				<tbody>
					<?php
						foreach ($data as $d)
							foreach ($d as $key => $val)
							{
								?>
								<tr><th><?php echo htmlspecialchars($key); ?></th><td>
										<?php
											if (filter_var($val, FILTER_VALIDATE_EMAIL))
												$val = "<a href=\"mailto:$val\">$val</a>";
											echo "<code>$val</code>";
										?>
								</td></tr>
								<?php
							}
					?>
				</tbody>
			</table>
		</div>
    <?php
}