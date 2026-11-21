import { defineConfig } from 'vite';
import { resolve } from 'path';

export default defineConfig({
    build: {
        manifest: true,
        outDir: 'dist',
        emptyOutDir: true,

        rollupOptions: {
            input: {
                main: resolve(import.meta.dirname, 'src/main.js'),
            },
        },
    },
});

