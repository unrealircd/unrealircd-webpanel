<?php

require_once('common_api.php');

if (!current_user_can(PERMISSION_MANAGE_PLUGINS))
    die(json_encode(['error' => "Access denied"]));
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET')
    die(json_encode($config['third-party-plugins']['data'] ?? []));

// Mutations must not be triggered by a GET request or a cross-site link.
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST')
{
    header('Allow: GET, POST');
    http_response_code(405);
    die(json_encode(['error' => 'Method not allowed']));
}
if (isset($_POST['install']))
    install_plugin($_POST['install']);
elseif (isset($_POST['uninstall']))
    uninstall_plugin($_POST['uninstall']);
else
    die(json_encode(['error' => 'Unknown operation']));

function uninstall_plugin($name)
{
    global $config;
    if (!is_string($name) || !preg_match('/\A[a-zA-Z0-9_-]+\z/D', $name))
        die(json_encode(['error' => 'Invalid plugin name']));
    if (!Plugins::plugin_exists($name))
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
    if (!is_string($name) || !preg_match('/\A[a-zA-Z0-9_-]+\z/D', $name))
        die(json_encode(['error' => 'Invalid plugin name']));
    if (in_array($name, $config['plugins']))
        die(json_encode(["error" => "Plugin already installed"]));
    $url = get_plugin_install_path_from_name($name);
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