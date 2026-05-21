# Reporte de Fase 2 — Cobertura de tests y CI

**Fecha:** 2026-05-20
**Rama:** develop
**Commit HEAD antes de iniciar:** e8af7c589d06ccf33c5124823e291512f6ffdcea

---

## Resumen ejecutivo

- AgendaPointFactory: **creado** (más AgendaFactory como dependencia necesaria)
- Tests nuevos agregados: **22** (de 22 planeados)
- Tests skipped (documentados): **1** (`test_closure_notification_flag_is_set_when_all_points_voted`)
- Tests totales en la suite: **38 → 60** (59 pasados + 1 skipped)
- Workflow CI creado: **SÍ** (`.github/workflows/tests.yml`)
- Primer run de CI: pendiente del push a `origin/develop`
- Estado del primer run: **PENDIENTE** (se confirmará tras el push)
- Estado general: **AMARILLO** (suite verde local + 1 test skipped con razón documentada; CI pendiente de push remoto)

---

## PARTE 1 — AgendaPointFactory

- Archivo existía antes: **NO**
- Acción: **creado** (`Modules/AgendaConsejo/database/factories/AgendaPointFactory.php`)
- Campos cubiertos en `definition()`:
  - `agenda_id` (vía `Agenda::factory()`)
  - `description`
  - `requested_by_user_id` (vía `User::factory()`)
  - `status` (default `'Abierto para Votación'`)
  - `min_votes_to_close` (1)
  - `order` (0)
- Foreign keys resueltas con sub-factories:
  - `agenda_id → Agenda::factory()`
  - `requested_by_user_id → User::factory()`

### Dependencia adicional: AgendaFactory

El prompt asumía que `AgendaFactory` ya existía ("el de Agenda existe; el de AgendaPoint se acaba de crear en Parte 1"). Pero `find Modules -name "*Factory*.php"` no devolvió nada: ninguna factory de módulo había sido escrita aún. Los tests previos creaban `Agenda` con `::create()` directo, no con factory.

Para que `AgendaPointFactory` pudiera usar `Agenda::factory()` como sub-factory siguiendo el patrón del prompt, hubo que crear también `AgendaFactory`:

- `Modules/AgendaConsejo/database/factories/AgendaFactory.php`
- Campos: `name`, `date` (entre +1 día y +1 mes), `status` (`'Programado'`), `director_id` (`User::factory()`).
- `code` se omite porque el modelo lo genera automáticamente en el evento `creating`.
- `closure_notification_sent` se omite porque tiene default `false`.
- `closed_at` se omite porque es nullable.

### Ajustes en los modelos para que `HasFactory` resuelva las factories

