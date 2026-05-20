# Reporte de Estado: Intranet UC

**Fecha de generación:** 2026-05-19
**Rama analizada:** `develop`
**Commit HEAD:** `68d4d9e`

---

## 1. Resumen del Proyecto

**Intranet UC** es una aplicación web interna para uso universitario (proyecto de servicio comunitario). Sirve como plataforma centralizada para la gestión administrativa de la institución, incluyendo:

- Autenticación y gestión de usuarios con roles y permisos granulares.
- Administración de documentos/oficios institucionales.
- Gestión de Consejos de Departamento (módulo AgendaConsejo): creación de agendas, puntos de discusión, sistema de votación con opciones configurables, comentarios y notificaciones automáticas.
- Módulos en desarrollo paralelo: Tesistas, Empleados, Inventario y Mantenimiento de equipos.

### Versiones principales
| Componente | Versión |
|---|---|
| Laravel | `^11.0` (estable) |
| PHP requerido | `^8.2` |
| PHP detectado (entorno local) | En producción: verificar |
| Node.js / npm | Compatible con Vite 5 |

### Estado general de salud: **AMARILLO**

El proyecto tiene una base técnica sólida y un módulo completamente funcional (AgendaConsejo). Sin embargo, existen deudas técnicas identificadas en el ROADMAP, el módulo Thesis está vacío en la rama `develop`, y varias ramas de funcionalidad relevantes no han sido integradas.

---

## 2. Stack Tecnológico

### Backend
| Paquete | Versión | Rol |
|---|---|---|
| `laravel/framework` | `^11.0` | Framework base |
| `inertiajs/inertia-laravel` | `^1.0` | Puente servidor-cliente con React |
| `laravel/sanctum` | `^4.0` | Autenticación de API / sesiones SPA |
| `nwidart/laravel-modules` | `^12.0` | Arquitectura modular |
| `spatie/laravel-permission` | `^6.7` | Roles y permisos (RBAC) |
| `tightenco/ziggy` | `^2.0` | Rutas de Laravel accesibles desde JS |
| `wikimedia/composer-merge-plugin` | `^2.1` | Fusión de `composer.json` de módulos |
| `laravel/breeze` | `^2.0` (dev) | Scaffolding de autenticación |
| `phpunit/phpunit` | `^11.0.1` (dev) | Suite de pruebas |

### Frontend
| Componente | Versión | Rol |
|---|---|---|
| React | `^18.3.1` | Framework UI |
| Inertia.js (React) | `^1.0.16` | SPA sin API REST explícita |
| Vite | `^5.0` | Bundler y servidor de desarrollo |
| Tailwind CSS | `^3.4.14` | Framework CSS de utilidades |
| MUI (Material UI) | `^5.15.18` | Componentes UI adicionales |
| i18next + react-i18next | `23.16.4` / `15.1.1` | Internacionalización (i18n) |
| @headlessui/react | `^1.4.2` | Componentes accesibles sin estilos |
| date-fns | `^4.1.0` | Manipulación de fechas |
| Jest + Testing Library | `^29.7.0` | Pruebas unitarias de frontend |

### Base de datos y servicios
- **Motor:** MySQL (configurado en `.env` local con `DB_CONNECTION=mysql`)
- **Cola de trabajos:** `QUEUE_CONNECTION=database`
- **Caché:** `CACHE_STORE=database`
- **Sesiones:** `SESSION_DRIVER=database`
- **Correo:** `MAIL_MAILER=log` (solo en desarrollo; no hay servicio SMTP configurado)
- **Autenticación:** Laravel Breeze + Sanctum + Spatie Permission

### Herramienta de build
- **Vite** con `laravel-vite-plugin` y `@vitejs/plugin-react`
- Plugin adicional `vite-plugin-static-copy` para copiar archivos de traducción (`locales/`) de cada módulo al directorio público en tiempo de build.

---

## 3. Arquitectura Actual

### 3.1 Visión general

