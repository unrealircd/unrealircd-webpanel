<?php

define('NO_EVENT_STREAM_HEADER', 1);
require_once('common_api.php');

if (!current_user_can(PERMISSION_MANAGE_PLUGINS))
    die(json_encode(['error' => "Access denied"]));

/* Listing is a GET. Installing and uninstalling change the panel's code,
 * so they need a POST (and thus the CSRF token, checked in inc/common.php).
 */
$is_post = (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST');
if (isset($_GET['install']) || isset($_GET['uninstall']))
{
    http_response_code(405);
    die(json_encode(['error' => "Use POST to install or uninstall plugins"]));
}
if (!$is_post)
    die(json_encode($config['third-party-plugins']['data'] ?? []));

if (isset($_POST['install']) && is_string($_POST['install']))
{
    install_plugin($_POST['install']);
}
elseif (isset($_POST['uninstall']) && is_string($_POST['uninstall']))
{
    uninstall_plugin($_POST['uninstall']);
}
die(json_encode(['error' => "Nothing to do"]));

/** A plugin name is used as a directory name: only allow simple names */
function valid_plugin_name($name) : bool
{
    return is_string($name) && preg_match('/^[A-Za-z0-9_-]{1,64}$/D', $name);
}

function uninstall_plugin($name)
{
    global $config;
    if (!valid_plugin_name($name))
        die(json_encode(['error' => "Invalid plugin name"]));
    /* Never remove the database backends this way */
    if (in_array($name, ["file_db", "sql_db"], true))
        die(json_encode(['error' => "This plugin cannot be uninstalled here"]));
    if (!in_array($name, $config['plugins'], true))
        die(json_encode(['error' => "Plugin not loaded"]));
    
    foreach($config['plugins'] as $k => $v)
        if ($v == $name)
            unset($config['plugins'][$k]);
    write_config();

    deleteDirectory(UPATH."/plugins/$name");
    die(json_encode(["success" => "Plugin was deleted successfully"]));
}

/**Attempt to install the plugin 
 * @param string $name name of the plugin
 * @return void
 */
function install_plugin($name)
{
    global $config;
    if (!valid_plugin_name($name))
        die(json_encode(['error' => "Invalid plugin name"]));
    if (in_array($name, $config['plugins']))
        die(json_encode(["error" => "Plugin already installed"]));
    $url = get_plugin_install_path_from_name($name);
    if (!$url)
        die(json_encode(["error" => "Unknown plugin"]));
    $pluginfile = file_get_contents($url);
    if (!is_dir(UPATH."/data/tmp"))
        mkdir(UPATH."/data/tmp");

    $path = UPATH."/data/tmp/";
    $file = $path.md5(time()).".tmp";
    if (!file_put_contents($file, $pluginfile))
        die(json_encode(["error" => "Cannot write to directory: Need write permission"]));

    unset($pluginfile);

    $zip = new ZipArchive;
    $res = $zip->open($file);
    if ($res !== true) {
        unlink($file);
        die(json_encode(["error" => "Could not open file we just wrote lol"]));
    }

    // ensure we have no conflicts
    $extractPath = UPATH."/plugins/$name";
    // lazy upgrade for now.
    if (is_dir($extractPath))
    {
        deleteDirectory($extractPath);
    }
    mkdir($extractPath);
    $zip->extractTo($extractPath);
    $zip->close();

    //clear up our temp shit
    unset($zip);
    unlink($file);
    unset($res);

    // load it in the config
    $config['plugins'][] = $name;
    write_config();
    
    // wahey
    die(json_encode(['success' => "Installation was complete"]));
}

/**
 * @param string $name Name of plugin
 * @return NULL|string Path or NULL
 */
function get_plugin_install_path_from_name($name)
{
    global $config;
    $list = $config['third-party-plugins']['data']->list;
    foreach($list as $p)
    {
        if (!strcmp($p->name,$name))
            return $p->download_link;
    }
    return NULL;
}

function deleteDirectory($dir) {
    if (!file_exists($dir)) {
        return true;
    }

    if (!is_dir($dir)) {
        return unlink($dir);
    }

    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }

        if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
            return false;
        }

    }

    return rmdir($dir);
}