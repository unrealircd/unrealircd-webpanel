<?php
require_once "../inc/common.php";
require_once "../inc/connection.php";
require_once "../inc/header.php";

$title = __('unrealircd_user_details_title');
$nickname = "";
$nick = NULL;
if (isset($_GET['nick']))
{
	$nickname = $_GET['nick'];
	$nick = $rpc->user()->get($nickname);
	if (!$nick)
	{
		Message::Fail(sprintf(__('unrealircd_user_details_nonick'), $nickname));
	} else {
		$nickname = $nick->name;
		$title .= " \"" . $nickname . "\"";
	}
}
?>
<title><?php echo $title; ?></title>
<h4><?php echo $title; ?></h4>
<br>
<form method="get" action="details.php">
  <div class="input-group short-form-control">
    <input class="short-form-control" id="nick" name="nick" type="text" value=<?php echo $nickname; ?>>
    <div class="input-group-append">
      <br><button type="submit" class="btn btn-primary"><?php echo __('unrealircd_user_buttongo'); ?></button>
    </div>
  </div>
</form>

<?php if (!$nick)
{
	require_once "../inc/footer.php";
	return;
}

?>
<br>
<div class="container-xxl">
  <div class="row">
    <div class="col-sm-3">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title"><?php echo __('unrealircd_user_basic_information'); ?></h5>
          <p class="card-text"><?php generate_html_whois($nick); ?></p>
        </div>
      </div>
    </div>
    <div class="col-sm-4">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title"><?php echo __('unrealircd_user_settings'); ?></h5>
          <p class="card-text"><?php generate_html_usersettings($nick); ?></p>
        </div>
      </div>
    </div>
    <div class="col-sm-3">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title"><?php echo __('unrealircd_user_channels'); ?></h5>
            <p class="card-text"><?php generate_html_userchannels($nick); ?></p>
          </div>
        </div>
      </div>
  </div>
</div>
<?php require_once UPATH.'/inc/footer.php'; ?>