La aplicación sigue una arquitectura de **Monolito Modular** usando `nwidart/laravel-modules`. El directorio `app/` actúa como **Core** (autenticación, usuarios, modelos globales) y `Modules/` contiene funcionalidades independientes.

**Principio de desacoplamiento:** Los módulos inyectan sus relaciones sobre `App\Models\User` a través del `boot()` de su `ServiceProvider` via `resolveRelationUsing()`, evitando que el Core dependa directamente de los módulos.

### 3.2 Módulos registrados

| Módulo | Estado (`modules_statuses.json`) | Descripción |
|---|---|---|
| `AgendaConsejo` | **Habilitado (`true`)** | Gestión de Consejos de Departamento con votación y notificaciones |
| `Thesis` | **No registrado** (directorio vacío) | Estructura de carpetas creada pero sin contenido en `develop` |

### 3.3 Controladores del Core (`app/Http/Controllers/`)

| Controlador | Descripción |
|---|---|
| `ProfileController` | Edición y eliminación del perfil del usuario autenticado |
| `UserController` | CRUD de usuarios con asignación de roles |
| `RoleController` | CRUD de roles con sincronización de permisos |
| `PermissionController` | CRUD de permisos con campo `description` personalizado |
| `DocumentController` | Visualización y edición de documentos/oficios institucionales (sin `store` ni `destroy`) |
| `Auth/AuthenticatedSessionController` | Login / Logout |
| `Auth/RegisteredUserController` | Registro de nuevos usuarios |
| `Auth/PasswordResetLinkController` | Solicitud de restablecimiento de contraseña |
| `Auth/NewPasswordController` | Procesamiento del nuevo password |
| `Auth/ConfirmablePasswordController` | Confirmación de contraseña para acciones críticas |
| `Auth/EmailVerificationPromptController` | Pantalla de verificación de email |
| `Auth/VerifyEmailController` | Procesamiento del enlace de verificación |
| `Auth/EmailVerificationNotificationController` | Reenvío del email de verificación |
| `Auth/PasswordController` | Actualización de contraseña desde perfil |

### 3.4 Controladores del Módulo AgendaConsejo (`Modules/AgendaConsejo/app/Http/Controllers/`)

| Controlador | Descripción |
|---|---|
| `AgendaController` | CRUD completo de Consejos; incluye `close()` para cerrar consejos y filtrado por rol del usuario |
| `AgendaPointController` | Creación, edición y eliminación de puntos de agenda; incluye `addConclusion()` |
| `VoteController` | Registro (`store`) y eliminación (`destroy`) de votos por punto |
| `CommentController` | Almacenamiento de comentarios en puntos de agenda |
| `Settings/VotingOptionController` | CRUD de opciones de votación globales con manejo de restricción referencial |

### 3.5 Modelos

#### Core (`app/Models/`)

| Modelo | Relaciones |
|---|---|
| `User` | `hasMany(Document)` + relaciones inyectadas por módulos vía `resolveRelationUsing` |
| `Document` | `belongsTo(User)` x2: `directed_to` y `applicant` |

#### Módulo AgendaConsejo (`Modules/AgendaConsejo/app/Models/`)

| Modelo | Relaciones |
|---|---|
| `Agenda` | `belongsTo(User)` como `director`; `belongsToMany(User)` como `participants`; `hasMany(AgendaPoint)` |
| `AgendaPoint` | `belongsTo(Agenda)`; `hasMany(Vote)`; `hasMany(Comment)`; `belongsToMany(User)` como `votableUsers`; `belongsToMany(VotingOption)` |
| `Vote` | `belongsTo(AgendaPoint)`; `belongsTo(User)`; `belongsTo(VotingOption)` |
| `VotingOption` | `belongsToMany(AgendaPoint)` (implícito) |
| `Comment` | `belongsTo(AgendaPoint)`; `belongsTo(User)` |

### 3.6 Migraciones

#### Core (`database/migrations/`)

