# Issues conocidos del módulo Employees

**Origen:** rama legacy `origin/employees`
**Estado:** issues documentados antes de la integración modular
**Fuente:** testing manual en repositorio paralelo con la rama `employees`
  checked out directamente (sin modularizar)

Este documento registra los problemas que el módulo Employees tiene
en su estado legacy. La modularización de Fase 3.2 NO arregla
ninguno de estos issues — solo migra el código tal como está. Los
arreglos quedan para una iteración posterior con análisis humano.

---

## Issue 1 — Crear un Cargo falla con violación de foreign key

**Severidad:** Alta. Bloquea la creación de cargos por la UI.

**Cómo reproducirlo:**

1. Ir a la sección de Cargos (`employee.staff.index`).
2. Hacer click en "Crear".
3. Llenar el formulario y enviar.

**Error que aparece:**

```
Illuminate \ Database \ QueryException
SQLSTATE[23000]: Integrity constraint violation: 1452
Cannot add or update a child row: a foreign key constraint fails
(`laravel`.`staffs`, CONSTRAINT `staffs_type_foreign`
FOREIGN KEY (`type`) REFERENCES `staff_types` (`id`)
ON DELETE CASCADE)

INSERT INTO `staffs` (`name`, `type`, `places_number`, ...)
VALUES (Asdasd, 1, 1, ...)
```

**Ubicación:**
`app/Http/Controllers/Employees/StaffController.php:47` (método `store`)

**Diagnóstico preliminar (no aplicado):**

El formulario está enviando un valor para `type` que no existe en la
tabla `staff_types`. Causas probables:

- La tabla `staff_types` está vacía porque el seeder no se ejecuta
  automáticamente con `migrate:fresh --seed` (mismo problema que
  pasó con Thesis en Fase 3.1, donde el `StudentStatusSeeder` no
  estaba enganchado al `DatabaseSeeder` raíz).
- El campo `type` del formulario no corresponde con un `id` real de
  `staff_types` (puede ser un problema de mapeo en el select).

**Sugerencia para arreglar después:**

1. Verificar que `StaffTypeSeeder` (o equivalente dentro de
   `EmployeeSeeder`) esté siendo llamado por el `DatabaseSeeder`
   principal.
2. Verificar que el select de "Tipo" en el formulario JSX use el
   `id` real de los tipos, no un índice posicional ni un string.
3. Probar con `tinker`:
   `Modules\Employees\Models\StaffType::all()->pluck('name', 'id')`

---

## Issue 2 — Crear un Empleado no carga la vista (error JS)

**Severidad:** Alta. Bloquea totalmente la creación de empleados
por la UI.

**Cómo reproducirlo:**

1. Ir a la sección de Empleados (`employee.index`).
2. Hacer click en "Crear" o navegar a `employee.create`.
3. La vista intenta renderizar y la consola del navegador muestra
   el error.

**Errores en consola (en orden de aparición):**

```
client.ts:77 WebSocket connection to 'ws://[::1]:5174/' failed
client.ts:77 Uncaught (in promise) SyntaxError: Failed to construct
'WebSocket': The URL 'ws://localhost:undefined/' is invalid.
```

Este primer error es de Vite HMR (hot module reload) y es un
problema de configuración del dev server, no del módulo. Ignorar
para este issue.

```
create.jsx:308 Uncaught TypeError: Cannot read properties of
undefined (reading 'benefits')
    at EmployeeCreate (create.jsx:308:64)
```

**Ubicación:**
`resources/js/Pages/Employee/Employee/create.jsx:308`

**Diagnóstico preliminar (no aplicado):**

El componente `EmployeeCreate` intenta acceder a la propiedad
`benefits` de algún objeto que viene como `undefined`. El controller
probablemente no está pasando la prop `benefits` al componente, o se
la pasa con otro nombre.

Mirando el inventario del módulo legacy, existe un
`BenefitController` y modelos relacionados a beneficios — el
formulario de creación de empleados parece esperar la lista de
beneficios disponibles para asociarlos, pero el `EmployeeController`
no se la entrega.

**Sugerencia para arreglar después:**

1. Inspeccionar el método `create()` del `EmployeeController` legacy.
   Verificar si pasa `benefits` en el array de props a Inertia.
