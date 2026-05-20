# Reporte de Fase 1.5 — Arreglo de Tailwind para módulos

**Fecha:** 2026-05-20
**Rama:** develop
**Commit HEAD antes de iniciar:** 1e3bc76

---

## Resumen ejecutivo

- Glob de módulos agregado a tailwind.config.js: **SÍ**
- tailwindcss-animate instalado: **SÍ** (v^1.0.7)
- darkMode configurado: **SÍ** (`["class"]`)
- Tokens de shadcn/ui agregados a theme.extend: **SÍ** (colors + borderRadius)
- Variables CSS agregadas a app.css: **SÍ** (14 variables)
- Tests siguen pasando: **38/38**
- Build sin errores de sintaxis: **SÍ** (`✓ built in 5.25s`)
- Estado general: **VERDE**

---

## PARTE 1 — Estado previo

### tailwind.config.js antes

```js
import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";

export default {
    content: [
        "./vendor/laravel/framework/.../views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
        "./resources/js/**/*.jsx",
        // ← SIN glob de Modules/
    ],
    theme: {
        extend: {
            fontFamily: { sans: ["Figtree", ...] },
            // ← SIN colors ni borderRadius
        },
    },
    plugins: [forms],  // ← SIN animate, SIN darkMode
};
```

### app.css antes

```css
@tailwind base;
@tailwind components;
@tailwind utilities;

/* scrollbar */
::-webkit-scrollbar { ... }
```

Sin ningún bloque `@layer base { :root { ... } }`.

### tailwindcss-animate en devDependencies

- Antes: **AUSENTE**
- Versión instalada: `^1.0.7`

No había conflictos con `colors` ni `borderRadius` en `theme.extend` (solo existía `fontFamily`).
No había bloque `@layer base` en `app.css`. Todos los cambios procedieron sin conflictos.

---

## PARTE 2 — Instalación de tailwindcss-animate

- Comando ejecutado: `npm install -D tailwindcss-animate`
- Resultado: **éxito** — 1 paquete agregado, 789 auditados
- Versión instalada: `^1.0.7`
- Nota: se reportaron 9 vulnerabilidades preexistentes (4 low, 5 moderate). No se ejecutó
  `npm audit fix` per las reglas de esta fase (solo instalar el paquete específico).

---

## PARTE 3 — Cambios en tailwind.config.js

- [x] Glob `./Modules/**/resources/assets/js/**/*.jsx` agregado a `content`
- [x] Import de `tailwindcss-animate` (`import animate from "tailwindcss-animate"`) agregado
- [x] Plugin `animate` agregado al array `plugins` junto a `forms`
- [x] `darkMode: ["class"]` agregado al nivel raíz del config
- [x] `colors` shadcn/ui (8 tokens semánticos) agregado a `theme.extend`
- [x] `borderRadius` shadcn/ui (lg/md/sm) agregado a `theme.extend`
- [x] `fontFamily` existente conservado sin modificaciones

### tailwind.config.js después

```js
import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";
import animate from "tailwindcss-animate";

export default {
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
        "./resources/js/**/*.jsx",
        "./Modules/**/resources/assets/js/**/*.jsx",
    ],

    darkMode: ["class"],

    theme: {
        extend: {
            fontFamily: {
                sans: ["Figtree", ...defaultTheme.fontFamily.sans],
            },
            colors: {
                border: "hsl(var(--border))",
                input: "hsl(var(--input))",
                ring: "hsl(var(--ring))",
                background: "hsl(var(--background))",
                foreground: "hsl(var(--foreground))",
                primary: {
                    DEFAULT: "hsl(var(--primary))",
                    foreground: "hsl(var(--primary-foreground))",
                },
                secondary: {
                    DEFAULT: "hsl(var(--secondary))",
                    foreground: "hsl(var(--secondary-foreground))",
                },
                destructive: {
                    DEFAULT: "hsl(var(--destructive))",
                    foreground: "hsl(var(--destructive-foreground))",
                },
                muted: {
                    DEFAULT: "hsl(var(--muted))",
                    foreground: "hsl(var(--muted-foreground))",
                },
                accent: {
                    DEFAULT: "hsl(var(--accent))",
                    foreground: "hsl(var(--accent-foreground))",
                },
                popover: {
                    DEFAULT: "hsl(var(--popover))",
                    foreground: "hsl(var(--popover-foreground))",
                },
                card: {
                    DEFAULT: "hsl(var(--card))",
                    foreground: "hsl(var(--card-foreground))",
                },
            },
            borderRadius: {
                lg: "var(--radius)",
                md: "calc(var(--radius) - 2px)",
                sm: "calc(var(--radius) - 4px)",
            },
        },
    },

    plugins: [forms, animate],
};
```

