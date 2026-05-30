# Reporte de Fase 3.4 — Integración del módulo Inventory

**Fecha:** 2026-05-30
**Rama:** develop
**Commit HEAD antes de iniciar:** efa7c68 (Update composer y package-lock)
**Rama fuente del código legacy:** origin/add/inventario
**Autores del legacy:** OdooCoder3421 (principal) + A.L.I.C.E (commit reciente)

---

## Inventario descubierto en PARTE 1

Generado a partir de `git ls-tree -r origin/add/inventario` y filtrado por los
patrones `Inventory`, `Item`, `ItemCategory`, `ItemStatus`, `Location`,
`Movement`, `MovementType`.

- **Controllers: 6** (`app/Http/Controllers/`)
  - ItemController, ItemCategoryController, ItemStatusController,
    LocationController, MovementTypeController, InventoryMovementController
- **Modelos: 6** (`app/Models/`)
  - Item, ItemCategory, ItemStatus, Location, MovementType, InventoryMovement
- **Migraciones: 6** (`database/migrations/`)
  - `2025_07_21_212518_create_item_categories_table`
  - `2025_07_21_212554_create_item_statuses_table`
  - `2025_07_21_212605_create_locations_table`
  - `2025_07_21_212617_create_items_table`
  - `2025_07_22_154940_create_movement_types_table`
  - `2025_07_22_212628_create_inventory_movements_table`
- **Seeders: 4** (`database/seeders/`)
  - ItemCategorySeeder, ItemStatusSeeder, LocationSeeder, MovementTypeSeeder
- **Vistas JSX: 20** (`resources/js/Pages/Inventory/`)
  - Category/ (Create, Edit, Index)
  - InventoryMovement/ (Create, Edit, Index, Show)
  - Item/ (Create, DeleteDialog, Edit, Index)
  - Location/ (Create, Edit, Index)
  - MovementType/ (Create, Edit, Index)
  - Status/ (Create, Edit, Index)
- **Rutas:** definidas en `routes/web.php`, grupo con `prefix('admin')->name('admin.')`,
  6 `Route::resource` (nombres `item`, `item-category`, `item-location`,
  `item-status`, `item-inventory-movement`, `item-movement-type`).
- **Diagrama de BD:** SÍ — `base-de-datos-modulo-inventario.pdf` (raíz del repo legacy,
  PDF de ~413 KB, agregado en el commit f5a7864).

Todos los archivos PHP del legacy ya estaban en **PascalCase** correcto;
no fue necesario renombrar ningún archivo (caso 1.4 no aplicó).

---

## Resumen ejecutivo

- Esqueleto del módulo creado: SÍ
- Migraciones migradas: 6/6
- Modelos migrados: 6/6
- Controllers migrados: 6/6
- Vistas JSX migradas: 20/20
- Seeders migrados y registrados: SÍ (4 de datos + InventoryAccessSeeder + InventoryDatabaseSeeder)
- Permiso `inventory.access` creado: SÍ
- Tablas semilladas verificadas con tinker: SÍ (ItemCategory, ItemStatus, Location, MovementType)
- Rutas registradas: 28 (6 resources con prefijo `admin.item.*` textual del legacy)
- Relaciones inyectadas en User: 1 (`inventoryMovements`)
- Factories creadas: 6
- Tests Feature creados: 8 (7 passing + 1 skipped justificado)
- Menú lateral actualizado con 6 entradas: SÍ
- Diagrama de BD migrado a docs/: SÍ (`docs/diagrams/inventory-database.pdf`)
- Tests pasando: antes 80 → ahora 87
- Tests skipped: antes 3 → ahora 4
- Estado general: **VERDE**

---

## Modelos adicionales detectados (Regla 3.14)

**Ninguno.** Se leyeron los `use` statements de los 6 controllers antes de
migrarlos. Todos importan exclusivamente modelos dentro del alcance del módulo
(Item, ItemCategory, ItemStatus, Location, MovementType, InventoryMovement) más
clases del framework (`Illuminate\Http\Request`, `Inertia\Inertia`,
`Illuminate\Support\Facades\DB`). No aparecieron modelos externos (Brand,
Supplier, Vendor, etc.). El único acoplamiento externo es
`InventoryMovement → App\Models\User` (relación `user()`), contemplado vía
inyección de relación en el ServiceProvider.

