# Checklist de Integración de Módulos

Pasos para integrar un módulo nuevo (desde cero o desde una rama legacy) a `develop`.
Leer [`MODULES_COOKBOOK.md`](MODULES_COOKBOOK.md) y [`TESTING_TEMPLATE.md`](TESTING_TEMPLATE.md) antes de comenzar.

---

## 1. Pre-requisitos

Antes de iniciar la integración, verificar:

```
[ ] La rama develop está limpia
    git status  →  "nothing to commit"

[ ] Los tests actuales pasan
    php artisan test  →  todos verdes

[ ] Cookbook leído
    docs/MODULES_COOKBOOK.md

[ ] Infraestructura compartida verificada
    Ver tabla en MODULES_COOKBOOK.md sección 6
```

---

## 2. Pasos de integración

```
[ ] 1. Crear rama nueva desde develop
       git checkout develop
       git pull
       git checkout -b modularize/<nombre>

[ ] 2. Generar esqueleto del módulo
       php artisan module:make <Nombre>

[ ] 3. Verificar estructura creada
       ls Modules/<Nombre>/
       Debe coincidir con la estructura estándar del cookbook (sección 2)

[ ] 4. Copiar código desde la rama legacy (no hacer merge de la rama)
       Mover archivos manualmente:
       - Controllers  →  Modules/<Nombre>/app/Http/Controllers/
       - Models       →  Modules/<Nombre>/app/Models/
       - Migraciones  →  Modules/<Nombre>/database/migrations/
       - Vistas JSX   →  Modules/<Nombre>/resources/assets/js/Pages/
       - Traducciones →  Modules/<Nombre>/resources/assets/js/i18n/locales/

[ ] 5. Ajustar namespaces PHP (Reglas 5.1 y 5.2 del cookbook)
       App\Http\Controllers\...  →  Modules\<Nombre>\Http\Controllers\...
       App\Models\...            →  Modules\<Nombre>\Models\...
       Verificar casing exacto (case-sensitive en Linux)

[ ] 6. Ajustar imports JSX (Reglas 5.3 y 5.4 del cookbook)
       - Imports del propio módulo: usar paths relativos (./, ../)
       - Imports del core compartido: usar @/
       - Imports de MUI: usar subpath (import Box from "@mui/material/Box")
         NO barrel imports (import { Box } from "@mui/material")

[ ] 7. Registrar rutas del módulo (en Modules/<Nombre>/routes/web.php)
       Aplicar middleware ['auth', 'verified', 'role:<roles>']
       Verificar nombres de rutas con: php artisan route:list --path=<prefijo>

[ ] 8. Inyectar relaciones de User desde el ServiceProvider del módulo
       (ver ARCHITECTURE.md — no editar User.php directamente)
       User::resolveRelationUsing('relacion', fn ($u) => $u->hasMany(Modelo::class));

[ ] 9. Actualizar referencias de Inertia en los controladores
       Inertia::render("<vista>")  →  Inertia::render("<Nombre>::<vista>")

[ ] 10. Agregar seeder del módulo al DatabaseSeeder principal condicionalmente
        (ver ARCHITECTURE.md sección Seeders)

[ ] 11. Crear factories para los modelos del módulo
        Cubrir TODOS los campos NOT NULL de cada migración (Regla 5.11)
        Archivos en: Modules/<Nombre>/database/factories/

[ ] 12. Escribir tests Feature mínimos (al menos 3 por módulo)
        - Smoke test del listado principal
        - Test de autorización (sin rol → 403, sin auth → redirect login)
        - Test del happy path principal (crear o acción central del módulo)
        Usar plantillas de TESTING_TEMPLATE.md

[ ] 13. Agregar la testsuite del módulo a phpunit.xml (Regla 5.9)
        <directory>Modules/<Nombre>/tests/Feature</directory>

[ ] 14. Habilitar el módulo y recargar autoload (Regla 5.5)
        php artisan module:enable <Nombre>
        composer dump-autoload

[ ] 15. Migrar y verificar
        php artisan migrate:fresh --seed
        npm run dev  (verificar que el frontend compila sin errores)

[ ] 16. Correr todos los tests
        php artisan test
        Los tests anteriores + los nuevos deben pasar todos

[ ] 17. Si hay menú lateral, agregar entradas en
        resources/js/_Partials/AsideDrawer/drawerRoutesList.js

[ ] 18. Documentar el módulo (opcional pero recomendado)
        Crear docs/<nombre>-module.md con:
        - Descripción y propósito
        - Modelos y relaciones
        - Rutas expuestas
        - Roles requeridos

[ ] 19. Pull Request a develop
        Título: "feat(<nombre>): integra módulo <Nombre>"
        Descripción del PR debe incluir:
        - Resumen del módulo y su propósito
        - Cantidad de tests agregados
        - Cambios en archivos compartidos (User.php, phpunit.xml, DatabaseSeeder.php, etc.)
        - Items de infraestructura compartida que se modificaron (si aplica)
```

---

## 3. Resolución de problemas comunes

| Síntoma | Causa probable | Solución |
|---------|---------------|----------|
| `ViteManifestNotFoundException` en tests | El test no extiende `Tests\TestCase` | Verificar que la clase extiende `Tests\TestCase` (que ya tiene `withoutVite()`) |
| `Class not found` | Autoload desactualizado | Ejecutar `composer dump-autoload` |
| Las migraciones del módulo no se detectan | Módulo no está activo | Verificar `modules_statuses.json` y ejecutar `php artisan module:enable <Nombre>` |
| Estilos Tailwind no se aplican en el módulo | Glob de módulos falta en Tailwind | Verificar `tailwind.config.js` — ver tabla de infraestructura en el cookbook |
| `NOT NULL constraint failed` | Factory o `Model::create()` no cubre todos los campos | Revisar la migración y completar los campos NOT NULL |
| `createTheme_default is not a function` | Barrel import de MUI en algún JSX | Cambiar a subpath import (`import Box from "@mui/material/Box"`) |
| `Route not found` en tests | Nombre de ruta incorrecto en el test | Ejecutar `php artisan route:list --path=<prefijo>` y comparar |
| `Integrity constraint violation: UNIQUE` en tests | `Role::create()` en lugar de `Role::firstOrCreate()` | Cambiar a `Role::firstOrCreate(['name' => '...', 'guard_name' => 'web'])` |
| Tests de autorización pasan cuando no deberían | Middleware de rol no aplicado en la ruta | Verificar el `middleware(['role:...'])` en `routes/web.php` del módulo |

---

## 4. Después del merge a develop

Una vez mergeado el PR, documentar en el cookbook si hubo:

```
[ ] Reglas nuevas descubiertas durante la integración
    → Agregar a MODULES_COOKBOOK.md sección 5

[ ] Bugs en archivos compartidos que requirieron ajustes
    → Documentar en el reporte de la fase correspondiente

[ ] Paquetes nuevos agregados a package.json o composer.json
    → Documentar en MODULES_COOKBOOK.md sección 7 (Pendientes)

[ ] Items de infraestructura compartida que se modificaron
    → Actualizar tabla en MODULES_COOKBOOK.md sección 6
```