---

## PARTE 4 — Cambios en app.css

- [x] Bloque `@layer base { :root { ... } }` agregado después de las directivas `@tailwind`
- [x] 14 variables CSS de shadcn/ui presentes (background, foreground, card, card-foreground,
      popover, popover-foreground, primary, primary-foreground, secondary, secondary-foreground,
      muted, muted-foreground, accent, accent-foreground, destructive, destructive-foreground,
      border, input, ring, radius)
- [x] Estilos custom previos conservados (scrollbar: `::-webkit-scrollbar`, `track`, `thumb`)

### app.css después

```css
@tailwind base;
@tailwind components;
@tailwind utilities;

@layer base {
    :root {
        --background: 0 0% 100%;
        --foreground: 222.2 84% 4.9%;
        --card: 0 0% 100%;
        --card-foreground: 222.2 84% 4.9%;
        --popover: 0 0% 100%;
        --popover-foreground: 222.2 84% 4.9%;
        --primary: 222.2 47.4% 11.2%;
        --primary-foreground: 210 40% 98%;
        --secondary: 210 40% 96.1%;
        --secondary-foreground: 222.2 47.4% 11.2%;
        --muted: 210 40% 96.1%;
        --muted-foreground: 215.4 16.3% 46.9%;
        --accent: 210 40% 96.1%;
        --accent-foreground: 222.2 47.4% 11.2%;
        --destructive: 0 84.2% 60.2%;
        --destructive-foreground: 210 40% 98%;
        --border: 214.3 31.8% 91.4%;
        --input: 214.3 31.8% 91.4%;
        --ring: 222.2 84% 4.9%;
        --radius: 0.5rem;
    }
}

/* scrollbar */
::-webkit-scrollbar { width: 6px; cursor: pointer; opacity: 0; ... }
::-webkit-scrollbar-track { background: none; }
::-webkit-scrollbar-thumb { background: #ced4dc; border-radius: 20px; }
```

---

## Verificación final

### npm run build

- Resultado: **éxito**
- Salida: `✓ built in 5.25s`
- Warnings: ninguno relacionado con Tailwind o el config

### php artisan test

- Tests totales: 38
- Tests pasados: 38
- Tests fallidos: 0

---

## Items pendientes (no atacados en esta fase)

Por decisión explícita, los siguientes items identificados en Fase 1 **no se modificaron**:

- **`.npmrc`** con `legacy-peer-deps=true` — decisión humana de no crearlo por ahora.
- **`app.blade.php`** con detección de `::` — la resolución de vistas de módulos ocurre en
  `app.jsx`. El blade actual es coherente con la guía de frontend existente. No es un problema real.
- **`composer.json` autoload-dev** para `Modules\\` — cada módulo gestiona su propio autoload
  vía su `composer.json` local. Los tests funcionan correctamente. No es un problema real.

---

## Commits realizados

1. `327bfcd` — `chore(frontend): instala tailwindcss-animate`
2. `3f74f7e` — `feat(frontend): configura tailwind para soporte de módulos y shadcn/ui`
3. `2f401fb` — `feat(frontend): agrega variables CSS de shadcn/ui en app.css`
4. *(este commit)* — `docs: agrega reporte de Fase 1.5`

---

## Próximo paso

Fase 1.5 completada en **VERDE**. El glob de módulos en Tailwind estaba identificado como
el item más urgente por desbloquear (sin él, los estilos JSX de módulos nuevos serían purgados
en build). Ese bloqueo está resuelto.

Listo para avanzar a **Fase 2** (infraestructura de testing + CI).