---

## Dependencias frontend instaladas

**Ninguna nueva para Inventory.** Las 20 vistas usan únicamente paquetes ya
presentes en `src/package.json`: `@inertiajs/react`, `@mui/material` (subpath),
`@mui/icons-material`, `react`, `react-i18next`. No había imports barril de MUI
ni imports `@/Pages/Inventory/...` que reescribir; los únicos imports `@/` son a
recursos compartidos del core (`@/Layouts/AdminLayout`, `@/Components/Alert`),
que se conservan según la Regla 3.3.

> Nota de entorno: fue necesario ejecutar `npm install --legacy-peer-deps` en
> `src/` porque el `node_modules` local estaba desactualizado y faltaba
> `react-beautiful-dnd` (dependencia **pre-existente** del módulo Maintenance,
> ya declarada en `package.json`). No es una dependencia de Inventory.

---

## Verificación de seeder con tinker

```
inventory.access exists: SÍ ("inventory.access")
ItemCategory:  {"1":"Mobiliario","2":"Equipo Tecnológico"}                                  (2)
ItemStatus:    {"1":"Activo","2":"Dañado","3":"En Reparación","4":"Desincorporado","5":"En Almacén"}  (5)
Location:      {"1":"Almacén Principal","2":"Laboratorio N°1","3":"Oficina de Dirección","4":"Área de Desincorporados"}  (4)
MovementType:  {"1":"Creación","2":"Actualización",...,"7":"Entrada","8":"Salida","9":"Ajuste"}  (9)
```

Todas las tablas semilladas devolvieron datos. `MovementType` incluye `Entrada`
y `Actualización`, los dos tipos que `ItemController` busca por nombre al
crear/actualizar un ítem (no quedan referencias huérfanas).

---

## Particularidades del módulo

- El permiso usa notación con punto (`inventory.access`) en lugar del patrón
  `isXxxxx` de otros módulos. Conservado textual (Regla 3.13).
- Rutas con prefijo `admin.item.*` textual del legacy (incluida la asimetría
  del legacy: la ruta de `Location` usa path `/location` pero nombre
  `admin.item-location.*`, y `InventoryMovement` solo expone `index` y `show`).
- El permiso `inventory.access` se aplicó como middleware
  `permission:inventory.access` en el grupo de rutas. En el legacy el grupo solo
  tenía `auth`+`verified`; se añadió el permiso para alinearlo con el menú lateral.
- Este módulo **no crea un rol nuevo** (a diferencia de Maintenance con
  `technician`); solo crea el permiso, que un admin puede asignar manualmente.
- No hay eventos ni listeners: `EventServiceProvider` queda con `$listen = []` y
  `$shouldDiscoverEvents = false`.
- Los movimientos de inventario son una **bitácora de auditoría**: se generan
  implícitamente al crear/actualizar ítems (`InventoryMovement::create(...)` en
  `ItemController`). No existe endpoint de creación directa de movimientos ni
  lógica de stock que modifique `quantity`.
- `Item/DeleteDialog.jsx` se migró tal cual, aunque en el legacy es un componente
  huérfano (ninguna vista lo importa). Se conserva por fidelidad al legacy.
- Diagrama de BD migrado a `docs/diagrams/inventory-database.pdf`.

---

## Commits realizados (sin co-autoría)

