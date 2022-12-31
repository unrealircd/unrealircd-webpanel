<link href="/css/unrealircd-admin.css" rel="stylesheet">
<body>
<div id="headerContainer">
<h2>UnrealIRCd <small>Administration Panel</small></h2><br>
</div>
<script src="js/unrealircd-admin.js" defer></script>
<div class="topnav">
  <a data-tab-target="#overview" class="active" href="#overview">Overview</a>
  <a data-tab-target="#Users" href="#Users">Users</a>
  <a data-tab-target="#Channels" href="#Channels">Channels</a>
  <a data-tab-target="#TKL" href="#TKL">Server Bans</a>
  <a data-tab-target="#Spamfilter" href="#Spamfilter">Spamfilter</a>
</div> 
<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://https://github.com/ValwareIRC
 * @since      1.0.0
 *
 * @package    Unrealircd
 * @subpackage Unrealircd/admin/partials
 */

define('UPATH', true);
include "Classes/class-rpc.php";

rpc_pop_lists();
?>

<div class="tab-content\">
<div id="overview" data-tab-content class="active">
    <table class='unrealircd_overview'>
    <th>Chat Overview</th><th></th>
        <tr><td><b>Users</b></td><td><?php echo count(RPC_List::$user); ?></td></tr>
        <tr><td><b>Opers</b></td><td><?php echo RPC_List::$opercount; ?></td></tr>
        <tr><td><b>Services</b></td><td><?php echo RPC_List::$services_count; ?></td></tr>
        <tr><td><b>Most popular channel</b></td><td><?php echo RPC_List::$most_populated_channel; ?> (<?php echo RPC_List::$channel_pop_count; ?> users)</td></tr>
        <tr><td><b>Channels</b></td><td><?php echo count(RPC_List::$channel); ?></td></tr>
        <tr><td><b>Server bans</b></td><td><?php echo count(RPC_List::$tkl); ?></td></tr>
        <tr><td><b>Spamfilter entries</b></td><td><?php echo count(RPC_List::$spamfilter); ?></td></tr></th>
    </table></div></div>

<div class="tab-content\">
<div id="users" data-tab-content>
    <h2>Users Overview Panel</h2>
    <table class='unrealircd_overview'>
    <th>
    <tr>
      <td>
      </td>
    </tr>
</th>
    <tbody>

    </tbody>
    </table></div></div>

</body>