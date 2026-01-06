# Guía de Frontend: Inertia, Vite e i18n

Hemos personalizado la integración de Inertia y las traducciones para soportar la arquitectura modular, y las particularidades del proyecto.

## 1. Vistas de Inertia en Módulos
Inertia por defecto busca vistas solo en `resources/js/Pages`. Hemos modificado el `app.jsx` para que resuelva vistas usando la sintaxis `Modulo::Pagina`.

*   **Uso en Controlador:** `return Inertia::render('AgendaConsejo::Agendas/Index', ...);`
*   **Resolución:** El `resolvePageComponent` en `app.jsx` busca automáticamente en `../../Modules/{Modulo}/resources/assets/js/Pages/{Pagina}.jsx`.

## 2. Sistema de Traducciones (i18next)
Cada módulo contiene sus propios archivos de traducción en `Modules/{Modulo}/resources/assets/js/i18n/locales/{lang}/{namespace}.json`.

### Carga Dinámica
Para que el frontend pueda acceder a estos JSONs que están "ocultos" en la carpeta de Módulos, hemos implementado dos estrategias:

1.  **En Desarrollo (`npm run dev`):**
    El archivo `i18n.js` utiliza `import.meta.url` para detectar el puerto de Vite y solicitar los JSONs directamente a través del servidor de desarrollo.

2.  **En Producción / General:**
    Existe una ruta en Laravel (`/locales/{lang}/{namespace}.json`) gestionada por `LocaleController`. Este controlador busca el archivo físico en el módulo y lo devuelve como respuesta JSON.

### Uso en React
```jsx
import { useTranslation } from 'react-i18next';

// Cargar el namespace 'agenda' (definido por el nombre del archivo agenda.json)
const { t } = useTranslation(['common', 'agenda']);

return <h1>{t('agenda:create_agenda')}</h1>;