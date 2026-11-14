<?php

/** Check that $ip is a plain IPv4 or IPv6 address.
 * Only IP addresses are looked up: no domain names (their WHOIS data is
 * chosen by the domain owner) and nothing that could add lines to the query.
 */
function is_valid_whois_ip($ip) : bool
{
	return is_string($ip) && (filter_var($ip, FILTER_VALIDATE_IP) !== false);
}

function get_ip_whois($ip)
{
	if (!is_valid_whois_ip($ip))
	{
		Message::Fail("Lookup failed: that is not a valid IP address");
		return "";
	}

	$w = get_ip_whois_from_server('whois.iana.org' , $ip);

	if (!preg_match("#whois:\s*([\w.-]+)#si" , $w , $data))
	{
		Message::Fail("Lookup failed: Could not find appropriate WHOIS server. Maybe you typed an incorrect IP?");
		return "";
	}

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
		Message::Fail("Lookup failed: Could not find appropriate WHOIS server. Maybe you typed an incorrect IP?");
		return "";
	}

	$f = @fsockopen($server, 43, $errno, $errstr, 3);	//Open a new connection

	if (!$f)
	{
		Message::Fail("Lookup failed: Could not open socket");
		return "";
	}

	// Set the timeout limit for reads
	if (!stream_set_timeout($f , 10))
	{
		fclose($f);
		Message::Fail("Lookup failed: Unable to stream_set_timeout");
		return "";
	}

	// Send the IP to the whois server
	fputs($f, $ip . "\r\n");

	// Read the answer, up to 256KB
	while (!feof($f) && (strlen($data) < 262144))
	{
		$chunk = fread($f , 8192);
		if ($chunk === false)
			break;
		$info = stream_get_meta_data($f);
		if ($info['timed_out'])
			break;
		$data .= $chunk;
	}
	fclose($f);

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
								/* WHOIS data is written by whoever runs that IP block: escape everything */
								?>
								<tr><th><?php echo h($key); ?></th><td>
										<?php
											if (filter_var($val, FILTER_VALIDATE_EMAIL))
												$val = "<a href=\"mailto:".h($val)."\">".h($val)."</a>";
											else
												$val = nl2br(h($val));
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