1. `7a83e42` feat(inventory): crea esqueleto del módulo con module:make (deshabilitado)
2. `5f79574` feat(inventory): migra 6 migraciones al módulo
3. `a038fa9` feat(inventory): migra 6 modelos al módulo con namespaces ajustados
4. `bad3df7` feat(inventory): migra 6 controllers al módulo con namespaces ajustados
5. `a7a901d` feat(inventory): migra 20 vistas JSX al módulo con imports relativos y MUI subpath
6. `b7bac88` feat(inventory): migra 4 seeders al módulo
7. `35cfc3b` feat(inventory): agrega InventoryAccessSeeder para el permiso inventory.access
8. `286e874` feat(inventory): registra InventoryDatabaseSeeder en DatabaseSeeder raíz
9. `31e8c10` feat(inventory): registra rutas del módulo con permission:inventory.access
10. `7cf8f34` feat(inventory): configura ServiceProvider y EventServiceProvider
11. `e2f2348` feat(inventory): activa módulo con seeder y permiso inventory.access funcionando
12. `70abb53` feat(inventory): agrega factories para modelos principales
13. `479f001` test(inventory): agrega tests Feature mínimos
14. `93fac8c` feat(inventory): agrega entradas del menú lateral con permission inventory.access
15. `f4c73c4` docs(inventory): agrega diagrama de BD del módulo en docs/diagrams/

> La inyección de relaciones de User (`inventoryMovements`) quedó incluida en el
> commit 10 (configuración del ServiceProvider), ya que es parte del `boot()` de
> `InventoryServiceProvider`.

---

## Verificación método-a-método

| Controller | Métodos legacy | Métodos migrados |
|---|---|---|
| ItemController | 6 (index, create, store, edit, update, destroy) | 6 ✓ |
| ItemCategoryController | 6 | 6 ✓ |
| ItemStatusController | 6 | 6 ✓ |
| LocationController | 6 | 6 ✓ |
| MovementTypeController | 6 | 6 ✓ |
| InventoryMovementController | 2 (index, show) | 2 ✓ |

Ningún método perdido.

---

## Problemas encontrados y decisiones

1. **`api.php` generado por `module:make`** referenciaba un `InventoryController`
   stub inexistente (se eliminó el stub porque el legacy no lo tiene). Esto rompía
   `php artisan route:list` con "Class ... InventoryController does not exist". Se
   vació `routes/api.php` (el módulo no expone rutas API), siguiendo el patrón de
   Maintenance.
2. **Migraciones sin bugs.** A diferencia de Maintenance, las 6 migraciones del
   legacy tenían `down()` correcto con el nombre real de la tabla. No hubo
   correcciones.
3. **`permissions.description` es NOT NULL.** El `firstOrCreate` de los tests
   inicialmente fallaba por no proveer `description`; se corrigió pasando el
   segundo array con la descripción (Regla 3.15).
4. **Entorno de verificación.** El proyecto usa MySQL en dev/CI, pero localmente
   no había servidor MySQL ni `src/.env`. Para la verificación end-to-end
   (`migrate:fresh --seed`, tinker) se configuró el `.env` local (gitignored) con
   SQLite, igual que el entorno de tests. No afecta el entregable ni la
   configuración de CI.
5. **`test_movement_updates_item_stock`** se marcó `skipped`: el módulo no tiene
   lógica de stock (los movimientos son bitácora; no modifican `quantity`).

---

## Estado de tests al finalizar

```
php artisan test
Tests: 4 skipped, 87 passed (202 assertions)

php artisan test --filter=Inventory
Tests: 1 skipped, 7 passed (12 assertions)
  - InventoryAuthorizationTest: 3/3 (guest redirect, sin permiso 403, con permiso OK)
  - ItemCrudTest: 3/3 (listar, crear, eliminar)
  - InventoryMovementTest: 1 passed (registrar movimiento) + 1 skipped (stock)
```

Los tests previos siguen verdes (80 → 87; +7 de Inventory). `npm run build`
completa sin errores.

---

## Próximo paso

Estado **VERDE**:

- Verificación manual del módulo en navegador.
- Para probar el módulo se necesita un usuario con permiso `inventory.access`
  asignado. Crearlo con tinker o asignarlo a un admin existente, por ejemplo:
  ```php
  $u = App\Models\User::where('email','admini@example.com')->first();
  $u->givePermissionTo('inventory.access');
  ```
- Push de `develop` al remoto (pendiente, manual).
- Verificar CI verde.
- **Las 4 integraciones de Fase 3 están COMPLETAS**: Thesis, Employees,
  Maintenance, Inventory.
- Próxima fase sugerida: Fase 4 (mejoras y consolidación) o cierre del proyecto
  según objetivos.
