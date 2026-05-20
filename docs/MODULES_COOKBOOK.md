# Manual de Módulos (Cookbook)

Guía para desarrolladores sobre cómo crear y gestionar módulos en Intranet UC.
Módulo de referencia funcional: `AgendaConsejo`.

Para la arquitectura general del proyecto ver [`ARCHITECTURE.md`](ARCHITECTURE.md).
Para reglas de frontend (Inertia, imports JSX, traducciones) ver [`FRONTEND_GUIDE.md`](FRONTEND_GUIDE.md).

---

## 1. Introducción

El proyecto usa una arquitectura de **Monolito Modular** basada en `nwidart/laravel-modules`. Cada funcionalidad (AgendaConsejo, Employees, Maintenance, Inventory…) vive en su propio módulo dentro de `Modules/`. El core (`app/`) contiene únicamente el modelo `User`, Auth y lógica base compartida.

El módulo `AgendaConsejo` es el primero integrado en `develop` y sirve de referencia para todos los siguientes.

---

## 2. Estructura estándar de un módulo

```
Modules/<Nombre>/
├── app/
│   ├── Http/Controllers/
│   ├── Models/
│   └── Providers/
│       ├── <Nombre>ServiceProvider.php
│       └── RouteServiceProvider.php
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
├── resources/
│   └── assets/js/
│       ├── Pages/          ← vistas React (Inertia)
│       └── i18n/locales/   ← traducciones JSON
├── routes/
│   └── web.php
├── tests/
│   ├── Feature/
│   └── Unit/
└── module.json
```

---

## 3. Crear un Nuevo Módulo

1. **Generar estructura:**
   ```bash
   php artisan module:make <Nombre>
   ```

2. **Limpiar configuración:**
   Hemos desactivado la generación automática de carpetas `views` y `lang` en `config/modules.php`. Revisar el `ServiceProvider` generado y eliminar referencias a `$this->registerViews()` si usas Inertia.

3. **Acomodar estructura de carpetas:**
   Verificar que la estructura generada coincida con la descrita arriba. Si no, ajustar manualmente.

4. **Ejecutar `composer dump-autoload`**

5. **Al referenciar vistas:**
   En los controladores usar la sintaxis `<Modulo>::<Pagina>`:
   ```php
   return Inertia::render('AgendaConsejo::Agendas/Index', [...]);
   ```
   Ver [`FRONTEND_GUIDE.md`](FRONTEND_GUIDE.md) para detalles de resolución de vistas en módulos.

6. **Al crear seeders:**
   Agregar la llamada del seeder al seeder principal condicionalmente. Ver [`ARCHITECTURE.md`](ARCHITECTURE.md) sección Seeders.

---

## 4. Adaptar una Funcionalidad Antigua a un Módulo

Si necesitas mover código de `app/` o de una rama legacy a `Modules/<Nombre>/app/`:

1. **Mover Archivos:**
   - Controladores → `Modules/<Nombre>/app/Http/Controllers/`
   - Modelos → `Modules/<Nombre>/app/Models/`
   - Migraciones → `Modules/<Nombre>/database/migrations/`
   - Vistas JSX → `Modules/<Nombre>/resources/assets/js/Pages/`
   - Traducciones → `Modules/<Nombre>/resources/assets/js/i18n/locales/`

2. **Actualizar Namespaces PHP:** (ver Regla 3.1 y 3.2)
   Cambia `App\...` por `Modules\<Nombre>\...` en todos los archivos movidos.

3. **Actualizar Referencias Inertia:**
   En los controladores cambia `Inertia::render("<vista>")` a `Inertia::render("<Modulo>::<vista>")`.

4. **Ajustar imports JSX:** (ver Regla 3.3 y 3.4)

5. **Inyectar Relaciones:**
   No edites `User.php`. Define las relaciones en el `boot()` del `ServiceProvider` del módulo. Ver [`ARCHITECTURE.md`](ARCHITECTURE.md) sección Desacoplamiento.

6. **Mover Rutas:**
   De `routes/web.php` a `Modules/<Nombre>/routes/web.php`.

