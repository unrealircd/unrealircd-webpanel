import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import { homedir } from 'os';
import { resolve } from 'path';
import fs from 'fs';

export default defineConfig({
    server: detectServerConfig(),
    plugins: [
        laravel({
            input: 'resources/js/app.js',
            refresh: true
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: 'https://unreal.test/',
                    includeAbsolute: false,
                },
            },
        }),
    ],
})
;

function detectServerConfig() {
    let host = 'unreal.test';

    let keyPath = resolve(homedir(), `.config/valet/Certificates/${host}.key`)
    let certificatePath = resolve(homedir(), `.config/valet/Certificates/${host}.crt`)

    if (!fs.existsSync(keyPath) || !fs.existsSync(certificatePath)) {
        return {};
    }

    console.log(host);

    return {
        hmr: { host },
        host,
        https: {
            key: fs.readFileSync(keyPath),
            cert: fs.readFileSync(certificatePath)
        }
    }
}