| Migración | Tabla(s) |
|---|---|
| `0001_01_01_000000` | `users`, `password_reset_tokens`, `sessions` |
| `0001_01_01_000001` | `cache`, `cache_locks` |
| `0001_01_01_000002` | `jobs`, `job_batches`, `failed_jobs` |
| `2024_05_09` | Tablas de permisos de Spatie |
| `2024_10_27` | `documents` |
| `2025_01_05` | `document_responses` |
| `2025_01_06` | Columnas adicionales en `documents` |
| `2025_11_09` | `notifications` |

#### Módulo AgendaConsejo (`Modules/AgendaConsejo/database/migrations/`)

| Migración | Tabla(s) |
|---|---|
| `2025_08_05_002232` | `agendas` |
| `2025_08_05_002302` | `agenda_points` |
| `2025_08_05_002410` | `voting_options` |
| `2025_08_05_002411` | `votes` |
| `2025_08_05_002918` | `agenda_user` (pivote) |
| `2025_08_07_214436` | `point_voting_option` (pivote) |
| `2025_08_07_214708` | `point_user` (pivote votantes) |
| `2025_08_08_031044` | Columna `closure_notification_sent` en `agendas` |
| `2025_10_27_232508` | `comments` |

### 3.7 Grupos de rutas

#### `routes/web.php` (Core)

| Grupo / Middleware | Rutas |
|---|---|
| Publico | `GET /` — página de bienvenida |
| `auth`, `verified` | `GET /dashboard` |
| `auth` | `GET/PATCH/DELETE /profile` |
| `auth`, `verified` | `GET /hola` (ruta de prueba/desarrollo) |
| `auth`, `verified` | CRUD `/admin/role` (roles) |
| `auth`, `verified` | CRUD `/admin/permission` (permisos) |
| `auth`, `verified` | CRUD `/admin/user` (usuarios) |
| `auth`, `verified` | CRUD `/document` (documentos) — sin `store` ni `destroy` |

#### `routes/auth.php` (Core)

Rutas estándar de Breeze: login, register, forgot-password, reset-password, verify-email, confirm-password, logout.

#### `Modules/AgendaConsejo/routes/web.php`

| Grupo / Middleware | Rutas |
|---|---|
| `auth`, `verified`, `role:director` | CRUD `/agendas`; CRUD `/agendas.points`; CRUD `/settings/voting-options`; `PUT /agendas/{agenda}/close`; `PATCH /points/{point}/conclusion` |
| `auth`, `verified`, `role:director|counselor` | `GET /agendas`; `GET /agendas/{agenda}`; `POST /points/{point}/comments` |
| `auth`, `verified`, `role:counselor` | `POST /points/{point}/votes`; `DELETE /votes/{vote}` |

---

## 4. Mapa de Ramas

| Rama | Estado | Contenido |
|---|---|---|
| `main` | **Rama principal / producción** | Base estable; commit `b2143ed`. Sin los módulos de funcionalidad. |
| `develop` | **Rama de integración activa** | Contiene el módulo `AgendaConsejo` completamente integrado y modularizado. Es la rama más avanzada. |
| `feature/AgendaConsejo` (remota) | Archivable | Trabajo anterior del módulo de Agenda antes de modularizar; ya integrado en `develop`. |
| `thesis-module` (local y remota) | **Trabajo en progreso — NO integrado** | Contiene controladores Thesis completos en `app/Http/Controllers/Thesis/` y modelos `Thesis*` en `app/Models/`. Sin convertir a módulo nwidart. |
| `employees` (local y remota) | **Trabajo en progreso — NO integrado** | Contiene controladores de empleados en `app/Http/Controllers/Employees/` y modelos en `app/Models/Employees/`. Sin convertir a módulo nwidart. |
| `maintenance_module` (remota) | **Trabajo en progreso — NO integrado** | Controladores de equipos y solicitudes de mantenimiento en `app/Http/Controllers/`. Sin estructura modular. |
| `add/inventario` (remota) | **Trabajo en progreso — NO integrado** | Controladores de inventario en `app/Http/Controllers/`. Sin estructura modular. |
| `add/i18nLocalitation` (remota) | Parcialmente integrado | Mejoras al sistema de traducción i18n; algunas partes ya en `develop`. |
| `add-document-module` (local y remota) | Archivable / Revertido | Intento de módulo de documentos que fue revertido (`revert-16-add-document-module`). |
| `addDocumentModel` (remota) | Archivable | Trabajo antiguo de modelo de documentos; probablemente ya integrado. |

