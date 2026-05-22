# CHANGELOG — Integración Módulo Thesis y correcciones generales

Este documento registra todos los cambios realizados en la rama `develop` durante la integración del módulo `Thesis` (proveniente de la rama `feature/thesis-module`), así como las correcciones y ajustes necesarios para que el ecosistema de módulos nWidart funcione correctamente.

---

## Tabla de contenidos

1. [Nuevo módulo: Thesis](#1-nuevo-módulo-thesis)
2. [Corrección en app.blade.php — renderizado de vistas de módulos](#2-corrección-en-appbladephp--renderizado-de-vistas-de-módulos)
3. [Corrección en tailwind.config.js](#3-corrección-en-tailwindconfigjs)
4. [Nuevas dependencias en package.json](#4-nuevas-dependencias-en-packagejson)
5. [Creación de .npmrc](#5-creación-de-npmrc)
6. [Archivos de shadcn/ui agregados manualmente](#6-archivos-de-shadcnui-agregados-manualmente)
7. [Correcciones de imports en JSX del módulo Thesis](#7-correcciones-de-imports-en-jsx-del-módulo-thesis)
8. [Corrección de imports de MUI (Material UI)](#8-corrección-de-imports-de-mui-material-ui)
9. [Corrección de bug en el componente Gantt](#9-corrección-de-bug-en-el-componente-gantt)
10. [Corrección en migración de AgendaConsejo](#10-corrección-en-migración-de-agendaconsejo)
11. [Entradas del menú lateral para el módulo Thesis](#11-entradas-del-menú-lateral-para-el-módulo-thesis)
12. [Activación del módulo Thesis](#12-activación-del-módulo-thesis)
13. [Comandos a ejecutar tras clonar o actualizar](#13-comandos-a-ejecutar-tras-clonar-o-actualizar)

---

## 1. Nuevo módulo: Thesis

Se creó el módulo `Modules/Thesis/` siguiendo la estructura estándar de [nWidart Laravel Modules](https://nwidart.com/laravel-modules/v6/introduction). Este módulo proviene de la rama `feature/thesis-module` y fue transformado en módulo independiente.

### Estructura del módulo

```
Modules/Thesis/
├── app/
│   ├── Http/Controllers/
│   │   ├── GanttChartController.php
│   │   ├── StudentStatusesController.php
│   │   ├── ThesisController.php
│   │   ├── ThesisFileController.php
│   │   ├── ThesisStudentController.php
│   │   └── ThesisTeacherController.php
│   ├── Models/
│   │   ├── StudentStatus.php
│   │   ├── StudentStatusHistory.php
│   │   ├── Thesis.php
│   │   ├── ThesisFile.php
│   │   ├── ThesisStudent.php
│   │   └── ThesisTeacher.php
│   └── Providers/
│       ├── RouteServiceProvider.php
│       └── ThesisServiceProvider.php
├── database/
│   ├── migrations/
│   │   ├── 2025_06_23_011355_create_student_statuses_table.php
│   │   ├── 2025_06_23_035127_create_thesis_student_table.php
│   │   ├── 2025_06_23_040248_create_thesis_table.php
│   │   ├── 2025_06_23_041043_create_student_thesis_pivot_table.php
│   │   ├── 2025_08_13_083804_create_thesis_files_table.php
│   │   ├── 2025_08_13_090716_add_type_column_to_thesis_files_table.php
│   │   ├── 2025_10_07_142358_create_student_status_history_table.php
│   │   ├── 2025_10_09_114245_create_thesis_teachers_table.php
│   │   └── 2025_10_09_115329_create_teacher_thesis_pivot_table.php
│   └── seeders/
│       ├── StudentStatusSeeder.php
│       └── ThesisDatabaseSeeder.php
├── resources/assets/js/Pages/
│   ├── GanttChart/
│   │   ├── components/GanttChart.jsx
│   │   └── index.jsx
│   ├── StudentStatuses/
│   │   ├── components/{DeleteDialog,Form,Table}.jsx
│   │   └── create.jsx / edit.jsx / index.jsx / show.jsx
│   ├── ThesisProjects/
│   │   ├── components/{DeleteDialog,Form,Table}.jsx
│   │   └── create.jsx / edit.jsx / index.jsx / show.jsx
│   ├── ThesisStudent/
│   │   ├── components/{DeleteDialog,File,Form,Table}.jsx
│   │   └── create.jsx / edit.jsx / index.jsx / show.jsx
│   └── ThesisTeacher/
│       ├── components/{DeleteDialog,Form,Table}.jsx
│       └── Create.jsx / Edit.jsx / Index.jsx / Show.jsx
├── routes/
│   └── web.php
└── module.json
```

### Rutas registradas (`Modules/Thesis/routes/web.php`)

Todas bajo middleware `auth` y `verified`:

| Método | URI | Nombre | Controlador |
|--------|-----|--------|-------------|
| GET/POST/... | `/thesis/thesisStudent` | `thesisStudent.*` | `ThesisStudentController` |
| POST | `/thesis/thesisStudent/import-excel` | `thesisStudent.importExcel` | `ThesisStudentController@importExcel` |
| GET/POST/... | `/thesis/studentStatuses` | `studentStatuses.*` | `StudentStatusesController` |
| GET/POST/... | `/thesis/Thesis` | `Thesis.*` | `ThesisController` |
| GET | `/thesis-files/{thesisFile}/download` | `thesis-files.download` | `ThesisFileController@download` |
| GET | `/thesis/gantt-chart` | `thesis.ganttChart` | `GanttChartController@index` |
| GET/POST/... | `/thesis/thesisTeachers` | `thesisTeacher.*` | `ThesisTeacherController` |

### Seeder de estatus de estudiante

El seeder `StudentStatusSeeder` crea los siguientes estatus por defecto en la tabla `student_statuses`:

- `inscrito` — Estudiante inscrito
- `PTEG inscrito` — El estudiante ha inscrito PTEG
- `TEG inscrito` — El estudiante ha inscrito TEG

Para ejecutarlo:

```bash
php artisan db:seed --class="Modules\Thesis\Database\Seeders\ThesisDatabaseSeeder"
```

---

## 2. Corrección en `app.blade.php` — renderizado de vistas de módulos

**Archivo:** `resources/views/app.blade.php`

### Problema

El archivo original siempre construía un segundo entry point de Vite con el path del componente:

```blade
@vite(['resources/js/app.jsx', "resources/js/Pages/{$page['component']}.jsx"])
```

Para las vistas normales (ej. `Dashboard`) esto funciona correctamente. Pero los módulos de InertiaJS usan la convención `Modulo::Carpeta/Pagina` (con `::` en el nombre del componente). El path resultante sería `resources/js/Pages/Thesis::ThesisStudent/index.jsx`, que no existe, causando un **error 404** al navegar a cualquier ruta del módulo.

### Solución

Se agregó una condición para detectar si el componente pertenece a un módulo (contiene `::`) y en ese caso no agregar el segundo entry point:

```blade
@if(str_contains($page['component'], '::'))
    @vite(['resources/js/app.jsx'])
@else
    @vite(['resources/js/app.jsx', "resources/js/Pages/{$page['component']}.jsx"])
@endif
```

---

## 3. Corrección en `tailwind.config.js`

Se realizaron cuatro cambios al archivo de configuración de Tailwind CSS:

### 3.1 — Glob para archivos JSX de módulos

**Problema:** Tailwind solo escaneaba `resources/js/**/*.jsx`. Los archivos JSX dentro de `Modules/` no eran escaneados, por lo que Tailwind eliminaba (purgaba) todas las clases CSS que solo aparecían en archivos de módulos. El resultado era que los estilos de las vistas del módulo Thesis no se aplicaban.

**Solución:** Se agregó el siguiente glob al array `content`:

```js
"./Modules/**/resources/assets/js/**/*.jsx"
```

### 3.2 — Plugin `tailwindcss-animate`

**Problema:** Los componentes de shadcn/ui (Card, ContextMenu, Gantt) usan utilidades de animación como `animate-in`, `fade-in-0`, `zoom-in-95`, etc., que provee el plugin `tailwindcss-animate`. Sin él, esas clases no existían en el output de Tailwind.

**Solución:** Se importó y registró el plugin:

```js
import animate from "tailwindcss-animate";
// ...
plugins: [forms, animate],
```

### 3.3 — Modo oscuro basado en clase (`darkMode: ["class"]`)

**Problema:** El modo oscuro por defecto en Tailwind es `media`, que aplica los estilos `dark:*` según la preferencia del sistema operativo. Si el SO estaba en modo oscuro, clases como `dark:bg-gray-900` (presente en los labels flotantes de los filtros del Gantt) se aplicaban, dando a los labels un fondo negro en lugar del `bg-white` esperado.

**Solución:** Se configuró el modo oscuro para que solo active cuando existe la clase `dark` en el HTML (lo cual este proyecto no usa actualmente):

```js
darkMode: ["class"],
```

### 3.4 — Colores y border-radius con variables CSS (shadcn/ui)

Se extendió el `theme` con los tokens de diseño que shadcn/ui requiere. Estos tokens usan variables CSS (`hsl(var(--*))`) y son usados por componentes como `Card`, `ContextMenu`, y el Gantt.

> **Importante:** Estas variables CSS deben estar definidas en `resources/css/app.css` para que los componentes tengan los colores correctos. Si no están definidas, los componentes usarán colores transparentes/heredados. Ejemplo de definición para tema claro:

```css
@layer base {
  :root {
    --background: 0 0% 100%;
    --foreground: 222.2 84% 4.9%;
    --card: 0 0% 100%;
    --card-foreground: 222.2 84% 4.9%;
    --radius: 0.5rem;
    /* ... ver documentación de shadcn/ui para el listado completo */
  }
}
```

---

## 4. Nuevas dependencias en `package.json`

Las siguientes dependencias fueron agregadas para soportar el módulo Thesis y sus componentes UI:

### `dependencies` (producción)

| Paquete | Versión | Motivo |
|---------|---------|--------|
| `@dnd-kit/core` | `^6.3.1` | Drag & drop — usado por el componente Gantt de shadcn-io |
| `@dnd-kit/modifiers` | `^9.0.0` | Modificadores de restricción para dnd-kit |
| `@dnd-kit/sortable` | `^10.0.0` | Listas ordenables con dnd-kit |
| `@radix-ui/react-context-menu` | `2.1.5` (**exacto**) | Menú contextual del Gantt — **pinneado a esta versión exacta** (ver nota abajo) |
| `@uidotdev/usehooks` | `^2.4.1` | Hooks de utilidad — usado por el Gantt |
| `class-variance-authority` | `^0.7.1` | Variantes de clases CSS — shadcn/ui |
| `clsx` | `^2.1.1` | Utilidad para combinar clases CSS — shadcn/ui |
| `date-fns` | `^4.1.0` | Manipulación de fechas — filtros del Gantt |
| `frappe-gantt` | `^1.0.3` | Librería base del diagrama Gantt |
| `i18next` | `23.16.4` | Internacionalización |
| `i18next-http-backend` | `2.6.2` | Backend HTTP para i18next |
| `jotai` | `^2.15.0` | Gestión de estado atómico — shadcn-io Gantt |
| `lodash.groupby` | `^4.6.0` | Agrupación de datos — Gantt |
| `lodash.throttle` | `^4.1.1` | Throttle de eventos — Gantt |
| `lucide-react` | `^0.545.0` | Iconos SVG — shadcn/ui |
| `react-i18next` | `15.1.1` | Integración de i18next con React |
| `tailwind-merge` | `^3.3.1` | Merge inteligente de clases Tailwind — función `cn()` de shadcn/ui |
| `tailwindcss-animate` | `^1.0.7` | Animaciones CSS para shadcn/ui |

### `devDependencies`

| Paquete | Versión | Motivo |
|---------|---------|--------|
| `@types/lodash.throttle` | `^4.1.9` | Tipos TypeScript para lodash.throttle |

### Nota importante sobre `@radix-ui/react-context-menu`

La versión `^2.2.x` (última disponible) requiere `@types/react@^19`, pero este proyecto usa **React 18**. Actualizar a React 19 **no es viable** porque MUI v5 (que usa el proyecto) no es compatible con React 19. Por eso se pinnea a la versión exacta `2.1.5`, que es la última compatible con React 18.

**No cambiar esta versión a `^2.2.x` sin antes actualizar también MUI a v6+.**

---

## 5. Creación de `.npmrc`

**Archivo:** `.npmrc` (raíz del proyecto)

**Contenido:**
```
legacy-peer-deps=true
```

**Motivo:** Varias dependencias tienen conflictos de peer deps (especialmente entre versiones de React y `@types/react`). Sin esta opción, `npm install` falla con error `ERESOLVE`. Esta configuración equivale a pasar `--legacy-peer-deps` en cada `npm install` y evita tener que recordarlo manualmente.

---

## 6. Archivos de shadcn/ui agregados manualmente

shadcn/ui **no es un paquete npm normal**. Es una CLI (`npx shadcn@latest add <componente>`) que genera código fuente y lo copia directamente en el proyecto. Estos archivos **deben estar en control de versiones** y **no se recuperan con `npm install`**.

Los siguientes archivos fueron generados originalmente por la CLI de shadcn/ui en la rama `feature/thesis-module` y copiados manualmente a `develop`:

### `resources/js/lib/utils.js`

Función utilitaria `cn()` que combina `clsx` y `tailwind-merge`. Es usada por todos los componentes de shadcn/ui para combinar clases CSS de forma inteligente:

```js
import { clsx } from "clsx";
import { twMerge } from "tailwind-merge";

export function cn(...inputs) {
    return twMerge(clsx(inputs));
}
```

### `resources/js/Components/ui/card.jsx`

Componente `Card` con sus subcomponentes (`CardHeader`, `CardTitle`, `CardDescription`, `CardContent`, `CardFooter`). Usado en el layout de las vistas del módulo Thesis (filtros, diagrama Gantt, etc.).

### `resources/js/Components/ui/context-menu.jsx`

Componente `ContextMenu` basado en `@radix-ui/react-context-menu`. Usado por el diagrama Gantt para el menú contextual que aparece al hacer click derecho sobre las barras.

### `resources/js/Components/ui/shadcn-io/gantt/index.jsx`

Componente Gantt de [shadcn-io](https://shadcn-io.vercel.app/) de ~1253 líneas. Implementa un diagrama de Gantt interactivo con soporte para:
- Drag & drop de barras (`@dnd-kit`)
- Zoom in/out de la vista temporal
- Menú contextual por barra (`@radix-ui/react-context-menu`)
- Estado atómico con `jotai`

Se corrigió un bug en esta misma sesión (ver sección 9).

---

## 7. Correcciones de imports en JSX del módulo Thesis

Los archivos JSX del módulo usaban imports absolutos con el alias `@/` apuntando a `resources/js/Pages/Thesis/...`, que no existe una vez que el código se mueve a `Modules/Thesis/`. Se corrigieron a imports relativos:

| Archivo | Import incorrecto | Import corregido |
|---------|-------------------|-----------------|
| `ThesisStudent/index.jsx` | `@/Pages/Thesis/ThesisStudent/components/Table` | `./components/Table` |
| `ThesisProjects/index.jsx` | `@/Pages/Thesis/ThesisProjects/components/Table` | `./components/Table` |
| `ThesisProjects/edit.jsx` | `@/Pages/Thesis/ThesisProjects/components/Form` | `./components/Form` |
| `StudentStatuses/index.jsx` | `@/Pages/Thesis/StudentStatuses/components/Table` | `./components/Table` |
| `StudentStatuses/edit.jsx` | `@/Pages/Thesis/StudentStatuses/components/Form` | `./components/Form` |

> **Regla general para futuras integraciones de módulos:** Dentro de los módulos, los imports entre componentes del mismo módulo deben ser **relativos** (`./`, `../`). El alias `@/` apunta a `resources/js/` y solo debe usarse para recursos compartidos del proyecto base (layouts, componentes globales, etc.).

---

## 8. Corrección de imports de MUI (Material UI)

**Problema:** Vite en modo desarrollo hace optimización de chunks. Cuando un archivo usa el import "barrel" `from "@mui/material"` y otro usa el import por subpath `from "@mui/material/Box"`, Vite genera chunks incompatibles que causan el siguiente error en runtime:

```
TypeError: createTheme_default is not a function
```

Este error se manifestaba en el login y en cualquier página que cargara componentes MUI.

**Archivos corregidos:**

| Archivo | Antes | Después |
|---------|-------|---------|
| `ThesisTeacher/Create.jsx` | `import { Paper, Typography } from "@mui/material"` | `import Paper from "@mui/material/Paper"` |
| `ThesisProjects/components/DeleteDialog.jsx` | `import { Box } from "@mui/material"` | `import Box from "@mui/material/Box"` |

> **Regla obligatoria para este proyecto:** Siempre usar imports por **subpath** de MUI: `import ComponentName from "@mui/material/ComponentName"`. **Nunca usar el barrel import** `from "@mui/material"`, ya que causa conflictos con el resto del proyecto que usa subpath imports.

---

## 9. Corrección de bug en el componente Gantt

**Archivo:** `resources/js/Components/ui/shadcn-io/gantt/index.jsx`

**Función afectada:** `createDynamicInitialTimelineData` (~línea 990)

**Bug:** Los operadores de la función que calcula el rango de años del timeline estaban invertidos, produciendo siempre un array vacío:

```js
// INCORRECTO — generateRange(earliestYear + 1, latestYear - 1) siempre produce []
// cuando earliestYear == latestYear (caso más común con pocos datos)
return [earliestYear + 1, latestYear - 1];
```

```js
// CORRECTO — genera un rango con al menos 2 años de margen
return [earliestYear - 1, latestYear + 1];
```

**Síntoma en producción:** Al hacer click en los botones del diagrama (zoom, navegación temporal) se producía el error:

```
Cannot read properties of undefined (reading 'year')
```

porque `timelineData[0]` era `undefined` al ser el array vacío. El usuario tenía que hacer click dos veces en los botones para que funcionaran (la segunda vez el estado ya estaba parcialmente inicializado).

---

## 10. Corrección en migración de AgendaConsejo

**Archivo:** `Modules/AgendaConsejo/database/migrations/2025_08_07_214436_create_point_voting_option_table.php`

**Problema:** El método `down()` intentaba eliminar la tabla con el nombre incorrecto `point_voting_option` en lugar de `agenda_point_voting_option`. Esto causaba que `migrate:reset` y `migrate:fresh` fallaran con errores de foreign key al no poder eliminar la tabla correcta, dejando tablas huérfanas en la base de datos.

```php
// ANTES (incorrecto)
Schema::dropIfExists('point_voting_option');

// DESPUÉS (correcto)
Schema::dropIfExists('agenda_point_voting_option');
```

> **Nota sobre migraciones en entorno local:** En desarrollo, preferir siempre `php artisan migrate:fresh` sobre `migrate:reset`. El comando `migrate:fresh` elimina las tablas directamente a nivel de base de datos (sin depender de los métodos `down()`), lo que evita estos fallos en cascada.

---

## 11. Entradas del menú lateral para el módulo Thesis

**Archivo:** `resources/js/_Partials/AsideDrawer/drawerRoutesList.js`

Se agregó la sección "Proyectos PTEG/TEG" al menú lateral de navegación, visible solo para administradores (`permissionNeeded: "isAdmin"`):

```js
{
    permissionNeeded: "isAdmin",
    subHeaderText: "Proyectos PTEG/TEG",
    routes: [
        { linkText: "Tesistas",                     routeName: "thesisStudent.index" },
        { linkText: "Docentes",                     routeName: "thesisTeacher.index" },
        { linkText: "Estatus de estudiantes",       routeName: "studentStatuses.index" },
        { linkText: "Proyectos PTEG/TEG",           routeName: "Thesis.index" },
        { linkText: "Diagrama General de Tesistas", routeName: "thesis.ganttChart" },
    ],
},
```

---

## 12. Activación del módulo Thesis

**Archivo:** `modules_statuses.json`

nWidart registra el estado de cada módulo en este archivo. Un módulo nuevo aparece como **deshabilitado** por defecto, por eso `php artisan migrate` no encontraba las migraciones del módulo aunque los archivos existieran.

Para activarlo se ejecutó:

```bash
php artisan module:enable Thesis
composer dump-autoload
```

El archivo quedó así:

```json
{
    "AgendaConsejo": true,
    "Thesis": true
}
```

> **Importante para futuras integraciones:** Cada vez que se agregue un nuevo módulo nWidart al proyecto, hay que ejecutar `php artisan module:enable NombreModulo` y `composer dump-autoload`. Sin esto, las migraciones, rutas y clases del módulo no serán detectadas por Laravel.

---

## 13. Comandos a ejecutar tras clonar o actualizar

Si otro desarrollador clona el repositorio o hace pull de esta rama, debe ejecutar los siguientes comandos en orden:

```bash
# 1. Instalar dependencias PHP
composer install

# 2. Instalar dependencias JS
#    (.npmrc ya tiene legacy-peer-deps=true, no hace falta el flag manual)
npm install

# 3. Regenerar autoload (necesario cuando hay módulos nuevos)
composer dump-autoload

# 4. Ejecutar migraciones
php artisan migrate

# 5. Poblar la tabla de estatus de estudiantes
php artisan db:seed --class="Modules\Thesis\Database\Seeders\ThesisDatabaseSeeder"

# 6. Compilar assets (desarrollo)
npm run dev
```

> **Nota sobre `npm install`:** Este proyecto tiene `.npmrc` con `legacy-peer-deps=true`, por lo que `npm install` debería funcionar sin flags adicionales. Si por alguna razón falla con error `ERESOLVE`, agregar `--legacy-peer-deps` manualmente.

> **Nota sobre migraciones:** En entornos locales, si hay conflictos con migraciones existentes (tablas huérfanas, foreign keys rotas), usar `php artisan migrate:fresh --seed` en lugar de `migrate:reset`.

---

*Documento generado durante la sesión de integración del módulo Thesis en la rama `develop`.*
