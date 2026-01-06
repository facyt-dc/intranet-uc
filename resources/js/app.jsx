import "./bootstrap";
import "../css/app.css";
import "@/i18n";

import { createRoot } from "react-dom/client";
import { createInertiaApp } from "@inertiajs/react";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";

const appName = import.meta.env.VITE_APP_NAME || "Laravel";

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => {
        // Define the globs for both locations
        // Main App: resources/js/Pages
        const pages = import.meta.glob("./Pages/**/*.jsx");
        
        // Modules: Modules/{Module}/resources/assets/js/Pages
        // Note: The path is relative to this app.jsx file (resources/js/)
        const modulePages = import.meta.glob("../../Modules/**/resources/assets/js/Pages/**/*.jsx");

        // Determine the path
        // Case A: Using the "Module::Page" syntax (Recommended for clarity)
        if (name.includes('::')) {
            const [module, page] = name.split('::');
            return resolvePageComponent(
                `../../Modules/${module}/resources/assets/js/Pages/${page}.jsx`,
                modulePages
            );
        }

        // Case B: Main App Page (Standard)
        const mainPath = `./Pages/${name}.jsx`;
        if (mainPath in pages) {
            return resolvePageComponent(mainPath, pages);
        }

        throw new Error(`Page not found: ${name}`);
    },
    setup({ el, App, props }) {
        const root = createRoot(el);
        root.render(<App {...props} />);
    },
    progress: {
        color: "#4B5563",
    },
});