7. **Crear factories** cubriendo TODOS los campos NOT NULL (Regla 3.11).

8. **Escribir tests Feature mínimos** (ver [`TESTING_TEMPLATE.md`](TESTING_TEMPLATE.md)).

9. **Habilitar el módulo** (Regla 3.5) y correr `php artisan test`.

---

## 5. Reglas Duras (no negociables)

Reglas aprendidas durante la integración de AgendaConsejo. Cada una indica el problema que evita.

### Regla 5.1 — Imports con casing correcto

PHP en Linux es case-sensitive. El namespace debe coincidir exactamente con la ruta del archivo.

```php
// CORRECTO
use App\Models\User;

// INCORRECTO — rompe en producción (Linux)
use app\Models\User;
```

### Regla 5.2 — Namespace de modelos en módulos

El autoload del módulo mapea `Modules\<Nombre>\` → `app/`, por lo tanto los modelos quedan en:

```php
// CORRECTO en este proyecto
use Modules\AgendaConsejo\Models\Agenda;

// Corresponde al archivo:
// Modules/AgendaConsejo/app/Models/Agenda.php
```

### Regla 5.3 — Imports relativos dentro del módulo (frontend)

Ver [`FRONTEND_GUIDE.md`](FRONTEND_GUIDE.md). Resumen: dentro de los JSX de un módulo, los imports entre componentes del mismo módulo deben ser **relativos**:

```jsx
// CORRECTO
import Table from "./components/Table";

// INCORRECTO — rompe cuando el código vive en Modules/
import Table from "@/Pages/AgendaConsejo/components/Table";
```

El alias `@/` solo se usa para recursos compartidos del core (`resources/js/`).

### Regla 5.4 — Imports de MUI: solo subpath

Ver [`FRONTEND_GUIDE.md`](FRONTEND_GUIDE.md). Resumen:

```jsx
// CORRECTO
import Box from "@mui/material/Box";
import Button from "@mui/material/Button";

// INCORRECTO — causa "createTheme_default is not a function"
import { Box, Button } from "@mui/material";
```

### Regla 5.5 — Activación del módulo

```bash
php artisan module:enable <Nombre>
composer dump-autoload
```

El módulo no carga sus ServiceProviders, rutas ni migraciones si no está en `modules_statuses.json`.

### Regla 5.6 — Migraciones: nombre correcto en down()

El nombre de la tabla en `Schema::dropIfExists()` debe coincidir exactamente con el creado en `up()`:

```php
// Si up() crea 'agenda_point_voting_option', down() debe borrar eso mismo:
public function down(): void
{
    Schema::dropIfExists('agenda_point_voting_option');
}
```

### Regla 5.7 — En desarrollo, usar migrate:fresh

```bash
php artisan migrate:fresh
# o con seeders:
php artisan migrate:fresh --seed
```

Preferir `migrate:fresh` sobre `migrate:reset` porque es más robusto frente a bugs en los métodos `down()`.

### Regla 5.8 — Tests dentro del módulo

Los tests Feature van en `Modules/<Nombre>/tests/Feature/`. Deben:

- Extender `Tests\TestCase` (no `Illuminate\Foundation\Testing\TestCase` directamente)
- Usar el trait `RefreshDatabase`
- Crear roles en el `setUp()` con `Role::firstOrCreate(['name' => 'rol', 'guard_name' => 'web'])`
- Usar `User::factory()` para crear usuarios
- Limpiar la caché de permisos de Spatie al inicio del `setUp()`:
  ```php
  app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
  ```

Ver plantillas completas en [`TESTING_TEMPLATE.md`](TESTING_TEMPLATE.md).

### Regla 5.9 — phpunit.xml debe incluir tests de módulos

```xml
<testsuites>
    <testsuite name="Unit">
        <directory>tests/Unit</directory>
    </testsuite>
    <testsuite name="Feature">
        <directory>tests/Feature</directory>
        <directory>Modules/AgendaConsejo/tests/Feature</directory>
        <!-- Agregar una línea por módulo al integrarlo -->
    </testsuite>
