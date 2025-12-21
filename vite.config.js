import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import react from "@vitejs/plugin-react";
import { viteStaticCopy } from 'vite-plugin-static-copy'

export default defineConfig({
    plugins: [
        laravel({
            input: "resources/js/app.jsx",
            refresh: true,
        }),
        react(),
        viteStaticCopy({
            targets: [
                {
                    // 1. Look inside every Module for a /locales folder
                    src: 'Modules/*/resources/assets/js/i18n/locales/**/*',
                    // 2. Copy them to the public/locales folder in the build/server
                    dest: 'locales'
                }
            ]
        })
    ],
});