El resolver por defecto de Laravel (`Factory::resolveFactoryName`) busca factories en `Database\Factories\...` relativo al namespace de la app (`App\`). Para modelos en módulos (`Modules\AgendaConsejo\Models\...`) la resolución por defecto apunta a `Database\Factories\Modules\AgendaConsejo\Models\AgendaPointFactory`, que no existe.

Verificado experimentalmente: `Factory::resolveFactoryName('Modules\AgendaConsejo\Models\AgendaPoint')` devolvía `Database\Factories\Modules\AgendaConsejo\Models\AgendaPointFactory`.

Solución mínima aplicada: agregar `protected static function newFactory()` a `Agenda` y `AgendaPoint` apuntando a la factory del módulo. Es un cambio puro de infraestructura de testing (no modifica lógica de negocio ni controllers).

### Refactor de VotingFlowTest

- Aplicado: **SÍ** (parcial)
- Cambio: reemplazo de `Agenda::create([...])` y `AgendaPoint::create([...])` directos por `Agenda::factory()->create([...])` y `AgendaPoint::factory()->create([...])`. Los `sync()` posteriores de `participants/votableUsers/votingOptions` se mantienen iguales — el factory no los toca.
- Verificación: los 4 tests de `VotingFlowTest` siguen verdes después del refactor.

---

## PARTE 2 — Tests Feature ampliados

### Lectura previa
- [x] `docs/TESTING_TEMPLATE.md` leído
- [x] `docs/MODULES_COOKBOOK.md` leído
- [x] 3 tests existentes leídos (`AgendaCrudTest`, `AgendaAuthorizationTest`, `VotingFlowTest`)
- [x] Controllers del módulo leídos (`AgendaController`, `AgendaPointController`, `VoteController`)
- [x] Rutas leídas (`Modules/AgendaConsejo/routes/web.php`)
- [x] Listener `CheckVotingStatus` leído (para entender `closure_notification_sent`)

### Archivos creados

#### `AgendaValidationTest.php`
- Tests escritos: **7**
- Tests skipped: 0
- Tests omitidos (lógica no implementada en controller): 0
- Lista:
  - [x] `test_agenda_name_is_required` — verde (campo real es `name`, no `title`)
  - [x] `test_agenda_name_max_length` — verde (string > 255 chars)
  - [x] `test_agenda_date_is_required` — verde
  - [x] `test_agenda_date_must_be_valid_format` — verde (string `"no-es-una-fecha"`)
  - [x] `test_agenda_date_must_be_today_or_future` — verde (regla `after_or_equal:today` del controller)
  - [x] `test_agenda_requires_at_least_one_participant` — verde (bonus)
  - [x] `test_agenda_participants_must_be_counselors` — verde (bonus: la regla `exists` exige rol counselor)

> Nota: la validación se hace contra `assertSessionHasErrors()` porque el controller responde con redirect (302) + errores en sesión, no con 422 JSON. Esto coincide con el patrón Inertia del proyecto.

#### `AgendaClosureTest.php`
- Tests escritos: **6**
- Tests skipped: **1**
- Lista:
  - [x] `test_director_can_close_agenda` — verde (PUT `agendas/{agenda}/close`, status pasa a `'Cerrado'` y `closed_at` no es null)
  - [x] `test_counselor_cannot_close_agenda` — verde (403 por middleware `role:director`)
  - [x] `test_closed_agenda_cannot_be_updated` — verde (controller hace early-return con `error` en sesión)
  - [x] `test_closed_agenda_cannot_receive_votes` — verde (`VoteController::store` valida `status === 'Cerrado'`)
  - [x] `test_closed_agenda_cannot_receive_new_points` — verde (bonus: `AgendaPointController::store` valida lo mismo)
  - [ ] `test_closure_notification_flag_is_set_when_all_points_voted` — **skipped**

#### `AgendaPointValidationTest.php`
- Tests escritos: **9**
- Tests skipped: 0
- Lista:
  - [x] `test_director_can_create_agenda_point` — verde (happy path)
  - [x] `test_counselor_cannot_create_agenda_point` — verde (403 por middleware `role:director`)
  - [x] `test_agenda_point_requires_description` — verde (campo real es `description`, no `title`)
  - [x] `test_agenda_point_requires_requested_by_user` — verde
  - [x] `test_agenda_point_requested_by_must_be_participant` — verde (la regla `Rule::exists('agenda_user', ...)` lo exige)
  - [x] `test_agenda_point_requires_at_least_one_votable_user` — verde
  - [x] `test_agenda_point_requires_at_least_one_voting_option` — verde
  - [x] `test_agenda_point_min_votes_cannot_exceed_votable_users` — verde (regla `max:$numberOfVotableUsers`)
  - [x] `test_agenda_point_belongs_to_existing_agenda` — verde (404 por Route Model Binding)

### Tests no escritos / skipped porque la lógica no está completamente operativa

1. **`test_closure_notification_flag_is_set_when_all_points_voted`** — *skipped con razón documentada en el código*.
   - **Hallazgo:** el flag `closure_notification_sent` lo activa el listener `CheckVotingStatus` (en `Modules/AgendaConsejo/app/Listeners/CheckVotingStatus.php`) en respuesta al evento `VoteCast`. El listener `implements ShouldQueue` y `QUEUE_CONNECTION=sync` en `phpunit.xml`, así que se ejecutaría sincrónicamente — *si estuviera registrado*.
   - **El problema:** el `EventServiceProvider` del módulo tiene `$shouldDiscoverEvents = true` pero **no sobrescribe `discoverEventsWithin()`** para apuntar al directorio de listeners del módulo. El auto-descubrimiento de Laravel busca por defecto en `app/Listeners` (relativo al `base_path`), no en `Modules/<Nombre>/app/Listeners`. Resultado: el listener nunca se engancha al evento.
   - **Por qué no se arregló en Fase 2:** el prompt es explícito ("No modifiques controllers de la aplicación. Esta fase es solo tests + CI. Si un test revela que un controller no valida algo, documéntalo en el reporte; no agregues la validación."). Wire-up de listeners es lógica de aplicación, no de tests.
   - **Acción recomendada para revisión humana:** en `Modules/AgendaConsejo/app/Providers/EventServiceProvider.php`, sobrescribir `discoverEventsWithin()` para devolver `[module_path('AgendaConsejo', 'app/Listeners')]`, o usar `$listen` explícito mapeando `VoteCast => [CheckVotingStatus::class]`.

### Resultado de la suite tras los cambios

```
php artisan test
```

- Tests totales: **60** (38 previos + 22 nuevos)
- Tests pasados: **59**
- Tests fallidos: **0**
- Tests skipped: **1** (documentado arriba)
- Duración: ~3.5s

---

## PARTE 3 — GitHub Actions

- Archivo `.github/workflows/tests.yml` creado: **SÍ**
- Pasos del workflow: **14** (checkout, setup PHP, setup Node, cache composer, cache npm, instalar PHP, instalar npm, copiar .env, generar APP_KEY, dump-autoload, build, tests, resumen)
- Triggers: `push` y `pull_request` en `develop` y `main`
- Runner: `ubuntu-latest` (case-sensitive: detecta imports con casing incorrecto)

### Verificación local antes del push

- [x] `composer install --no-interaction --prefer-dist` — exitoso
- [x] `npm install --legacy-peer-deps` — exitoso (775 paquetes instalados; warnings de deprecation esperados; sin errores ERESOLVE)
- [x] `npm run build` — exitoso (`✓ built in 4.97s`)
- [x] `php artisan test` — exitoso (59/60 verdes, 1 skipped documentado)

### Primer run en GitHub Actions

- Commit del workflow: se asignará al hacer push
- URL del run: **pendiente** (se confirmará tras `git push origin develop`)
- Estado: **PENDIENTE**
- Duración del run: pendiente
- Pasos esperados que tomen más tiempo: `npm install` (~30s en local) y `npm run build` (~5s en local)

### Si falló el primer run

(No aplica todavía; se actualiza el reporte si el run remoto falla.)

### Notas sobre `package-lock.json`

`npm install --legacy-peer-deps` regeneró `package-lock.json` en local (842 líneas cambiadas, ~355 inserciones / 487 eliminaciones). Estos cambios son producto de la resolución `legacy-peer-deps` que el lockfile original no tenía. **No se incluyen en los commits de Fase 2** porque el prompt prohibe actualizar paquetes existentes; el CI generará un lockfile coherente en su propia ejecución limpia.

---

## Commits realizados

(Hashes definitivos se completan al hacer commit.)

1. `feat(tests): agrega AgendaFactory y AgendaPointFactory + newFactory() en modelos`
2. `test(agenda): agrega AgendaValidationTest`
3. `test(agenda): agrega AgendaClosureTest`
4. `test(agenda): agrega AgendaPointValidationTest`
5. `refactor(test): VotingFlowTest usa factories en setUp`
6. `ci: agrega workflow de tests con GitHub Actions`
7. `docs: agrega reporte de Fase 2`

---

## Próximo paso

Suite local en **AMARILLO**:
- 59/60 pasan; 1 skipped con razón documentada (wiring de listener `CheckVotingStatus`).
- CI pendiente de validar en GitHub Actions tras push.

**Antes de avanzar a Fase 3** (modularizar Employees, Maintenance, Inventory):
1. Hacer push de `develop` a `origin` para que GitHub Actions corra el workflow por primera vez.
2. Verificar que el primer run en GitHub Actions pase en verde y actualizar el reporte con el enlace y la duración.
3. Decisión humana opcional: arreglar el `EventServiceProvider` del módulo para que `CheckVotingStatus` se enganche, y quitar el `markTestSkipped` del test correspondiente. Esto convierte la fase de **AMARILLO** a **VERDE**.

Si el CI queda verde, se puede avanzar a Fase 3. El test skipped puede atenderse en una iteración posterior cuando el wiring del listener se decida.
