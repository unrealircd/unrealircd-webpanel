<?php

require_once "../common.php";
require_once "../header.php";

$ip_info = [];
$ip = NULL;
$noip = NULL;
if (isset($_GET['ip']))
    $_POST['ip'] = $_GET['ip'];

if (isset($_POST['ip']))
    $ip = $_POST['ip'];

$title = "IP WHOIS Lookup";
$title .= (isset($noip)) ? "" : " for $ip";

echo "<h4>$title</h4>";


if (!isset($ip))
    $noip = true;

else
{
    $whois = get_ip_whois($ip);
    $file = split($whois, "\n");

    $i = 0;

    $start = false;
    foreach ($file as $line) {
        if (!strlen($line) && $start)
            $i++;

        if (($line && !ctype_alnum($line[0])) || !$line) // we don't care about your opinion we just want the info
            continue;
        $start = true;
        $tok = split($line);
        foreach ($tok as &$t)
            if (!strlen($t))
                $t = NULL;

        $resplit = split(glue($tok));

        $key = trim($resplit[0], ":");
        $resplit[0] = NULL;
        $value = glue($resplit);

        if (!isset($ip_info[$i][$key]))
            $ip_info[$i][$key] = $value;
        else
            $ip_info[$i][$key] .= "\n $value";
    }
}
?>

<br>
<form method="get" action="ip-whois.php">
<div class="input-group">
	<input class="short-form-control" id="ip" name="ip" type="text" value=<?php echo $ip; ?>>
	<div class="input-group-append">
		<br><button type="submit" class="btn btn-primary">Go</button>
	</div>
</div>
</form>

<?php

if ($ip)
{
    ?>
    <div class="container-xxl mt-3">
        <div class="row">
            <div class="col">
                <?php generate_ip_whois_table($ip_info); ?>
            </div>
            <div class="col-sm">
              <div class="btn btn-sm btn-info" data-toggle="modal" data-target="#ip_whois_raw">View RAW</div>
            </div>
        </div>
    </div>
    
    <?php
}
if (!isset($whois))
    return;
?>

<div class="modal fade" id="ip_whois_raw" tabindex="-1" role="dialog" aria-labelledby="confirmModalCenterTitle" aria-hidden="true">
	<div class="modal-dialog modal-xl modal-dialog-centered" role="document">
		<div class="modal-content">
		<div class="modal-header">
			<h5 class="modal-title" id="myModalLabel">RAW IP WHOIS Information</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			<span aria-hidden="true">&times;</span>
			</button>
		</div>
		<div class="modal-body">
            <code><?php echo str_replace("\n", "<br>",htmlspecialchars($whois)); ?></code>
		</div>
		<div class="modal-footer">
            <button id="CloseButton" type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
		</div>
		</div>
	</div>
</div>

<?php require_once("../footer.php");