</testsuites>
```

**Nota:** `Modules/*/tests/Feature` (glob) no es compatible con PHPUnit 10/11; agregar cada módulo explícitamente.

### Regla 5.10 — withoutVite en el TestCase base

`tests/TestCase.php` llama a `$this->withoutVite()` en su `setUp()` para evitar `ViteManifestNotFoundException` cuando los tests renderizan vistas Blade que usan `@vite(...)`. Esta configuración ya está activa en `develop`.

### Regla 5.11 — Factories deben cubrir TODOS los campos NOT NULL

SQLite en memoria (entorno de testing) es estricto: si un factory o un `Model::create()` en un test no provee un campo NOT NULL, falla con `Integrity constraint violation`. Revisar siempre la migración correspondiente antes de escribir el factory.

```php
// Verificar migración antes de escribir el factory:
// $table->foreignId('requested_by_user_id')->constrained('users')  ← NOT NULL
// El factory o el test DEBE proveer ese campo.
```

### Regla 5.12 — PSR-4 autoloading de módulos en composer.json

Para que los tests de módulos sean descubiertos por Composer/PHP, el `composer.json` raíz debe tener los módulos en `autoload-dev`:

```json
"autoload-dev": {
    "psr-4": {
        "Tests\\": "tests/",
        "Modules\\AgendaConsejo\\Tests\\": "Modules/AgendaConsejo/tests/"
    }
}
```

**Estado actual:** el `composer.json` raíz solo tiene `Tests\\`. Cada módulo gestiona su propio autoload en su `composer.json` local. Verificar que `composer dump-autoload` se ejecute al integrar un módulo nuevo.

---

## 6. Infraestructura compartida (estado en develop)

Configuraciones que deben estar presentes para que los módulos funcionen. Verificar su estado antes de integrar un módulo nuevo.

| Configuración | Archivo | Estado |
|---|---|---|
| `withoutVite()` en TestCase | `tests/TestCase.php` | ✅ OK |
| Testsuite de módulos en phpunit | `phpunit.xml` | ✅ OK (AgendaConsejo) |
| SQLite en memoria en tests | `phpunit.xml` | ✅ OK |
| AgendaConsejo activo | `modules_statuses.json` | ✅ OK |
| Detección `::` en app.blade.php | `resources/views/app.blade.php` | ⚠️ PENDIENTE |
| Glob de módulos en Tailwind | `tailwind.config.js` | ⚠️ PENDIENTE |
| Plugin `tailwindcss-animate` | `tailwind.config.js` | ⚠️ PENDIENTE |
| `.npmrc` con `legacy-peer-deps` | `.npmrc` | ⚠️ PENDIENTE (archivo no existe) |
| Variables CSS shadcn/ui | `resources/css/app.css` | ⚠️ PENDIENTE |
| `Modules\\` en `autoload-dev` | `composer.json` | ⚠️ PENDIENTE |

Los items marcados ⚠️ fueron documentados en el reporte de Fase 1. Consultar a un humano antes de agregarlos.

---

## 7. Pendientes documentados (deuda técnica conocida)

- **Pin de `@radix-ui/react-context-menu@2.1.5`**: restricción de compatibilidad con React 18 / MUI v5. Se libera en Fase 5 (post-entrega).
- **Doc-comments `/** @test */` deprecados en PHPUnit 11**: PHPUnit emite warnings. Migrar a atributos `#[Test]` cuando se actualice a PHPUnit 12 / Laravel 12 (Fase 4 del ROADMAP).
- **`AgendaPointFactory` no existe**: los tests actuales crean `AgendaPoint` con `::create()` directo. Crear factory en Fase 2.
- **Items de infraestructura compartida pendientes**: ver tabla de la Sección 6.

---

## 8. Referencia rápida de comandos

```bash
# Gestión de módulos
php artisan module:make <Nombre>
php artisan module:enable <Nombre>
php artisan module:disable <Nombre>
php artisan module:list
php artisan module:migrate <Nombre>
php artisan module:seed <Nombre>
php artisan module:publish

# Base de datos
php artisan migrate:fresh
php artisan migrate:fresh --seed

# Tests
php artisan test
php artisan test --filter=<Nombre>

# Autoload
composer dump-autoload
```
