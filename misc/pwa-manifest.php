<?php

function create_pwa_manifest()
{
    $manifest = [];
    $manifest['name'] = "UnrealIRCd Admin WebPanel";
    $manifest['short_name'] = "WebPanel";
    $manifest['start_url'] = BASE_URL;
    $manifest['display'] = 'standalone';
    $manifest['background_color'] = '#fff';
    $manifest['theme_color'] = '#4a86ee';
    $manifest['icons'] = [
        (object)[
            'src' => BASE_URL.'img/favicon.ico',
            'sizes' => '64x64',
            'type' => 'image/x-icon'
        ],
        [
            'src' => BASE_URL.'img/unreal.jpg',
            'sizes' => '128x128',
            'type' => 'image/x-icon'
        ]
    ];

    $json = json_encode($manifest, JSON_PRETTY_PRINT);
    return file_put_contents('../manifest.json', $json) ? true : false;
}