**Rama más actualizada:** `develop` (68d4d9e) — adelantada respecto a `main`.

---

## 5. Evaluación de Actualización de Laravel

### Estado actual vs. estable

| | Versión |
|---|---|
| Laravel instalado | **11.x** (^11.0) |
| Laravel más reciente estable (a mayo 2026) | **12.x** |
| PHP requerido actualmente | ^8.2 |
| PHP requerido por Laravel 12 | ^8.2 |

### Cambios relevantes de Laravel 11 → 12

- Laravel 12 fue liberado en febrero 2025. Los cambios de ruptura entre L11 y L12 son mínimos comparados con versiones anteriores.
- El directorio `bootstrap/app.php` y `bootstrap/providers.php` ya usan la estructura introducida en L11, lo que facilita la migración.
- Revisar compatibilidad de paquetes clave:
  - `nwidart/laravel-modules ^12.0`: Verifica si hay versión compatible con L12.
  - `spatie/laravel-permission ^6.7`: Compatible con Laravel 12.
  - `inertiajs/inertia-laravel ^1.0`: Compatible con Laravel 12.
  - `laravel/sanctum ^4.0`: Compatible con Laravel 12.

### Complejidad estimada de la actualización: **BAJA**

El stack actual (L11 + PHP 8.2) es muy reciente. La transición a L12 se reduce principalmente a ejecutar `composer update` y revisar el CHANGELOG de cada paquete. No se detectan patrones de código deprecated que requieran refactorización masiva.

---

## 6. Evaluación de nwidart/laravel-modules

### Estado: **INSTALADO Y PARCIALMENTE IMPLEMENTADO**

| Criterio | Estado |
|---|---|
| Paquete instalado | Si — `nwidart/laravel-modules ^12.0` |
| `modules_statuses.json` presente | Si |
| `config/modules.php` presente | Si |
| `wikimedia/composer-merge-plugin` configurado | Si — fusiona `Modules/*/composer.json` |
| Módulo funcional en `develop` | **AgendaConsejo** — completamente implementado |
| Módulo vacío en `develop` | **Thesis** — solo estructura de carpetas, sin código |

### Módulos existentes

**AgendaConsejo** (estado: habilitado)
- ServiceProvider registrado y funcional.
- Migraciones propias en `Modules/AgendaConsejo/database/migrations/`.
- Vistas React en `Modules/AgendaConsejo/resources/assets/js/Pages/`.
- Traducciones i18n en `Modules/AgendaConsejo/resources/assets/js/i18n/`.
- Sistema de eventos (`VoteCast`) y notificaciones (`NewAgendaAssigned`, `AllPointsReadyForClosure`).
- Tests en `Modules/AgendaConsejo/tests/Feature/` y `Unit/` (directorio existe; contenido pendiente de verificar).

**Thesis** (estado: no registrado en `modules_statuses.json`)
- Directorio presente en `Modules/Thesis/` con estructura de carpetas creada.
- Sin archivos PHP, migraciones ni rutas.
- El código real existe en la rama `thesis-module` bajo `app/Http/Controllers/Thesis/` y `app/Models/` — aún no fue modularizado.

### Módulos que deberían crearse (basado en ramas activas)

