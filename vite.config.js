// vite.config.js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css', // Este ya importa a salas.css, libros.css, etc.
                'resources/css/componentes/perfil.css',
                'resources/css/componentes/perfil-amigo.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
});