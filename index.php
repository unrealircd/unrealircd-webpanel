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
echo "
<link href=\"/css/unrealircd-admin.css\" rel=\"stylesheet\">
<h1>UnrealIRCd</h1>
<div class=\"tab-content\">
<div id=\"overview\" data-tab-content class=\"active\">
    <h2>IRC Overview Panel</h2>
    <table class='unrealircd_overview'>
        <tr><td>Users</td><td>".count(RPC_List::$user)."</td></tr>
        <tr><td>Opers</td><td>".RPC_List::$opercount."</td></tr>
        <tr><td>Services</td><td>".RPC_List::$services_count."</td></tr>
        <tr><td>Most popular channel</td><td>".RPC_List::$most_populated_channel." (".RPC_List::$channel_pop_count." users)</td></tr>
        <tr><td>Channels</td><td>".count(RPC_List::$channel)."</td></tr>
        <tr><td>Server bans</td><td>".count(RPC_List::$tkl)."</td></tr>
        <tr><td>Spamfilter entries</td><td>".count(RPC_List::$spamfilter)."</td></tr>
    </table></div></div>";
?>