| Módulo propuesto | Basado en rama | Controladores identificados |
|---|---|---|
| `Thesis` | `thesis-module` | `ThesisController`, `ThesisStudentController`, `ThesisTeacherController`, `ThesisFileController`, `StudentStatusesController`, `GanttChartController` |
| `Employees` | `employees` | `EmployeeController`, `StaffController`, `StaffTypeController`, `BenefitController`, `TeachingLevelController`, `EmployeeBenefitHistoryController` |
| `Maintenance` | `maintenance_module` | `EquipmentController`, `EquipmentCategoryController`, `maintenanceRequestController`, `MaintenanceStageController` |
| `Inventory` | `add/inventario` | `ItemController`, `ItemCategoryController`, `ItemStatusController`, `LocationController`, `MovementTypeController`, `InventoryMovementController` |

### Plan de migración sugerido (por módulo)

Para cada módulo a integrar, seguir el proceso documentado en `docs/MODULES_COOKBOOK.md`:

1. Crear estructura: `php artisan module:make NombreModulo`
2. Mover controladores a `Modules/NombreModulo/app/Http/Controllers/`
3. Mover modelos a `Modules/NombreModulo/app/Models/`
4. Mover migraciones a `Modules/NombreModulo/database/migrations/`
5. Actualizar namespaces de `App\...` a `Modules\NombreModulo\...`
6. Inyectar relaciones en `User` desde el `ServiceProvider` del módulo
7. Mover rutas al archivo de rutas del módulo
8. Actualizar llamadas `Inertia::render()` con sintaxis `Modulo::Vista`
9. Mover vistas React y archivos i18n a `resources/assets/`
10. Agregar seeder condicional en `DatabaseSeeder.php`

---

## 7. Problemas Conocidos

### Críticos

| # | Problema | Ubicación | Severidad |
|---|---|---|---|
| C1 | **Import con casing incorrecto:** `use app\Models\User` (minúscula) en `Agenda.php` y `AgendaPoint.php` y `Vote.php`. En Linux/sistemas case-sensitive esto causará un error fatal (`Class not found`). En Windows (desarrollo actual) funciona pero fallará en producción en servidor Linux. | `Modules/AgendaConsejo/app/Models/Agenda.php:11`, `AgendaPoint.php:11`, `Vote.php:9` | **CRITICO** |
| C2 | **Módulo Thesis vacío en `develop`:** El directorio `Modules/Thesis/` existe en `develop` pero está completamente vacío. Si se intenta habilitar este módulo producirá errores. El código real está en la rama `thesis-module` y no ha sido modularizado. | `Modules/Thesis/` | **CRITICO** |

### Altos

| # | Problema | Ubicación | Severidad |
|---|---|---|---|
| A1 | **Bug en revocación de roles:** El ROADMAP documenta que existe un bug que impide revocar/quitar roles a usuarios desde la interfaz de administración. El método `update` de `UserController` llama a `assignRole()` pero nunca revoca los roles anteriores con `syncRoles()`. | `app/Http/Controllers/UserController.php:91` | **ALTO** |
| A2 | **`DocumentController` incompleto:** El controlador omite los métodos `store` y `destroy`, pero la ruta resource está registrada (aunque con `->only([...])`). No es un error de routing, pero la funcionalidad de creación y eliminación de documentos no existe. | `app/Http/Controllers/DocumentController.php` | **ALTO** |
| A3 | **Cuatro ramas de funcionalidad sin integrar:** `thesis-module`, `employees`, `maintenance_module`, `add/inventario` contienen trabajo significativo (decenas de commits cada una) que no ha sido fusionado en `develop` ni convertido al patrón de módulos nwidart. | Ramas remotas | **ALTO** |

### Medios

