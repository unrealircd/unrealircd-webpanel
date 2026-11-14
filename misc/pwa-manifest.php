<?php

function create_pwa_manifest()
{
    /* BASE_URL is only defined by the installer. Elsewhere (eg. at login,
     * when manifest.json is missing) use the configured base_url.
     */
    $base_url = defined('BASE_URL') ? BASE_URL : get_config("base_url");
    if (!$base_url)
        $base_url = "/";

    $manifest = [];
    $manifest['name'] = "UnrealIRCd Admin WebPanel";
    $manifest['short_name'] = "WebPanel";
    $manifest['start_url'] = $base_url;
    $manifest['display'] = 'standalone';
    $manifest['background_color'] = '#fff';
    $manifest['theme_color'] = '#4a86ee';
    $manifest['icons'] = [
        (object)[
            'src' => $base_url.'img/favicon.ico',
            'sizes' => '64x64',
            'type' => 'image/x-icon'
        ],
        [
            'src' => $base_url.'img/unreal.jpg',
            'sizes' => '128x128',
            'type' => 'image/x-icon'
        ]
    ];

    $json = json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    return @file_put_contents(UPATH.'/manifest.json', $json) ? true : false;
}