2. Si no lo pasa, agregar
   `'benefits' => Benefit::all()` o similar a la respuesta de
   `Inertia::render(...)`.
3. Verificar que el componente JSX maneje el caso de
   `benefits === undefined` con un default seguro
   (ej. `const benefits = props.benefits ?? []`).

---

## Issue 3 — Conflicto de dependencias frontend: styled-components 6 → 5

**Severidad:** Media. Bloqueante para `npm install` limpio en el
módulo, ya resuelto con downgrade.

**Cómo reproducirlo:**

Tras hacer checkout de la rama `employees`, ejecutar:

```powershell
npm install
```

**Error obtenido:**

```
npm error code ERESOLVE
npm error ERESOLVE could not resolve

Found: @types/react@18.3.3
node_modules/@types/react
  peerOptional @types/react@"^18.0.0" from @testing-library/react@16.0.0
  peerOptional @types/react@"^17.0.0 || ^18.0.0 || ^19.0.0" from
    @mui/icons-material@7.3.11
  3 more (@mui/material, @mui/styles, @mui/system)

Could not resolve dependency:
styled-components@"^6.1.19" from the root project

Conflicting peer dependency: @types/react@19.2.15
node_modules/@types/react
  peerOptional @types/react@"^19.1.1" from react-native@0.85.3
  node_modules/react-native
    peerOptional react-native@">= 0.68.0" from styled-components@6.4.2
    node_modules/styled-components
      styled-components@"^6.1.19" from the root project
```

**Causa raíz:**

`styled-components@^6.x` arrastra una peer-dependency a
`react-native` que requiere `@types/react@^19.1.1`, lo cual entra en
conflicto con el `@types/react@^18` que usa el resto del stack del
proyecto (React 18 + MUI v5).

Este es exactamente el tipo de cadena de incompatibilidad que ya
afectaba al proyecto con `@radix-ui/react-context-menu` y que
documentamos en el plan de prioridades como "deuda técnica de
modernización del frontend" (Fase 5, post-entrega).

**Workaround aplicado:**

Downgrade de `styled-components` en el `package.json` del módulo:

```diff
- "styled-components": "^6.3.11"
+ "styled-components": "^5.3.11"
```

`styled-components@5.x` no arrastra la peer-dependency a
react-native y es compatible con React 18 y MUI v5.

**Implicación para la modularización:**

El `package.json` raíz del proyecto **no debe quedar con
`styled-components@^6.x`**. Si el módulo Employees requiere
`styled-components`, agregar la versión 5 al `package.json` raíz
durante la integración. Si el código del módulo usa APIs específicas
de v6, anotarlo como deuda y migrar a APIs compatibles con v5.

**Sugerencia para arreglar de fondo:**

Cuando se haga la Fase 5 (modernización del frontend), revisar:

1. Si el módulo necesita `styled-components` en absoluto, o si
   puede reemplazarse con `@emotion/styled` (que es lo que MUI usa
   internamente).
2. Si se mantiene `styled-components`, evaluar subir a v6 una vez
   que el resto del stack soporte React 19.

---

## Resumen de acción para la integración modular

Durante Fase 3.2, Claude Code debe:

1. **Copiar el código tal como está**, sin intentar arreglar los
   tres issues anteriores. Son comportamientos heredados del legacy.
2. **Documentar en el reporte de Fase 3.2** que estos issues siguen
   presentes después de modularizar.
3. **Aplicar el downgrade de `styled-components` proactivamente**
   durante la integración (cambiar a `^5.3.11` en `package.json`).
   Este es el único arreglo justificable dentro del alcance de la
   modularización, porque sin él `npm install` falla y bloquea el
   build.
4. **No tocar la lógica de los Issues 1 y 2.** Quedan documentados
   para revisión humana después de la entrega.

---

## Cómo retomar estos issues

Cuando se decida arreglar los issues 1 y 2:

1. Crear rama `fix/employees-issues` desde develop.
2. Atacar uno a la vez: primero el seeder de StaffTypes (Issue 1),
   después el de benefits en create (Issue 2).
3. Para cada arreglo, escribir un test Feature que reproduzca el
   bug antes de arreglarlo (test rojo → arreglo → test verde).
4. PR a develop con su descripción referenciando este archivo.