| # | Problema | Ubicación | Severidad |
|---|---|---|---|
| M1 | **Pruebas de módulo vacías:** El directorio `Modules/AgendaConsejo/tests/Feature/` existe pero estaba vacío al momento del análisis. No hay tests automatizados que validen la lógica del módulo principal. | `Modules/AgendaConsejo/tests/` | **MEDIO** |
| M2 | **SQLite desactivado en pruebas:** En `phpunit.xml`, las líneas de `DB_CONNECTION=sqlite` e `DB_DATABASE=:memory:` están comentadas. Las pruebas corren contra la base de datos real (MySQL local), lo que es riesgoso y lento. | `phpunit.xml:25-26` | **MEDIO** |
| M3 | **Contraseña no hasheada en `UserController`:** Al actualizar usuario, el password se asigna directamente al atributo sin forzar el hash. Aunque el modelo tiene el cast `'password' => 'hashed'`, es un patrón frágil si el cast se modifica. | `app/Http/Controllers/UserController.php:86` | **MEDIO** |
| M4 | **Ruta de desarrollo (`/hola`):** Existe una ruta `GET /hola` que renderiza `holaMundo.jsx`. Debe eliminarse antes de producción. | `routes/web.php:33` | **MEDIO** |
| M5 | **Traducción i18n con claves de pago/billing:** Los archivos de traducción `es/translation.json` contienen decenas de claves relacionadas con "métodos de pago", "suscripciones" y "facturación" que parecen ser residuos del scaffolding inicial y no corresponden al dominio universitario. | `resources/js/i18n/locales/es/translation.json` | **MEDIO** |
| M6 | **`ARCHITECTURE.md` con contenido truncado:** La sección de "Comunicación entre módulos" termina con la frase incompleta `"- Servi"`. | `docs/ARCHITECTURE.md:52` | **MEDIO** |

### Bajos

| # | Problema | Ubicación | Severidad |
|---|---|---|---|
| B1 | **`.env.example` ausente del repositorio:** Solo existe `.env` (que contiene configuración local y la clave de aplicación). No se encontró `.env.example` en el working tree de `develop`. Esto dificulta la instalación en nuevos entornos. | Raíz del proyecto | **BAJO** |
| B2 | **`modules_statuses.json` no registra Thesis:** El módulo Thesis existe como directorio pero no está en `modules_statuses.json`, lo que puede causar inconsistencias si se activa manualmente. | `modules_statuses.json` | **BAJO** |
| B3 | **Seeders de permisos sin limpiar caché:** El ROADMAP identifica que los seeders no ejecutan `forgetCachedPermissions()` antes de crear datos, lo que puede causar comportamientos inesperados en entornos con caché activa. | `database/seeders/` | **BAJO** |

---

## 8. Próximos Pasos Recomendados (por prioridad)

### Prioridad 1 — Correcciones críticas (hacer antes de cualquier otra cosa)

1. **Corregir imports en modelos de AgendaConsejo:**
   En `Modules/AgendaConsejo/app/Models/Agenda.php`, `AgendaPoint.php` y `Vote.php`, cambiar:
   ```php
   use app\Models\User;  // INCORRECTO
   ```
   por:
   ```php
   use App\Models\User;  // CORRECTO
   ```
   Esto es un error fatal en producción (servidor Linux).

2. **Crear `.env.example`:**
   Crear un archivo `.env.example` limpio (sin `APP_KEY` real ni contraseñas) para documentar todas las variables necesarias, especialmente `MAIL_*` y `DB_*`.

3. **Corregir el bug de revocación de roles:**
   En `UserController::update()` y `UserController::store()`, reemplazar `assignRole()` por `syncRoles()` para que al editar un usuario se reemplacen correctamente sus roles:
   ```php
   $user->syncRoles($request->roles);
   ```

### Prioridad 2 — Deuda técnica activa

4. **Configurar SQLite para pruebas:**
   Descomentar en `phpunit.xml` las líneas de `DB_CONNECTION=sqlite` y `DB_DATABASE=:memory:` para aislar las pruebas de la base de datos real.

5. **Eliminar ruta de desarrollo `/hola`:**
   Remover la ruta `GET /hola` y el componente `holaMundo.jsx` del proyecto.

6. **Limpiar traducciones residuales:**
   Eliminar las claves de billing/pagos del archivo `resources/js/i18n/locales/es/translation.json`.

7. **Completar `DocumentController`:**
   Implementar los métodos `store` (con validación y subida de archivos si aplica) y `destroy`.

### Prioridad 3 — Integración de ramas (plan modular)

8. **Modularizar `thesis-module` → Módulo `Thesis`:**
   El código ya existe en `app/Http/Controllers/Thesis/` y `app/Models/Thesis*` de la rama `thesis-module`. Seguir el proceso del `MODULES_COOKBOOK.md` para crear el módulo nwidart y hacer merge a `develop`.

9. **Modularizar `employees` → Módulo `Employees`:**
   Similar al anterior. Convertir los controladores y modelos en `Employees/` al patrón de módulos.

10. **Evaluar y modularizar `maintenance_module` → Módulo `Maintenance`:**
    Revisar el estado actual (tiene cron jobs configurados según commits) y convertir al patrón modular.

11. **Evaluar y modularizar `add/inventario` → Módulo `Inventory`:**
    Convertir controladores y modelos de inventario al patrón modular.

### Prioridad 4 — Calidad y preparación para entrega

12. **Escribir pruebas de Feature para AgendaConsejo:**
    Crear tests en `Modules/AgendaConsejo/tests/Feature/` que cubran: creación de agenda, votación, cierre de consejo, restricciones por rol.

13. **Sincronizar `develop` con `main`:**
    Una vez que los bugs críticos estén corregidos y las pruebas pasen, hacer merge de `develop` a `main`.

14. **Completar la documentación de arquitectura:**
    Terminar la sección truncada en `ARCHITECTURE.md` sobre comunicación entre módulos.

15. **Definir y sembrar la matriz completa de roles y permisos:**
    Implementar los seeders limpios con `forgetCachedPermissions()` y la tabla de roles documentada en `DATABASE_ROLES.md`.

---

## 9. Definición de Listo (Definition of Done)

Antes de considerar el proyecto listo para demostración o entrega, deben cumplirse todas las condiciones siguientes:

### Entorno y configuración
- [ ] Existe un `.env.example` completo y funcional en el repositorio.
- [ ] `composer install && php artisan migrate:refresh --seed` ejecuta sin errores en un entorno limpio.
- [ ] `npm install && npm run build` completa sin advertencias de error.
- [ ] La versión de PHP del servidor de producción es `>= 8.2`.
- [ ] Se ha confirmado el motor de base de datos en producción (MySQL `>= 8.0` recomendado).

### Corrección de errores
- [ ] El bug crítico de imports (`use app\Models\User`) está corregido en los tres modelos de AgendaConsejo.
- [ ] El bug de revocación de roles está corregido (`syncRoles` en lugar de `assignRole`).
- [ ] La ruta de desarrollo `/hola` ha sido eliminada.

### Cobertura mínima de pruebas
- [ ] Al menos las rutas protegidas por rol están cubiertas con pruebas Feature (acceso denegado vs permitido).
- [ ] Las pruebas corren contra SQLite en memoria (no la base de datos real).
- [ ] `php artisan test` pasa sin fallos.

### Arquitectura modular
- [ ] La lista de módulos a incluir en la entrega está acordada y documentada.
- [ ] Cada módulo acordado está en `modules_statuses.json` con estado `true`.
- [ ] El Core (`app/`) no tiene controladores pertenecientes a módulos (no deben existir carpetas `app/Http/Controllers/Thesis/`, `app/Http/Controllers/Employees/`, etc. en `develop`).
- [ ] Las relaciones de `User` con módulos están en los `ServiceProvider` de cada módulo, no en `User.php`.

### Seguridad mínima
- [ ] `APP_DEBUG=false` en producción.
- [ ] `APP_KEY` generada en producción con `php artisan key:generate`.
- [ ] El correo está configurado con un servicio real (no `log`) en producción para que funcionen las notificaciones de AgendaConsejo.

---

*Reporte generado mediante análisis estático del repositorio. No se ejecutó la aplicación ni se consultó la base de datos.*
